<?php
require_once 'template/header.php';
require_once "../repository/supabase_employee.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

$user_id = $_SESSION["user_id"] ?? "22056"; // usuario actual
$search_type = $_GET['search_type'] ?? 'today'; // 'today', 'range'
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

if (!$user_id) die("Usuario no encontrado en sesión");

$supabase = new Supabase();

// Traer días disponibles
$daysResponse = $supabase->select("days", "day_id, day_date, manager_id");
$days = $daysResponse["data"] ?? [];

// Obtener día actual
$today = date('Y-m-d');

// Lógica según el tipo de búsqueda
$evaluations = [];
$daysToShow = [];

if ($search_type === 'today') {
    // Buscar día actual
    $todayDays = array_filter($days, fn($d) => $d['day_date'] == $today);
    $daysToShow = array_values($todayDays);
    
    if (!empty($daysToShow)) {
        $evaluations = getEvaluationsForRange($supabase, $user_id, array_column($daysToShow, 'day_id'));
    }
} elseif ($search_type === 'range' && $start_date && $end_date) {
    // Rango de fechas (incluye día específico si start_date = end_date)
    $rangeDays = array_filter($days, function($d) use ($start_date, $end_date) {
        return $d['day_date'] >= $start_date && $d['day_date'] <= $end_date;
    });
    $daysToShow = array_values($rangeDays);
    
    if (!empty($daysToShow)) {
        $evaluations = getEvaluationsForRange($supabase, $user_id, array_column($daysToShow, 'day_id'));
    }
}

// Función para obtener evaluaciones de un rango
function getEvaluationsForRange($supabase, $user_id, $day_ids) {
    $evaluations = [];
    $evalResponse = $supabase->select("evaluations", "evaluation_id, employee_id, day_id, category, checked, item");
    
    foreach ($evalResponse["data"] as $e) {
        if ($e['employee_id'] == $user_id && in_array($e['day_id'], $day_ids)) {
            $evaluations[$e['day_id']][$e['category']] = [
                'evaluation_id' => $e['evaluation_id'],
                'item'          => $e['item'],
                'checked'       => (bool)$e['checked']
            ];
        }
    }
    return $evaluations;
}

// Categorías en orden
$categorias = array_keys($category_item);
?>

