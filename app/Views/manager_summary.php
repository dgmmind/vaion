<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Resumen') ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 16px; }
    .container { width: 100%; overflow-x: auto; }
    .filters { margin-bottom: 10px; }
    .filters input { padding: 4px; }
    .btn { padding: 6px 10px; background:#3498db; color:#fff; border:none; border-radius:4px; cursor:pointer; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 6px; text-align: center; }
    th { background: #2c3e50; color:#fff; }
    .ok { color: #16a34a; font-weight: bold; }
    .mid { color: #f59e0b; font-weight: bold; }
    .bad { color: #dc2626; font-weight: bold; }
    .left { text-align: left; }
  </style>
</head>
<body>
  <div class="container">
    <h1><?= htmlspecialchars($title ?? 'Resumen') ?></h1>

    <form method="get" class="filters" action="<?= BASE_URL ?>/manager/summary">
      <label>Desde: <input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>"></label>
      <label>Hasta: <input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>"></label>
      <button class="btn" type="submit">Filtrar</button>
    </form>

    <?php
    $categorias = $categorias ?? [];
    $percentTeam = $percentTeam ?? [];
    $percentByEmp = $percentByEmp ?? [];
    $employeeNames = $employeeNames ?? [];

    print_r($employeeNames);

    $fmt = function($v){
      if ($v === null) return '-';
      $cls = $v >= 90 ? 'ok' : ($v >= 70 ? 'mid' : 'bad');
      return "<span class='$cls'>" . $v . "%</span>";
    };
    ?>

    <h3>Equipo (por categoría)</h3>
    <table>
      <thead>
        <tr>
          <?php foreach ($categorias as $cat): ?>
            <th><?= htmlspecialchars($cat) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <tr>
          <?php foreach ($categorias as $cat): ?>
            <td><?= $fmt($percentTeam[$cat] ?? null) ?></td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>

    <h3 style="margin-top:18px;">Por empleado y categoría</h3>
    <table>
      <thead>
        <tr>
          <th class="left">Empleado</th>
          <?php foreach ($categorias as $cat): ?>
            <th><?= htmlspecialchars($cat) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($percentByEmp as $eid => $cats): ?>
          <tr>
            <td class="left"><?= htmlspecialchars($employeeNames[$eid] ?? ('Empleado ' . $eid)) ?></td>
            <?php foreach ($categorias as $cat): ?>
              <td><?= $fmt($cats[$cat] ?? null) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
