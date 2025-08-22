<?php require_once __DIR__ . '/../Settings/settings.php'; ?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Crear Día</title></head>
<body>
<h1>Crear Día</h1>
<form method="post" action="<?= BASE_URL ?>/manager/createDay">
  <!-- Campo oculto con el ID del manager -->
  <input type="hidden" name="manager_id" value="<?= htmlspecialchars($manager_id) ?>">
  
  <p><strong>Manager ID:</strong> <?= htmlspecialchars($manager_id) ?></p>
  
  <div>
    <label>Fecha del día: <input type="date" name="day_date" required></label>
  </div>
  <div>
    <button type="submit">Crear día y evaluaciones</button>
  </div>
</form>
<a href="<?= BASE_URL ?>/manager/days">Ver días</a>
</body>
</html>
