<?php
session_start();

// Incluir el archivo de usuarios
require_once 'data/users.php';

// Verificar si ya hay una sesión activa
if (isset($_SESSION['user_id'])) {
    // Redirigir según el rol
    if ($_SESSION['role'] === 'admin') {
        header('Location: manager/dashboard.php');
    } else {
        header('Location: employee/dashboard.php');
    }
    exit();
}

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = null;
    $userRole = null;
    $managerKey = null;
    
    // Buscar el usuario en el array de usuarios
    foreach ($users as $managerKey => $manager) {
        // Verificar si es el manager
        if ($manager['username'] === $username && $manager['password'] === $password) {
            $user = $manager;
            $userRole = 'admin';
            break;
        }
        
        // Verificar si es un empleado
        foreach ($manager['employees'] as $employee) {
            if ($employee['username'] === $username && $employee['password'] === $password) {
                $user = $employee;
                $userRole = 'user';
                break 2;
            }
        }
    }
    
    // Si se encontró el usuario
    if ($user && $userRole && $managerKey) {
        // Iniciar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $userRole;
        
        // Solo los managers necesitan manager_id
        if ($userRole === 'admin') {
            $_SESSION['manager_id'] = $managerKey;
        }
        
        // Redirigir según el rol
        if ($userRole === 'admin') {
            header('Location: manager/dashboard.php');
        } else {
            header('Location: employee/dashboard.php');
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Evaluación</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        body::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" fill="currentColor"/>
                    <path d="M6 20.7021C6.82017 18.8687 8.17732 17.3331 9.87434 16.3114C11.5714 15.2897 13.5206 14.8286 15.4549 14.9883" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            
            <h1 class="login-title">Iniciar Sesión</h1>
            
            <?php if (isset($error)): ?>
                <div class="login-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="form form--login">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required autofocus placeholder="Ingresa tu usuario">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Continuar
                </button>
            </form>
            
            <div class="login-footer">
                <p>¿Necesitas ayuda? <a href="#" style="color: #3b82f6; text-decoration: none;">Contáctanos</a></p>
            </div>
        </div>
    </div>
</body>
</html>