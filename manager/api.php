<?php
require_once "../repository/supabase.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

session_start();
$manager_id = $_SESSION["manager_id"] ?? null;

if (!$manager_id) {
    http_response_code(400);
    echo json_encode(["error" => "Manager no encontrado en sesión"]);
    exit;
}

$supabase = new Supabase();

// Decidir acción mediante parámetro 'action'
$action = $_GET['action'] ?? 'create_day';

switch ($action) {
    // =========================
    // Crear día y evaluaciones
    // =========================
    case 'create_day':
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
            exit;
        }

        $day_date = $_POST["day_date"] ?? null;
        if (!$day_date) {
            http_response_code(400);
            echo json_encode(["error" => "Falta day_date"]);
            exit;
        }
        
        // Ensure the date is in YYYY-MM-DD format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day_date)) {
            http_response_code(400);
            echo json_encode(["error" => "Formato de fecha inválido. Use YYYY-MM-DD"]);
            exit;
        }
        
        // Create a DateTime object to ensure the date is valid
        $date = DateTime::createFromFormat('Y-m-d', $day_date);
        if (!$date || $date->format('Y-m-d') !== $day_date) {
            http_response_code(400);
            echo json_encode(["error" => "Fecha inválida"]);
            exit;
        }

        $manager = $users[$manager_id] ?? null;
        if (!$manager) {
            http_response_code(404);
            echo json_encode(["error" => "Manager no encontrado"]);
            exit;
        }

        // Verificar si ya existe un día con esta fecha para este manager
        $existingDay = $supabase->select("days", "day_id", ["manager_id" => $manager_id, "day_date" => $day_date]);
        if (!empty($existingDay['data'])) {
            http_response_code(400);
            echo json_encode(["error" => "Ya existe un día registrado para esta fecha"]);
            exit;
        }

        // Insertar día
        $dayResult = $supabase->insert("days", ["manager_id" => $manager_id, "day_date" => $day_date]);
        if ($dayResult["status"] !== 201) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo crear el día", "details" => $dayResult]);
            exit;
        }

        $day_id = $dayResult["data"][0]["day_id"] ?? null;
        if (!$day_id) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo obtener el ID del día"]);
            exit;
        }

        // Preparar evaluaciones por defecto
        $employees = $manager["employees"] ?? [];
        $evaluationsToInsert = [];
        foreach ($employees as $emp) {
            foreach ($category_item as $category => $items) {
                $evaluationsToInsert[] = [
                    "day_id"      => $day_id,
                    "employee_id" => $emp["id"],
                    "manager_id"  => $manager_id,
                    "category"    => $category,
                    "checked"     => true,
                    "item"        => "PERFECTO"
                ];
            }
        }

        // Insertar evaluaciones en batch (50 por batch) y validar cada inserción
        $batchSize = 50;
        $evaluationsInserted = 0;
        $evaluationsErrors = [];
        
        for ($i = 0; $i < count($evaluationsToInsert); $i += $batchSize) {
            $batch = array_slice($evaluationsToInsert, $i, $batchSize);
            $insertResult = $supabase->insert("evaluations", $batch);
            
            if ($insertResult["status"] === 201) {
                $evaluationsInserted += count($batch);
            } else {
                $evaluationsErrors[] = [
                    "batch_index" => $i,
                    "error" => $insertResult
                ];
            }
        }
        
        // Verificar que se insertaron todas las evaluaciones
        $totalEvaluations = count($evaluationsToInsert);
        $success = ($evaluationsInserted === $totalEvaluations);
        
        // Preparar respuesta detallada
        $response = [
            "success" => $success,
            "day" => $dayResult["data"][0],
            "evaluations" => [
                "total_expected" => $totalEvaluations,
                "total_inserted" => $evaluationsInserted,
                "success_rate" => round(($evaluationsInserted / $totalEvaluations) * 100, 2) . "%"
            ],
            "message" => $success ? "Día y evaluaciones creadas correctamente" : "Día creado pero algunas evaluaciones fallaron"
        ];
        
        // Si hubo errores, incluirlos en la respuesta
        if (!empty($evaluationsErrors)) {
            $response["evaluation_errors"] = $evaluationsErrors;
            http_response_code(207); // Multi-Status
        }
        
        echo json_encode($response);
        break;

    // =========================
    // Listar días del manager
    // =========================
    case 'list_days':
        $response = $supabase->select("days", "day_id, day_date, manager_id");
        $days = array_filter($response["data"], fn($d) => $d["manager_id"] === $manager_id);

        echo json_encode([
            "manager_id" => $manager_id,
            "days"       => array_values($days)
        ]);
        break;

    // =========================
    // Actualizar evaluación por evaluation_id y day_id
    // =========================
    case 'update_evaluation':
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
            exit;
        }

        $input = json_decode(file_get_contents("php://input"), true);

        $evaluation_id = $input['evaluation_id'] ?? null;
        $day_id        = $input['day_id'] ?? null;
        $item          = $input['item'] ?? null;

        if (!$evaluation_id || !$day_id || !$item) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan parámetros requeridos"]);
            exit;
        }

        // Determinar checked: PERFECTO = true, otro = false
        $checked = ($item === "PERFECTO");

        // Filtro único usando evaluation_id + day_id
        $filter = [
            'evaluation_id' => $evaluation_id,
            'day_id' => $day_id
        ];
        
        $data = [
            'item' => $item, 
            'checked' => $checked ? 'true' : 'false',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $updateResult = $supabase->update("evaluations", $data, $filter);

        if ($updateResult["status"] === 200) {
            echo json_encode([
                "success" => true,
                "checked" => $checked,
                "item"    => $item
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo actualizar", "details" => $updateResult]);
        }
        break;

    // =========================
    // Obtener pausas del usuario para hoy
    // =========================
    case 'getUserPauses':
        $userId = $_GET['userId'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Falta userId"]);
            exit;
        }
        
        // Obtener TODAS las pausas del usuario (sin filtrar por fecha)
        $pausesResponse = $supabase->select('pauses', '*', [
            'conditions' => "employee_id.eq.{$userId}",
            'order' => 'start_time.desc'
        ]);

        if ($pausesResponse['status'] === 200) {
            $pauses = $pausesResponse['data'] ?? [];
            
            echo json_encode([
                "success" => true,
                "pauses" => $pauses
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener pausas",
                "details" => $pausesResponse
            ]);
        }
        break;

    // =========================
    // Obtener pausas activas del usuario
    // =========================
    case 'getUserActivePauses':
        $userId = $_GET['userId'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Falta userId"]);
            exit;
        }
        
        // Obtener solo las pausas activas (end_time es null)
        $activePausesResponse = $supabase->select('pauses', '*', [
            'conditions' => "employee_id.eq.{$userId},end_time.is.null"
        ]);

        if ($activePausesResponse['status'] === 200) {
            $activePauses = $activePausesResponse['data'] ?? [];
            
            echo json_encode([
                "success" => true,
                "pauses" => $activePauses
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener pausas activas",
                "details" => $activePausesResponse
            ]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Acción no válida"]);
        break;
}
