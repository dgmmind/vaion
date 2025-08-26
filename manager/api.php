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

        $manager = $users[$manager_id] ?? null;
        if (!$manager) {
            http_response_code(404);
            echo json_encode(["error" => "Manager no encontrado"]);
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
                    "category"    => $category,
                    "checked"     => true,
                    "item"        => "PERFECTO"
                ];
            }
        }

        // Insertar en batch (50 por batch)
        $batchSize = 50;
        for ($i = 0; $i < count($evaluationsToInsert); $i += $batchSize) {
            $batch = array_slice($evaluationsToInsert, $i, $batchSize);
            $supabase->insert("evaluations", $batch);
        }

        echo json_encode([
            "day"               => $dayResult["data"][0],
            "evaluations_count" => count($evaluationsToInsert),
            "message"           => "Día y evaluaciones creadas correctamente"
        ]);
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
        // Update using the correct filter format as an array
        $updateResult = $supabase->update(
            "evaluations",
            ["evaluation_id" => $evaluation_id, "day_id" => $day_id],
            ["item" => $item, "checked" => $checked, "updated_at" => date('Y-m-d H:i:s')]
        );

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

    default:
        http_response_code(400);
        echo json_encode(["error" => "Acción no válida"]);
        break;
}
