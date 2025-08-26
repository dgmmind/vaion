<?php
require_once 'template/header.php';
require_once "../repository/supabase.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

$supabase = new Supabase();

$pauseReasons = [
  "Almuerzo",
  "Break corto",
  "Reunión",
  "Asunto personal",
  "Otro"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Control de Pausas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-light: #EEF2FF;
            --danger: #EF4444;
            --gray-100: #F9FAFB;
            --gray-200: #F3F4F6;
            --gray-300: #E5E7EB;
            --gray-700: #374151;
            --gray-900: #111827;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--gray-100); color: var(--gray-900); }
        
        .dashboard {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background: white;
            padding: 1.5rem;
            border-right: 1px solid var(--gray-200);
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        h1 { font-size: 1.5rem; font-weight: 600; margin-bottom: 0.5rem; }
        h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; }
        .text-muted { color: var(--gray-700); margin-bottom: 1.5rem; display: block; }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2>Menú</h2>
            <nav>
                <ul style="list-style: none;">
                    <li style="padding: 0.5rem 0;">Inicio</li>
                    <li style="padding: 0.5rem 0;">Historial</li>
                    <li style="padding: 0.5rem 0;">Configuración</li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['name'] ?? 'Usuario') ?></h1>
            <span class="text-muted">Sistema de control de pausas</span>
            
            <div class="card">
                <h2>Estado de Pausa</h2>
                <!-- Existing pause status content will go here -->
            </div>
        </main>
    </div>
</body>
</html>

<?php
require_once 'template/footer.php';
?>
