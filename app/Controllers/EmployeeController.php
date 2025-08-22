<?php
namespace App\Controllers;

use App\Services\Supabase;
require_once __DIR__ . '/../Settings/settings.php';

class EmployeeController
{
    // Muestra las evaluaciones de un empleado específico
    public function viewEvaluations()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Usar el ID del empleado desde la sesión (mismo ID que en Supabase)
        $employee_id = $_SESSION['user']['id'] ?? '';
        
        if (empty($employee_id)) {
            $_SESSION['error'] = 'ID de empleado no especificado';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        // Obtener datos del empleado (buscar dentro de los arreglos de empleados por cada manager)
        $usersJson = file_get_contents(__DIR__ . '/../../json/users.json');
        $users = json_decode($usersJson, true);
        
        $employee = null;
        if (is_array($users)) {
            foreach ($users as $manager) {
                if (!isset($manager['employees']) || !is_array($manager['employees'])) continue;
                foreach ($manager['employees'] as $emp) {
                    if (($emp['id'] ?? null) == $employee_id) {
                        $employee = $emp;
                        break 2;
                    }
                }
            }
        }

        if (!$employee) {
            $_SESSION['error'] = 'Empleado no encontrado';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        // Obtener evaluaciones del empleado desde Supabase
        $supabase = new Supabase();
        $evaluations = [];
        
        try {
            // Traer evaluaciones tal cual están en Supabase para el empleado
            $rows = $supabase->getEvaluationsByEmployee($employee_id);
            if (!is_array($rows)) {
                $rows = [];
            }

            // Agrupar por día sin añadir categorías por defecto
            $grouped = [];
            foreach ($rows as $row) {
                $dayId = $row['day_id'] ?? null;
                if (!$dayId) { continue; }

                if (!isset($grouped[$dayId])) {
                    $grouped[$dayId] = [
                        'day_id' => $dayId,
                        'date' => isset($row['created_at']) ? substr($row['created_at'], 0, 10) : date('Y-m-d'),
                        'data' => []
                    ];
                }

                $category = $row['category'] ?? 'SIN_CATEGORIA';
                $grouped[$dayId]['data'][$category] = [
                    'item' => $row['item'] ?? null,
                    'checked' => (bool)($row['checked'] ?? false)
                ];
            }

            // Convertir a lista ordenada por fecha descendente
            $evaluations = array_values($grouped);
            usort($evaluations, function ($a, $b) {
                return strcmp($b['date'], $a['date']);
            });

        } catch (\Exception $e) {
            $errorMsg = 'Error al cargar las evaluaciones: ' . $e->getMessage();
            $_SESSION['error'] = $errorMsg;
        }

        $title = 'Evaluaciones de ' . $employee['name'];
        require __DIR__ . '/../Views/employee/evaluations.php';
    }
}
