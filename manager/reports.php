<?php
require_once 'template/header.php';

// Ahora $managerId está disponible desde el header
require_once "../repository/supabase.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

$supabase = new Supabase();

// Traer días del manager
$days = array_filter(
    $supabase->select("days", "day_id, day_date, manager_id")["data"] ?? [],
    fn($d) => $d["manager_id"] === $managerId
);
usort($days, fn($a, $b) => strcmp($a["day_date"], $b["day_date"])); // ordenar por fecha

// Mapa empleados
$manager = $users[$managerId] ?? null;
if (!$manager) die("Manager no encontrado en usuarios");
$employeesMap = array_column($manager["employees"] ?? [], "name", "id");

// --- Filtros ---
$dayStart = $_GET['day_start'] ?? null;
$dayEnd = $_GET['day_end'] ?? null;
$selectedEmployee = $_GET['employee_id'] ?? null;

// Normalizar rango: si solo hay inicio, el fin es igual
if ($dayStart && !$dayEnd) $dayEnd = $dayStart;
if ($dayEnd && !$dayStart) $dayStart = $dayEnd;

$evaluations = [];
if ($dayStart && $dayEnd) {
    foreach ($supabase->select("evaluations", "employee_id, category, checked, item, day_id")["data"] ?? [] as $e) {
        // Traer fecha de ese day_id
        $day = current(array_filter($days, fn($d) => $d["day_id"] == $e["day_id"]));
        if (!$day) continue;

        $date = $day["day_date"];
        if ($date < $dayStart || $date > $dayEnd) continue;
        if ($selectedEmployee && $e["employee_id"] != $selectedEmployee) continue;
        if (!isset($employeesMap[$e["employee_id"]])) continue;

        $evaluations[$e['employee_id']][$e['category']][] = $e['item'];
    }
}

$categoryItems = $category_item ?? [];
$categories = array_keys($categoryItems);
?>

<style>
.reports-container {
    margin: 20px 0;
}

