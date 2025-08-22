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
  <a href="<?= BASE_URL ?>/">Home</a>
  <a href="<?= BASE_URL ?>/auth/logout">Cerrar sesión</a>
</body>
</html>
