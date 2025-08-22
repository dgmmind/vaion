<?php
namespace App\Controllers;

use App\Services\Supabase;
require_once __DIR__ . '/../Settings/settings.php';

class ManagerController
{
    // Muestra formulario para crear día/semana
    public function createDayForm(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        $title = 'Crear Día';
        $manager_id = $_SESSION['user']['id'] ?? '';
        require __DIR__ . '/../Views/create_day.php';
    }

    // Procesa creación de día y evaluaciones
    public function createDay(): void
    {
        // Obtener el ID del manager desde la sesión
        if (empty($_SESSION['user']['id'])) {
            $_SESSION['error'] = 'No se pudo identificar al usuario';
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        $manager_id = $_SESSION['user']['id'];
        $day_date = $_POST['day_date'] ?? '';

        if (empty($day_date)) {
            $_SESSION['error'] = 'La fecha del día es requerida';
            header('Location: ' . BASE_URL . '/manager/createDayForm');
            exit;
        }

        // Cargar empleados del manager desde el JSON
        $usersJson = file_get_contents(__DIR__ . '/../../json/users.json');
        if ($usersJson === false) {
            $_SESSION['error'] = 'Error al cargar los datos de empleados';
            header('Location: ' . BASE_URL . '/manager/createDayForm');
            exit;
        }

        $allUsers = json_decode($usersJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $_SESSION['error'] = 'Error en el formato de datos de empleados';
            header('Location: ' . BASE_URL . '/manager/createDayForm');
            exit;
        }

        $employees = [];
        $managerFound = false;
        
        // Buscar el manager y sus empleados en la estructura del JSON
        foreach ($allUsers as $managerData) {
            if (is_array($managerData) && isset($managerData['id']) && (string)$managerData['id'] === (string)$manager_id) {
                $managerFound = true;
                if (isset($managerData['employees']) && is_array($managerData['employees'])) {
                    // Filtrar empleados para asegurar que tengan la estructura correcta
                    $employees = array_filter($managerData['employees'], function($emp) {
                        return is_array($emp) && !empty($emp['id']) && !empty($emp['name']);
                    });
                }
                break;
            }
        }

        if (!$managerFound) {
            $_SESSION['error'] = 'No se encontraron datos para el usuario actual';
            header('Location: ' . BASE_URL . '/manager/createDayForm');
            exit;
        }

        $supabase = new Supabase();
        
        // 1. Crear el día
        $day = $supabase->createDay($manager_id, $day_date);
        
        $day_id = null;
        if (is_array($day)) {
            if (isset($day[0]['day_id'])) { 
                $day_id = (int)$day[0]['day_id']; 
            } elseif (isset($day['day_id'])) { 
                $day_id = (int)$day['day_id']; 
            }
        }
        
        if (!$day_id) {
            $_SESSION['error'] = 'Error al crear el día. Intente nuevamente.';
            header('Location: ' . BASE_URL . '/manager/createDayForm');
            exit;
        }
        
        // 2. Crear evaluaciones si hay empleados
        if (!empty($employees)) {
            $result = $supabase->createEvaluations($day_id, $employees);
            
            if ($result === false) {
                $_SESSION['error'] = 'Día creado, pero hubo un error al crear las evaluaciones';
            } else {
                $_SESSION['message'] = 'Día y evaluaciones creados exitosamente';
            }
        } else {
            $_SESSION['message'] = 'Día creado, pero no se encontraron empleados para evaluar';
        }
        
        header('Location: ' . BASE_URL . '/manager/days');
        exit;
    }

    // Visualiza días del usuario logueado
    public function listDays(): void
    {
        if (empty($_SESSION['user']['id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para ver los días';
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        $manager_id = $_SESSION['user']['id'];
        $supabase = new Supabase();
        
        // Obtener solo los días del usuario logueado
        $days = $supabase->getDaysByManager($manager_id);
        
        if ($days === false) {
            $_SESSION['error'] = 'Error al cargar los días. Por favor, intente de nuevo.';
            $days = []; // Asegurar que $days sea un array
        } else {
            // Obtener evaluaciones para cada día
            foreach ($days as &$day) {
                $evaluations = $supabase->getEvaluationsByDay($day['day_id']);
                $day['evaluations'] = is_array($evaluations) ? $evaluations : [];
            }
            unset($day); // Romper la referencia
        }
        
        $title = 'Mis Días';
        require __DIR__ . '/../Views/list_days.php';
    }

    // Visualiza evaluaciones de un día específico
    public function viewDayEvaluations($day_id): void
    {
        if (empty($_SESSION['user']['id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para ver las evaluaciones';
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        $manager_id = $_SESSION['user']['id'];
        $supabase = new Supabase();
        
        // Verificar que el día pertenezca al manager
        $day = $supabase->getDayById($day_id);
        
        if (!$day || $day['manager_id'] !== $manager_id) {
            $_SESSION['error'] = 'No tienes permiso para ver estas evaluaciones o el día no existe';
            header('Location: ' . BASE_URL . '/manager/days');
            exit;
        }
        
        // Obtener las evaluaciones del día
        $evaluations = $supabase->getEvaluationsByDay($day_id);
        
        if ($evaluations === false) {
            $_SESSION['error'] = 'Error al cargar las evaluaciones';
            header('Location: ' . BASE_URL . '/manager/days');
            exit;
        }
        
        // Cargar datos de evaluaciones desde el archivo JSON
        $evaluationsData = json_decode(file_get_contents(__DIR__ . '/../../json/evaluations.json'), true);
        
        // Agrupar evaluaciones por empleado
        $evaluaciones_por_empleado = [];
        $categorias = array_keys($evaluationsData);
        
        foreach ($evaluations as $eval) {
            $empleado_id = $eval['employee_id'];
            if (!isset($evaluaciones_por_empleado[$empleado_id])) {
                $evaluaciones_por_empleado[$empleado_id] = [];
            }
            $evaluaciones_por_empleado[$empleado_id][] = $eval;
        }
        
        // Pasar datos a la vista
        $title = 'Evaluaciones del Día ' . ($day['day_date'] ?? '');
        require __DIR__ . '/../Views/day_evaluations.php';
    }
    
    // Actualiza una evaluación vía AJAX
    public function updateEvaluation() {
        header('Content-Type: application/json');
        
        // Verificar autenticación
        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
        // Obtener datos de la solicitud
        $evaluation_id = $_POST['evaluation_id'] ?? null;
        $field = $_POST['field'] ?? null;
        $value = $_POST['value'] ?? null;
        $day_id = $_POST['day_id'] ?? null;
        $checked = isset($_POST['checked']) ? filter_var($_POST['checked'], FILTER_VALIDATE_BOOLEAN) : false;
        
        
        // Validar datos requeridos
        if (empty($evaluation_id) || $field !== 'item' || $value === null) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Datos inválidos',
                'details' => [
                    'evaluation_id' => $evaluation_id,
                    'field' => $field,
                    'value' => $value
                ]
            ]);
            exit;
        }
        
        // Limpiar el valor del ítem
        $item = trim($value);
        if (empty($item)) {
            $item = 'Sin especificar';
        }
        
        $supabase = new Supabase();
        
        // Preparar datos para actualizar
        $updateData = [
            'item' => $item,
            'checked' => $checked  // Incluir el estado del checkbox
        ];
        
        
        // Actualizar tanto el ítem como el estado del checkbox
        $success = $supabase->updateEvaluation($evaluation_id, $updateData);
        
        if ($success === false) {
            $error = error_get_last();
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error al actualizar la evaluación en la base de datos',
                'evaluation_id' => $evaluation_id,
                'item' => $item,
                'checked' => $checked,
                'error' => $error['message'] ?? 'Error desconocido'
            ]);
            exit;
        }
        
        // Éxito
        echo json_encode([
            'success' => true,
            'evaluation_id' => $evaluation_id,
            'message' => 'Evaluación actualizada correctamente',
            'item' => $item,
            'checked' => $checked
        ]);
        exit;
    }
    
    // Método listEvaluations eliminado - Reemplazado por viewDayEvaluations
}
