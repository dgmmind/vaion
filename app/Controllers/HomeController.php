<?php
namespace App\Controllers;

require_once __DIR__ . '/../Settings/settings.php';

class HomeController
{
    public function home(): void
    {
        $title = 'Home';
        require __DIR__ . '/../Views/home.php';
    }

    public function dashboard(): void
    {
        if (empty($_SESSION['user'])) {
            echo '<pre>DEBUG SESSION: ' . print_r($_SESSION, true) . "\n";
            echo 'Redirigiendo a: ' . (BASE_URL . '/auth/login') . '</pre>';
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        $user = $_SESSION['user'];
        $title = 'Dashboard';
        require __DIR__ . '/../Views/dashboard.php';
    }
}
