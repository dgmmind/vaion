<?php
require_once "../repository/supabase.php";
require_once "../data/users.php";

// Configurar zona horaria de Honduras
date_default_timezone_set('America/Tegucigalpa');

// Log para verificar la zona horaria
error_log('Zona horaria configurada: ' . date_default_timezone_get() . ' - Hora actual: ' . date('Y-m-d H:i:s T'));

session_start();
$user_id = $_SESSION["user_id"] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit;
}

$supabase = new Supabase();

// Configurar el tipo de contenido
header('Content-Type: application/json');

// Obtener datos de entrada en bruto
$input = file_get_contents('php://input');

// Inicializar datos
$data = [];

// Si hay datos JSON, decodificarlos
if (strpos($input, '{') === 0) {
    $data = json_decode($input, true) ?? [];
}

// Obtener la acción de GET, POST o datos JSON
$action = $_GET['action'] ?? ($_POST['action'] ?? ($data['action'] ?? ''));

// Si no hay acción, devolver error
if (empty($action)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Se requiere el parámetro action',
        'received' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
            'get' => $_GET,
            'post' => $_POST,
            'input' => $input
        ]
    ]);
    exit;
}

// Combinar datos de POST y los datos del JSON
$data = array_merge($_POST, $data);

// Manejar acción de verificación de pausa activa
if ($action === 'getActivePause' || (isset($_GET['action']) && $_GET['action'] === 'getActivePause')) {
    // Configurar el tipo de contenido primero
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    try {
        // Limpiar búfer de salida
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Obtener pausas activas usando el método select de Supabase
        $result = $supabase->select('pauses', '*', [
            'employee_id' => $user_id,
            'end_time' => 'null',
            'order' => 'start_time.desc',
            'limit' => 1
        ]);
        
        $response = ['success' => true, 'pause' => null];
        if (isset($result['data']) && is_array($result['data']) && !empty($result['data'][0])) {
            $response['pause'] = $result['data'][0];
        }
        
        // Enviar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error al verificar pausa activa: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Configurar el tipo de contenido
header('Content-Type: application/json');

switch ($action) {
    // =========================
    // Pause Management
    // =========================
    case 'startPause':
        $reason = $data['reason'] ?? '';
        if (empty($reason)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Debe especificar una razón para la pausa']);
            exit;
        }

        try {
            // Verificar si ya hay una pausa activa
            $activePause = $supabase->select('pauses', '*', [
                'employee_id' => $user_id,
                'end_time' => 'null',
                'limit' => 1,
                'order' => 'start_time.desc'
            ]);

            if (isset($activePause['data']) && is_array($activePause['data']) && !empty($activePause['data'][0])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ya tiene una pausa activa']);
                exit;
            }

            // Crear nueva pausa
            $newPause = $supabase->insert('pauses', [
                'employee_id' => $user_id,
                'reason' => $reason,
                'start_time' => date('Y-m-d H:i:s', time()),
                'created_at' => date('Y-m-d H:i:s', time())
            ]);

            if (isset($newPause['error'])) {
                throw new Exception($newPause['error']);
            }

            // Obtener los datos completos de la pausa recién creada
            if (empty($newPause['data'])) {
                throw new Exception('No se pudo obtener la información de la pausa recién creada');
            }

            $pauseData = $newPause['data'][0];
            
            // Devolver todos los detalles de la pausa
            echo json_encode([
                'success' => true, 
                'message' => 'Pausa iniciada',
                'pause_id' => $pauseData['pause_id'],
                'employee_id' => $pauseData['employee_id'],
                'start_time' => $pauseData['start_time'],
                'end_time' => $pauseData['end_time'],
                'reason' => $pauseData['reason'] ?? null,
                'created_at' => $pauseData['created_at'],
                'updated_at' => $pauseData['updated_at'] ?? null
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al iniciar la pausa: ' . $e->getMessage()]);
        }
        break;

    case 'endPause':
        try {
            // Obtener el ID de la pausa activa
            $activePause = $supabase->select('pauses', 'pause_id', [
                'employee_id' => $user_id,
                'end_time' => null,
                'order' => 'start_time.desc',
                'limit' => 1
            ]);

            // Verificar si hay error
            if (isset($activePause['error'])) {
                throw new Exception('Error al buscar pausa activa: ' . $activePause['error']);
            }

            // Verificar si hay pausa activa
            if (empty($activePause['data'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No hay pausa activa para este usuario']);
                exit;
            }

            $pauseId = $activePause['data'][0]['pause_id'];
            $endTime = date('Y-m-d H:i:s', time());
            
            // Actualizar solo el end_time
            $updateResult = $supabase->update('pauses', 
                [
                    'end_time' => $endTime,
                    'updated_at' => $endTime
                ],
                ['pause_id' => $pauseId]
            );
            
            if (isset($updateResult['error'])) {
                throw new Exception('Error al actualizar la pausa: ' . $updateResult['error']);
            }

            // Devolver confirmación simple
            echo json_encode([
                'success' => true, 
                'message' => 'Pausa finalizada',
                'pause_id' => $pauseId,
                'end_time' => $endTime
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al finalizar la pausa: ' . $e->getMessage()]);
        }
        break;
        
    case 'getActivePause':
        $result = $supabase->select('pauses', '*', [
            'employee_id' => $user_id,
            'end_time' => 'null',
            'order' => 'start_time.desc',
            'limit' => 1
        ]);
        
        if (isset($result['data'][0])) {
            echo json_encode(['success' => true, 'pause' => $result['data'][0]]);
        } else {
            echo json_encode(['success' => true, 'pause' => null]);
        }
        break;
        
    case 'getPauses':
        $result = $supabase->select('pauses', '*', [
            'employee_id' => $user_id,
            'order' => 'start_time.desc',
            'limit' => 50
        ]);
        
        if (isset($result['data'])) {
            echo json_encode(['success' => true, 'pauses' => $result['data']]);
        } else {
            echo json_encode(['success' => true, 'pauses' => []]);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
