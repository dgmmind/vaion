<?php /** @var string $title */ /** @var ?string $error */ require_once __DIR__ . '/../Settings/settings.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
</head>
<body>
  <h1>Login</h1>
  <?php if(!empty($error)): ?>
    <div><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= BASE_URL ?>/auth/setLogin">
    <label for="username">Usuario</label>
    <input type="text" id="username" name="username" autocomplete="username" required>

    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" autocomplete="current-password" required>

    <button type="submit">Ingresar</button>
    <a href="<?= BASE_URL ?>/">Home</a>
  </form>
</body>
</html>
