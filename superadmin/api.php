<?php
// Configurar zona horaria para Honduras
date_default_timezone_set('America/Tegucigalpa');

require_once "../repository/supabase.php";
require_once "../data/users.php";

session_start();

// Verificar si el usuario es superadmin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'superadmin') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado"]);
    exit;
}

$supabase = new Supabase();

// Obtener la acción solicitada
$action = $_GET['action'] ?? '';

switch ($action) {
    // =========================
    // Obtener pausas de un empleado específico
    // =========================
    case 'getUserPauses':
        $userId = $_GET['userId'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Falta userId"]);
            exit;
        }
        
        // Obtener el rango de fechas de los parámetros o usar el día actual
        $startDate = $_GET['startDate'] ?? date('Y-m-d');
        $endDate = $_GET['endDate'] ?? date('Y-m-d');
        
        // Validar fechas
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) $startDate = date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) $endDate = date('Y-m-d');
        
        // Obtener pausas en el rango de fechas
        $pausesResponse = $supabase->select('pauses', '*', [
            'conditions' => "employee_id.eq.{$userId},start_time.gte.{$startDate}T00:00:00,start_time.lte.{$endDate}T23:59:59",
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
    // Obtener pausas activas de un empleado
    // =========================
    case 'getUserActivePauses':
        $userId = $_GET['userId'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Falta userId"]);
            exit;
        }
        
        $pausesResponse = $supabase->select('pauses', '*', [
            'conditions' => "employee_id.eq.{$userId},end_time.is.null"
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
                "message" => "Error al obtener pausas activas",
                "details" => $pausesResponse
            ]);
        }
        break;

    // =========================
    // Endpoint no válido
    // =========================
    default:
        http_response_code(400);
        echo json_encode(["error" => "Acción no válida"]);
        break;
}
