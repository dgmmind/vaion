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

// Obtener todos los managers
$managersMap = [];
foreach ($users as $userId => $user) {
    if ($user['role'] === 'admin') {
        $managersMap[$userId] = $user['name'];
    }
}

// Traer todos los días
$days = $supabase->select("days", "day_id, day_date, manager_id")["data"] ?? [];

// Indexar day_id => day_date para filtro rápido
$dayIndex = [];
foreach ($days as $d) { $dayIndex[$d['day_id']] = $d['day_date']; }

// Categories from data
$categoryItems = $category_item ?? [];
$categories = array_keys($categoryItems);

// Calcular métricas por manager y por categoría para la semana (lun-vie)
$managerStats = []; // [manager_id => ['name'=>..., 'perfect'=>int, 'total'=>int, 'percent'=>int, 'employees'=>int]]
foreach ($managersMap as $mid => $mname) {
    $managerStats[$mid] = ['name' => $mname, 'perfect' => 0, 'total' => 0, 'percent' => 0, 'employees' => 0];
}

$categoryStats = []; // [category => ['name'=>..., 'perfect'=>int, 'total'=>int, 'percent'=>int]]
foreach ($categories as $cat) {
    $categoryStats[$cat] = ['name' => $cat, 'perfect' => 0, 'total' => 0, 'percent' => 0];
}

// Obtener empleados de cada manager
$managerEmployees = [];
foreach ($users as $userId => $user) {
    if ($user['role'] === 'admin' && isset($user['employees'])) {
        $managerEmployees[$userId] = array_column($user['employees'], 'id');
        $managerStats[$userId]['employees'] = count($user['employees']);
    }
}

foreach ($supabase->select("evaluations", "employee_id, category, item, day_id")["data"] ?? [] as $e) {
    $eid = $e['employee_id'] ?? null;
    if (!$eid) continue;
    
    // Encontrar a qué manager pertenece este empleado
    $managerId = null;
    foreach ($managerEmployees as $mid => $employeeIds) {
        if (in_array($eid, $employeeIds)) {
            $managerId = $mid;
            break;
        }
    }
    
    if (!$managerId || !isset($managerStats[$managerId])) continue;
    
    $dDate = $dayIndex[$e['day_id']] ?? null;
    if (!$dDate) continue;
    if ($dDate < $dateStart || $dDate > $dateEnd) continue; // fuera de lun-vie

    $managerStats[$managerId]['total'] += 1;
    if (($e['item'] ?? '') === 'PERFECTO') $managerStats[$managerId]['perfect'] += 1;

    $cat = $e['category'] ?? null;
    if ($cat && isset($categoryStats[$cat])) {
        $categoryStats[$cat]['total'] += 1;
        if (($e['item'] ?? '') === 'PERFECTO') $categoryStats[$cat]['perfect'] += 1;
    }
}

foreach ($managerStats as $mid => $s) {
    $managerStats[$mid]['percent'] = $s['total'] > 0 ? round($s['perfect'] / $s['total'] * 100, 2) : 0;
}

foreach ($categoryStats as $cat => $s) {
    $categoryStats[$cat]['percent'] = $s['total'] > 0 ? round($s['perfect'] / $s['total'] * 100, 1) : 0;
}

// Ordenar desc por percent, luego por total, luego por nombre
usort($managerStats, function($a, $b) {
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
    <div class="card">
      <div class="header-card">
        <p>Bienvenido, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!</p>
      </div>

      <div class="body-card">
        <?php if (!empty($managersMap)): ?>
          <div class="card-column-two">
            <!-- Managers izquierda -->
            <div class="column-one" data-section="managers">
              <h3>Top Managers (Semana)</h3>
              <div class="progress-grid">
                <?php foreach ($managerStats as $idx => $stat): ?>
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
                    <div class="description"><?= $stat['perfect'] ?> de <?= $stat['total'] ?> perfectos (<?= $stat['employees'] ?> empleados)</div>
                  </div>
                <?php endforeach; ?>
                <?php if (count($managerStats) === 0): ?>
                  <div class="description">Sin datos para esta semana.</div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Categorías derecha -->
            <div class="column-two" data-section="categories">
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
          <p class="description">No hay managers registrados.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php
require_once 'template/footer.php';
?>
