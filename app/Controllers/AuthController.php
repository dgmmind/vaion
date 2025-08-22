<?php
namespace App\Controllers;

use App\Services\UserService;

require_once __DIR__ . '/../Settings/settings.php';

class AuthController
{
    public function login(): void
    {
        if (!empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        $title = 'Login';
        $error = isset($_GET['error']) ? 'Credenciales inválidas o usuario inactivo.' : null;
        require __DIR__ . '/../Views/login.php';
    }

    public function setLogin(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            header('Location: ' . BASE_URL . '/auth/login?error=1');
            exit;
        }

        $service = new UserService();
        $user = $service->authenticate($username, $password);

        if (!$user) {
            header('Location: ' . BASE_URL . '/auth/login?error=1');
            exit;
        }

        $_SESSION['user'] = $user;
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}
