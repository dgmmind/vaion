<?php
// public front controller
declare(strict_types=1);

session_start();

// Basic error reporting for dev
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Simple autoload (very basic)
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/app/';
    $class = str_replace('\\', '/', $class);
    $paths = [
        $baseDir . $class . '.php',
        $baseDir . 'Controllers/' . basename($class) . '.php',
        $baseDir . 'Core/' . basename($class) . '.php',
        $baseDir . 'Services/' . basename($class) . '.php',
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) {
            require_once $p;
            return;
        }
    }
});

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;

$router = new Router();

// Define routes
$router->get('/', [HomeController::class, 'home']);
$router->get('/auth/login', [AuthController::class, 'login']);
$router->post('/auth/setLogin', [AuthController::class, 'setLogin']);

// Extra: dashboard after login
$router->get('/dashboard', [HomeController::class, 'dashboard']);
$router->get('/auth/logout', [AuthController::class, 'logout']);

// Rutas manager
$router->get('/manager/createDayForm', [\App\Controllers\ManagerController::class, 'createDayForm']);
$router->post('/manager/createDay', [\App\Controllers\ManagerController::class, 'createDay']);
$router->get('/manager/days', [\App\Controllers\ManagerController::class, 'listDays']);

// Ruta para actualizar evaluaciones
$router->post('/manager/update_evaluation', [\App\Controllers\ManagerController::class, 'updateEvaluation']);

// Ruta para ver evaluaciones con parámetro en la URL
$router->get('/manager/evaluations/([0-9]+)', function($day_id) {
    (new \App\Controllers\ManagerController())->viewDayEvaluations($day_id);
});

// Ruta para manejar el parámetro day_id por GET
$router->get('/manager/evaluations', function() {
    $day_id = $_GET['day_id'] ?? null;
    if ($day_id) {
        // Validar que sea numérico
        if (!is_numeric($day_id)) {
            http_response_code(400);
            echo 'ID de día inválido';
            return;
        }
        // Llamar directamente al controlador
        (new \App\Controllers\ManagerController())->viewDayEvaluations($day_id);
    } else {
        http_response_code(400);
        echo 'Falta el parámetro day_id';
    }
});

// Ruta para ver evaluaciones de un empleado
$router->get('/employee/evaluations', [\App\Controllers\EmployeeController::class, 'viewEvaluations']);

// Dispatch (trim base dir like /vaion)
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir && $scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
    $uri = substr($uri, strlen($scriptDir));
    if ($uri === '') { $uri = '/'; }
}
$router->dispatch($method, $uri);