.filter-form {
    margin: 20px 0;
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.filter-form .form-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-form .form-group {
    display: flex;
    flex-direction: column;
}

.filter-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.filter-form input,
.filter-form select {
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
}

.filter-form select {
    min-width: 150px;
}

.filter-form button {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    align-self: end;
}

.results-section {
    margin-top: 30px;
}

.results-title {
    margin-bottom: 20px;
}

.no-results {
    color: #64748b;
    font-style: italic;
}

/* Estilo para el texto debajo de la barra de progreso en tablas y tarjetas */
.progress-text {
    font-size: 12px;
    color: #64748b;
    margin-top: 3px;
}
</style>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <div class="header-card">
        <h1 class="title">Reportes</h1>
        <p class="description">Genera reportes de evaluaciones por período y empleado</p>
      </div>
      
      <form method="GET" class="filter-form" onsubmit="return validateForm()">
        <div class="form-row">
          <div class="form-group">
            <label>Desde:</label>
            <input type="date" id="day_start" name="day_start" value="<?= htmlspecialchars($dayStart) ?>" onchange="updateDayEndMin()">
          </div>

          <div class="form-group">
            <label>Hasta:</label>
            <input type="date" id="day_end" name="day_end" value="<?= htmlspecialchars($dayEnd) ?>">
          </div>

          <div class="form-group">
            <label>Empleado:</label>
            <select name="employee_id">
              <option value="">Todos</option>
              <?php foreach ($employeesMap as $id => $name): ?>
                <option value="<?= $id ?>" <?= $selectedEmployee == $id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit">Filtrar</button>
        </div>
      </form>

        <?php if ($dayStart && $dayEnd): ?>
            <div class="results-section">
                <h3 class="results-title">
                    <?php if ($selectedEmployee): ?>
                        Resultados del Empleado: <?= htmlspecialchars($employeesMap[$selectedEmployee]) ?> 
                        (<?= htmlspecialchars($dayStart) ?> - <?= htmlspecialchars($dayEnd) ?>)
                    <?php else: ?>
                        Resultados del Período: <?= htmlspecialchars($dayStart) ?> - <?= htmlspecialchars($dayEnd) ?>
                    <?php endif; ?>
                </h3>
                
                <?php if (empty($evaluations)): ?>
                    <p class="no-results">No se encontraron evaluaciones para el período seleccionado.</p>
                <?php else: ?>
                    <div class="data-table tree-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Empleado</th>
                                    <?php foreach ($categories as $category): ?>
                                        <th><?= htmlspecialchars($category) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    // Si se seleccionó un empleado específico, mostrar solo ese en la tabla
                                    $employeesForTable = $selectedEmployee ? [$selectedEmployee => $evaluations[$selectedEmployee] ?? []] : $evaluations;
                                ?>
                                <?php foreach ($employeesForTable as $employeeId => $employeeEvals): ?>
                                    <tr>
                                        <td class="employee-name"><?= htmlspecialchars($employeesMap[$employeeId]) ?></td>
                                        <?php foreach ($categories as $category): 
                                            $categoryEvals = $employeeEvals[$category] ?? [];
                                            $perfectCount = count(array_filter($categoryEvals, fn($item) => $item === "PERFECTO"));
                                            $percent = count($categoryEvals) > 0 ? round($perfectCount / count($categoryEvals) * 100) : 0;
                                        ?>
                                            <td>
                                                <div class="progress-bar-container">
                                                    <div class="progress-bar" style="width: <?= $percent ?>%;"><?= $percent ?>%</div>
                                                </div>
                                                <div class="progress-text"><?= $perfectCount ?>/<?= count($categoryEvals) ?></div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="progress-columns">
                    <!-- Empleados a la izquierda -->
                    <div class="progress-section" data-section="employees">
                        <h3>Progreso por Empleado</h3>
                        <div class="progress-grid">
                            <?php 
                                // Armar y ordenar por porcentaje desc (luego total desc, nombre asc)
                                $employeesToShow = $selectedEmployee ? [$selectedEmployee => $employeesMap[$selectedEmployee]] : $employeesMap;
                                $employeeStats = [];
                                foreach ($employeesToShow as $employeeId => $employeeName) {
                                    $totalItems = 0; $perfectItems = 0;
                                    if (!empty($evaluations[$employeeId])) {
                                        foreach ($evaluations[$employeeId] as $category => $items) {
                                            $totalItems += count($items);
                                            $perfectItems += count(array_filter($items, fn($item) => $item === "PERFECTO"));
                                        }
                                    }
                                    $employeeProgress = $totalItems > 0 ? round($perfectItems / $totalItems * 100) : 0;
                                    $employeeStats[] = [
                                        'id' => $employeeId,
                                        'name' => $employeeName,
                                        'percent' => $employeeProgress,
                                        'perfect' => $perfectItems,
                                        'total' => $totalItems,
                                    ];
                                }
                                usort($employeeStats, function($a, $b) {
                                    if ($b['percent'] !== $a['percent']) return $b['percent'] <=> $a['percent'];
                                    if ($b['total'] !== $a['total']) return $b['total'] <=> $a['total'];
                                    return strcmp($a['name'], $b['name']);
                                });
                                $empIndex = 0;
                            ?>
                            <?php foreach ($employeeStats as $stat): ?>
                                <?php
                                    if ($empIndex === 0) { $empBadge = 'badge-gold'; $empIcon = 'award'; $empLabel = '1º'; }
                                    elseif ($empIndex === 1) { $empBadge = 'badge-diamond'; $empIcon = 'zap'; $empLabel = '2º'; }
                                    elseif ($empIndex === 2) { $empBadge = 'badge-silver'; $empIcon = 'star'; $empLabel = '3º'; }
                                    else { $empBadge = 'badge-bronze'; $empIcon = 'award'; $empLabel = ''; }
                                ?>
                                <div class="progress-card">
                                    <div class="card-header">
                                        <h4><?= htmlspecialchars($stat['name']) ?></h4>
                                        <span class="badge <?= $empBadge ?>">
                                            <i data-feather="<?= $empIcon ?>"></i><?= $empLabel ? ' ' . $empLabel : '' ?>
                                        </span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="employee-progress-bar" style="width: <?= $stat['percent'] ?>%;"><?= $stat['percent'] ?>%</div>
                                    </div>
                                    <div class="description">
                                        <?php if ($stat['total'] > 0): ?>
                                            <?= $stat['perfect'] ?> de <?= $stat['total'] ?> perfectos
                                        <?php else: ?>
                                            Sin evaluaciones en este período
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php $empIndex++; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Categorías a la derecha -->
                    <div class="progress-section" data-section="categories">
                        <h3>Resumen por Categoría</h3>
                        <div class="progress-grid">
                            <?php 
                                // Construir y ordenar categorías por % desc, luego total desc, nombre asc
                                $categoryStats = [];
                                foreach ($categories as $category) {
                                    $categoryTotal = 0; $categoryPerfect = 0;
                                    foreach ($evaluations as $employeeEvals) {
                                        $categoryEvals = $employeeEvals[$category] ?? [];
                                        $categoryTotal += count($categoryEvals);
                                        $categoryPerfect += count(array_filter($categoryEvals, fn($item) => $item === "PERFECTO"));
                                    }
                                    $categoryPercent = $categoryTotal > 0 ? round($categoryPerfect / $categoryTotal * 100) : 0;
                                    $categoryStats[] = [
                                        'name' => $category,
                                        'percent' => $categoryPercent,
                                        'perfect' => $categoryPerfect,
                                        'total' => $categoryTotal,
                                    ];
                                }
                                usort($categoryStats, function($a, $b) {
                                    if ($b['percent'] !== $a['percent']) return $b['percent'] <=> $a['percent'];
                                    if ($b['total'] !== $a['total']) return $b['total'] <=> $a['total'];
                                    return strcmp($a['name'], $b['name']);
                                });
                                $catIndex = 0;
                            ?>
                            <?php foreach ($categoryStats as $stat): ?>
                                <?php
                                    if ($catIndex === 0) { $catBadge = 'badge-gold'; $catIcon = 'award'; $catLabel = '1º'; }
                                    elseif ($catIndex === 1) { $catBadge = 'badge-diamond'; $catIcon = 'zap'; $catLabel = '2º'; }
                                    elseif ($catIndex === 2) { $catBadge = 'badge-silver'; $catIcon = 'star'; $catLabel = '3º'; }
                                    else { $catBadge = 'badge-bronze'; $catIcon = 'award'; $catLabel = ''; }
                                ?>
                                <div class="progress-card">
                                    <div class="card-header">
                                        <h4><?= htmlspecialchars($stat['name']) ?></h4>
                                        <span class="badge <?= $catBadge ?>">
                                            <i data-feather="<?= $catIcon ?>"></i><?= $catLabel ? ' ' . $catLabel : '' ?>
                                        </span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: <?= $stat['percent'] ?>%;"><?= $stat['percent'] ?>%</div>
                                    </div>
                                    <div class="description"><?= $stat['perfect'] ?> de <?= $stat['total'] ?> perfectos</div>
                                </div>
                                <?php $catIndex++; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
  
  
  <script>
  function updateDayEndMin() {
      const dayStart = document.getElementById('day_start');
      const dayEnd = document.getElementById('day_end');
      
      if (dayStart && dayEnd) {
          // Establecer la fecha mínima para day_end
          dayEnd.min = dayStart.value;
          
          // Si day_end tiene una fecha anterior a day_start, ajustarla
          if (dayEnd.value && dayStart.value && dayEnd.value < dayStart.value) {
              dayEnd.value = dayStart.value;
          }
      }
  }

  function validateForm() {
      const dayStart = document.getElementById('day_start').value;
      const dayEnd = document.getElementById('day_end').value;
      
      if (!dayStart || !dayEnd) {
          alert('Por favor selecciona ambas fechas (desde y hasta)');
          return false;
      }
      
      if (dayEnd < dayStart) {
          alert('La fecha de fin no puede ser anterior a la fecha de inicio');
          return false;
      }
      
      return true;
  }

  // Ejecutar al cargar la página para mantener la restricción
  document.addEventListener('DOMContentLoaded', function() {
      updateDayEndMin();
  });

  // También ejecutar cuando cambie la fecha de inicio
  document.addEventListener('change', function(e) {
      if (e.target.id === 'day_start') {
          updateDayEndMin();
      }
  });
  </script>

<?php
require_once 'template/footer.php';
?>