<!-- Main Content -->
<main class="main-content">
  <div class="container">
    <!-- Selector de tipo de búsqueda -->
    <div class="day-selector" style="margin-bottom: 20px;">
      <form method="GET" style="display: flex; gap: 16px; align-items: end; flex-wrap: wrap;">
        <div>
          <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Tipo de búsqueda:</label>
          <select name="search_type" onchange="toggleSearchFields(this.value)" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; min-width: 150px;">
            <option value="today" <?= $search_type === 'today' ? 'selected' : '' ?>>Día Actual</option>
            <option value="range" <?= $search_type === 'range' ? 'selected' : '' ?>>Rango de Fechas</option>
          </select>
        </div>
        
        <!-- Campos para rango de fechas -->
        <div id="range-fields" style="display: <?= $search_type === 'range' ? 'flex' : 'none' ?>; gap: 16px;">
          <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Fecha Inicio:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date ?? '') ?>" 
                   style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; min-width: 150px;">
          </div>
          <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Fecha Fin:</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>" 
                   style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; min-width: 150px;">
          </div>
        </div>
        
        <div>
          <button type="submit" class="button" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px;">
            Buscar
          </button>
        </div>
      </form>
    </div>

    <?php if ($search_type === 'today' && !empty($daysToShow)): ?>
      <h3>Evaluaciones del Día Actual: <?= htmlspecialchars($today) ?></h3>
      
      <?php 
      $todayEvaluations = $evaluations[array_keys($evaluations)[0]] ?? [];
      if (!empty($todayEvaluations)): 
      ?>
        <div class="data-table tree-wrapper">
          <table>
            <thead>
              <tr>
                <th>Empleado ID</th>
                <?php foreach ($categorias as $cat): ?>
                  <th colspan="2"><?= htmlspecialchars($cat) ?></th>
                <?php endforeach; ?>
              </tr>
              <tr>
                <th></th>
                <?php foreach ($categorias as $cat): ?>
                  <th>Checked</th>
                  <th>Item</th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= htmlspecialchars($user_id) ?></td>
                <?php foreach ($categorias as $cat):
                  $eval = $todayEvaluations[$cat] ?? null;
                  $item = $eval['item'] ?? "";
                  $checked = $eval['checked'] ?? false;
                ?>
                  <td class="checkbox-cell <?= $checked ? 'checked-green' : 'checked-red' ?>">
                    <label>
                      <input type="checkbox" disabled <?= $checked ? 'checked' : '' ?>>
                      <span class="feather-icon" data-feather="<?= $checked ? 'check-square' : 'square' ?>"></span>
                    </label>
                  </td>
                  <td>
                    <?= htmlspecialchars($item) ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="progress-section">
          <h4>No hay evaluaciones para el día actual</h4>
          <p style="text-align: center; color: #64748b; margin: 20px 0;">
            Aún no se han registrado evaluaciones para hoy (<?= htmlspecialchars($today) ?>).
          </p>
        </div>
      <?php endif; ?>
      
    <?php elseif ($search_type === 'range' && !empty($daysToShow)): ?>
      <h3>Evaluaciones del Rango: <?= count($daysToShow) ?> día(s)</h3>
      
      <?php foreach ($daysToShow as $day): ?>
        <div style="margin-bottom: 24px;">
          <h4 style="margin-bottom: 16px; color: #1f2937; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;">
            Día: <?= htmlspecialchars($day['day_date']) ?>
          </h4>
          
          <?php 
          $dayEvaluations = $evaluations[$day['day_id']] ?? [];
          if (!empty($dayEvaluations)): 
          ?>
            <div class="data-table">
              <table>
                <thead>
                  <tr>
                    <th>Empleado ID</th>
                    <?php foreach ($categorias as $cat): ?>
                      <th colspan="2"><?= htmlspecialchars($cat) ?></th>
                    <?php endforeach; ?>
                  </tr>
                  <tr>
                    <th></th>
                    <?php foreach ($categorias as $cat): ?>
                      <th>Checked</th>
                      <th>Item</th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><?= htmlspecialchars($user_id) ?></td>
                    <?php foreach ($categorias as $cat):
                      $eval = $dayEvaluations[$cat] ?? null;
                      $item = $eval['item'] ?? "";
                      $checked = $eval['checked'] ?? false;
                    ?>
                      <td class="checkbox-cell <?= $checked ? 'checked-green' : 'checked-red' ?>">
                        <label>
                          <input type="checkbox" disabled <?= $checked ? 'checked' : '' ?>>
                          <span class="feather-icon" data-feather="<?= $checked ? 'check-square' : 'square' ?>"></span>
                        </label>
                  </td>
                  <td>
                    <?= htmlspecialchars($item) ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div style="padding: 16px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
          <p style="text-align: center; color: #64748b; margin: 0;">
            No hay evaluaciones para este día.
          </p>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  
<?php elseif ($search_type === 'today' && empty($daysToShow)): ?>
  <div class="progress-section">
    <h3>No hay días disponibles para hoy</h3>
    <p style="text-align: center; color: #64748b; margin: 20px 0;">
      No se han configurado días de evaluación para la fecha actual (<?= htmlspecialchars($today) ?>).
    </p>
  </div>
  
<?php elseif ($search_type === 'range' && ($start_date || $end_date) && empty($daysToShow)): ?>
  <div class="progress-section">
    <h3>No se encontraron evaluaciones</h3>
    <p style="text-align: center; color: #64748b; margin: 20px 0;">
      No hay evaluaciones para el rango de fechas seleccionado.
    </p>
  </div>
  
<?php else: ?>
  <div class="progress-section">
    <h3>Selecciona una opción de búsqueda</h3>
    <p style="text-align: center; color: #64748b; margin: 20px 0;">
      Elige el tipo de búsqueda que deseas realizar arriba.
    </p>
  </div>
<?php endif; ?>

  </div>
</main>

<script>
function toggleSearchFields(searchType) {
  const rangeFields = document.getElementById('range-fields');
  
  if (searchType === 'range') {
    rangeFields.style.display = 'flex';
  } else {
    rangeFields.style.display = 'none';
  }
}
</script>

<?php
require_once 'template/footer.php';
?>