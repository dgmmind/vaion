<?php /** @var string $title */ require_once __DIR__ . '/../Settings/settings.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
</head>
<body>
  <h1>Home</h1>
  <p>Bienvenido. Ingresa para continuar.</p>
  <a href="<?= BASE_URL ?>/auth/login">Ir a Login</a>
</body>
</html>

<?php
print_r($_SESSION);