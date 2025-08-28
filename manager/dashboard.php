<?php
require_once 'template/header.php';
require_once "../repository/supabase.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

// Helper: obtener lunes y viernes de la semana actual
$today = new DateTime();
// ISO-8601: 1 (lunes) a 7 (domingo)
$dayOfWeek = (int)$today->format('N');
$monday = clone $today; $monday->modify('-' . ($dayOfWeek - 1) . ' days');
$friday = clone $monday; $friday->modify('+4 days');

$dateStart = $monday->format('Y-m-d');
$dateEnd = $friday->format('Y-m-d');

$supabase = new Supabase();

// Empleados del manager
$manager = $users[$managerId] ?? null;
$employeesMap = $manager ? array_column($manager["employees"] ?? [], "name", "id") : [];

// Traer días del manager
$days = array_filter(
    $supabase->select("days", "day_id, day_date, manager_id")["data"] ?? [],
    fn($d) => $d["manager_id"] === $managerId
);

// Indexar day_id => day_date para filtro rápido
$dayIndex = [];
foreach ($days as $d) { $dayIndex[$d['day_id']] = $d['day_date']; }

// Categories from data
$categoryItems = $category_item ?? [];
$categories = array_keys($categoryItems);

// Calcular métricas por empleado y por categoría para la semana (lun-vie)
$employeeStats = []; // [employee_id => ['name'=>..., 'perfect'=>int, 'total'=>int, 'percent'=>int]]
foreach ($employeesMap as $eid => $ename) {
    $employeeStats[$eid] = ['name' => $ename, 'perfect' => 0, 'total' => 0, 'percent' => 0];
}

$categoryStats = []; // [category => ['name'=>..., 'perfect'=>int, 'total'=>int, 'percent'=>int]]
foreach ($categories as $cat) {
    $categoryStats[$cat] = ['name' => $cat, 'perfect' => 0, 'total' => 0, 'percent' => 0];
}

foreach ($supabase->select("evaluations", "employee_id, category, item, day_id")["data"] ?? [] as $e) {
    $eid = $e['employee_id'] ?? null;
    if (!$eid || !isset($employeesMap[$eid])) continue; // solo empleados del manager
    $dDate = $dayIndex[$e['day_id']] ?? null;
    if (!$dDate) continue;
    if ($dDate < $dateStart || $dDate > $dateEnd) continue; // fuera de lun-vie

    $employeeStats[$eid]['total'] += 1;
    if (($e['item'] ?? '') === 'PERFECTO') $employeeStats[$eid]['perfect'] += 1;

    $cat = $e['category'] ?? null;
    if ($cat && isset($categoryStats[$cat])) {
        $categoryStats[$cat]['total'] += 1;
        if (($e['item'] ?? '') === 'PERFECTO') $categoryStats[$cat]['perfect'] += 1;
    }
}

foreach ($employeeStats as $eid => $s) {
    $employeeStats[$eid]['percent'] = $s['total'] > 0 ? (int)round($s['perfect'] / $s['total'] * 100) : 0;
}

foreach ($categoryStats as $cat => $s) {
    $categoryStats[$cat]['percent'] = $s['total'] > 0 ? (int)round($s['perfect'] / $s['total'] * 100) : 0;
}

// Ordenar desc por percent, luego por total, luego por nombre
usort($employeeStats, function($a, $b) {
    if ($b['percent'] !== $a['percent']) return $b['percent'] - $a['percent'];
    if ($b['total'] !== $a['total']) return $b['total'] - $a['total'];
    return strcmp($a['name'], $b['name']);
});

