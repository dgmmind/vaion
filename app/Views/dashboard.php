<?php /** @var string $title */ /** @var array $user */ require_once __DIR__ . '/../Settings/settings.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
</head>
<body>
  <h1>Dashboard</h1>
  <p>Hola <strong><?= htmlspecialchars($user['name'] ?? $user['username'] ?? 'Usuario') ?></strong>.</p>
  <p>Tu rol: <?= htmlspecialchars($user['role'] ?? 'user') ?></p>
  
  <nav>
    <a href="<?= BASE_URL ?>/" class="nav-link">Home</a>
    <?php if (isset($user['employee_id'])): ?>
      <a href="<?= BASE_URL ?>/employee/evaluations?employee_id=<?= urlencode($user['employee_id']) ?>" class="nav-link">Mis Evaluaciones</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/auth/logout" class="nav-link">Cerrar sesión</a>
  </nav>
  
  <style>
    nav {
      margin: 20px 0;
      padding: 10px 0;
      border-top: 1px solid #eee;
      border-bottom: 1px solid #eee;
    }
    .nav-link {
      display: inline-block;
      margin-right: 15px;
      padding: 8px 15px;
      background-color: #f0f0f0;
      border-radius: 4px;
      text-decoration: none;
      color: #333;
      transition: background-color 0.2s;
    }
    .nav-link:hover {
      background-color: #e0e0e0;
    }
  </style>
</body>
</html>