$categoryStatsList = array_values($categoryStats);
usort($categoryStatsList, function($a, $b) {
    if ($b['percent'] !== $a['percent']) return $b['percent'] - $a['percent'];
    if ($b['total'] !== $a['total']) return $b['total'] - $a['total'];
    return strcmp($a['name'], $b['name']);
});
?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <div class="header-card">
        <h1 class="title">Dashboard</h1>
        <p class="description">Resumen de desempeño semanal (Lun-Vie: <?= htmlspecialchars($dateStart) ?> a <?= htmlspecialchars($dateEnd) ?>)</p>
        <p>Bienvenido, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!</p>
      </div>

      <!-- Section Title and Controls -->
      <div class="section-title">
        <div class="section-title-header">
          <h2>Dashboard</h2>
          <span id="pausedBadge" style="display: none; background: #fef2f2; color: #dc2626; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; align-items: center; gap: 4px;">
            <i data-feather="pause" style="width: 14px; height: 14px; stroke-width: 3;"></i>
            <span>EN PAUSA</span>
          </span>
        </div>
        <div class="switch-container">
          <span style="font-size: 14px; color: #64748b;">Pausar sistema</span>
          <label class="switch">
            <input type="checkbox" id="systemPauseSwitch">
            <span class="slider round"></span>
          </label>
        </div>
      </div>

      <?php if (!empty($employeesMap)): ?>
        <div class="progress-columns">
          <!-- Empleados izquierda -->
          <div class="progress-section" data-section="employees">
            <h3>Top Empleados (Semana)</h3>
            <div class="progress-grid">
              <?php foreach ($employeeStats as $idx => $stat): ?>
                <?php
                  if ($idx === 0) { $badgeClass = 'badge-gold'; $icon = 'award'; $label = '1º'; }
                  elseif ($idx === 1) { $badgeClass = 'badge-diamond'; $icon = 'zap'; $label = '2º'; }
                  elseif ($idx === 2) { $badgeClass = 'badge-silver'; $icon = 'star'; $label = '3º'; }
                  else { $badgeClass = 'badge-bronze'; $icon = 'award'; $label = ''; }
                ?>
                <div class="progress-card">
                  <div class="card-header">
                    <h4><?= htmlspecialchars($stat['name']) ?></h4>
                    <span class="badge <?= $badgeClass ?>">
                      <i data-feather="<?= $icon ?>"></i><?= $label ? ' ' . $label : '' ?>
                    </span>
                  </div>
                  <div class="progress-bar-container">
                    <div class="employee-progress-bar" style="width: <?= $stat['percent'] ?>%;"><?= $stat['percent'] ?>%</div>
                  </div>
                  <div class="description"><?= $stat['perfect'] ?> de <?= $stat['total'] ?> perfectos</div>
                </div>
              <?php endforeach; ?>
              <?php if (count($employeeStats) === 0): ?>
                <div class="description">Sin datos para esta semana.</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Categorías derecha -->
          <div class="progress-section" data-section="categories">
            <h3>Top Categorías (Semana)</h3>
            <div class="progress-grid">
              <?php foreach ($categoryStatsList as $idx => $stat): ?>
                <?php
                  if ($idx === 0) { $badgeClass = 'badge-gold'; $icon = 'award'; $label = '1º'; }
                  elseif ($idx === 1) { $badgeClass = 'badge-diamond'; $icon = 'zap'; $label = '2º'; }
                  elseif ($idx === 2) { $badgeClass = 'badge-silver'; $icon = 'star'; $label = '3º'; }
                  else { $badgeClass = 'badge-bronze'; $icon = 'award'; $label = ''; }
                ?>
                <div class="progress-card">
                  <div class="card-header">
                    <h4><?= htmlspecialchars($stat['name']) ?></h4>
                    <span class="badge <?= $badgeClass ?>">
                      <i data-feather="<?= $icon ?>"></i><?= $label ? ' ' . $label : '' ?>
                    </span>
                  </div>
                  <div class="progress-bar-container">
                    <div class="category-progress-bar" style="width: <?= $stat['percent'] ?>%;"><?= $stat['percent'] ?>%</div>
                  </div>
                  <div class="description"><?= $stat['perfect'] ?> de <?= $stat['total'] ?> perfectos</div>
                </div>
              <?php endforeach; ?>
              <?php if (count($categoryStatsList) === 0): ?>
                <div class="description">Sin datos para esta semana.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php else: ?>
        <p class="description">No hay empleados asignados.</p>
      <?php endif; ?>
    </div>
  </main>

<?php
require_once 'template/footer.php';
?>