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
    <div class="card">
      <!-- Header Card: Filtros de Búsqueda -->
      <div class="header-card">
        <div class="title">Filtros de Búsqueda</div>
        <div class="description">Selecciona el tipo de búsqueda y fechas para ver tus evaluaciones</div>
        <form method="GET" class="form form--horizontal form--filters">
          <div class="form-row">
            <div class="form-group">
              <label>Tipo de búsqueda:</label>
              <select name="search_type" onchange="toggleSearchFields(this.value)">
                <option value="today" <?= $search_type === 'today' ? 'selected' : '' ?>>Día Actual</option>
                <option value="range" <?= $search_type === 'range' ? 'selected' : '' ?>>Rango de Fechas</option>
              </select>
            </div>
            
            <!-- Campos para rango de fechas -->
            <div id="range-fields" class="form-row" style="display: <?= $search_type === 'range' ? 'flex' : 'none' ?>;">
              <div class="form-group">
                <label>Fecha Inicio:</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Fecha Fin:</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>">
              </div>
            </div>
          </div>
          
          <div>
            <button type="submit" class="btn btn-primary">
              Buscar
            </button>
          </div>
        </form>
      </div>

          <?php if ($search_type === 'today' && !empty($daysToShow)): ?>
       
        <?php 
        $todayEvaluations = $evaluations[array_keys($evaluations)[0]] ?? [];
        if (!empty($todayEvaluations)): 
        ?>
          <!-- Body Card: Tabla de Evaluaciones -->
          <div class="body-card">
            <div class="table-container">
              <div class="table-title">
                <h3 class="title">Evaluaciones del Día Actual</h3>
                <p class="description"><?= htmlspecialchars($today) ?></p>
              </div>
            <div class="data-table tree-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Empleado ID</th>
                    <?php foreach ($categorias as $cat): ?>
                      <th colspan="2"><?= htmlspecialchars($cat) ?></th>
                    <?php endforeach; ?>
                    <th>PROMEDIO</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><?= htmlspecialchars($user_id) ?></td>
                    <?php 
                    $totalCategories = 0;
                    $perfectScores = 0;
                    foreach ($categorias as $cat):
                      $eval = $todayEvaluations[$cat] ?? null;
                      $item = $eval['item'] ?? "";
                      $checked = $eval['checked'] ?? false;
                      if ($item === 'PERFECTO') $perfectScores++;
                      $totalCategories++;
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
                    <?php endforeach; 
                    $average = $totalCategories > 0 ? round(($perfectScores / $totalCategories) * 100) : 0;
                    ?>
                    <td class="text-center font-weight-bold" style="background-color: #f8fafc;">
                      <?= $average ?>%
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            </div>
          </div>
        <?php else: ?>
          <!-- Body Card: Sin Evaluaciones -->
          <div class="body-card">
            <div class="progress-section">
              <h4>No hay evaluaciones para el día actual</h4>
              <p style="text-align: center; color: #64748b; margin: 20px 0;">
                Aún no se han registrado evaluaciones para hoy (<?= htmlspecialchars($today) ?>).
              </p>
            </div>
          </div>
        <?php endif; ?>
        
      <?php elseif ($search_type === 'range' && !empty($daysToShow)): ?>
      
        
        <!-- Body Card: Tabla Unificada de Evaluaciones -->
        <div class="body-card">
          <div class="table-container">
            <div class="table-title">
              <h3 class="title">Evaluaciones del Rango</h3>
              <p class="description"><?= count($daysToShow) ?> día(s) seleccionado(s)</p>
            </div>
            <div class="data-table">
            <table>
              <thead>
                <tr>
                  <th>FECHA</th>
                  <th>EMPLEADO ID</th>
                  <?php foreach ($categorias as $cat): ?>
                    <th colspan="2"><?= htmlspecialchars($cat) ?></th>
                  <?php endforeach; ?>
                  <th>PROMEDIO</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($daysToShow as $day): ?>
                  <?php 
                  $dayEvaluations = $evaluations[$day['day_id']] ?? [];
                  if (!empty($dayEvaluations)): 
                  ?>
                    <tr>
                      <td class="date-cell" style="font-weight: 600; color: #1f2937; border-right: 2px solid #e5e7eb;">
                        <?= htmlspecialchars($day['day_date']) ?>
                      </td>
                      <td><?= htmlspecialchars($user_id) ?></td>
                      <?php 
                      $totalCategories = 0;
                      $perfectScores = 0;
                      foreach ($categorias as $cat):
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
                          <td class="select-cell">
                            <?php 
                            $item = $eval ? $eval['item'] : 'N/A';
                            if ($item === 'PERFECTO') $perfectScores++;
                            $totalCategories++;
                            echo htmlspecialchars($item); 
                            ?>
                          </td>
                        <?php endforeach; 
                        $average = $totalCategories > 0 ? round(($perfectScores / $totalCategories) * 100) : 0;
                        ?>
                        <td class="text-center font-weight-bold" style="background-color: #f8fafc;">
                          <?= $average ?>%
                        </td>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <td class="date-cell" style="font-weight: 600; color: #1f2937; border-right: 2px solid #e5e7eb;">
                        <?= htmlspecialchars($day['day_date']) ?>
                      </td>
                      <td colspan="<?= (count($categorias) * 2) + 2 ?>" style="text-align: center; color: #64748b; padding: 16px;">
                        No hay evaluaciones para este día
                      </td>
                    </tr>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          </div>
        </div>
  
<?php elseif ($search_type === 'today' && empty($daysToShow)): ?>
  <!-- Header Card: Sin Días Disponibles -->
  <div class="header-card">
    <div class="title">No hay días disponibles para hoy</div>
    <div class="description">No se han configurado días de evaluación para la fecha actual</div>
  </div>
  
  <!-- Body Card: Mensaje de Información -->
  <div class="body-card">
    <div class="progress-section">
      <p style="text-align: center; color: #64748b; margin: 20px 0;">
        No se han configurado días de evaluación para la fecha actual (<?= htmlspecialchars($today) ?>).
      </p>
    </div>
  </div>
  
<?php elseif ($search_type === 'range' && ($start_date || $end_date) && empty($daysToShow)): ?>
  <!-- Header Card: Sin Evaluaciones Encontradas -->
  <div class="header-card">
    <div class="title">No se encontraron evaluaciones</div>
    <div class="description">No hay evaluaciones para el rango de fechas seleccionado</div>
  </div>
  
  <!-- Body Card: Mensaje de Información -->
  <div class="body-card">
    <div class="progress-section">
      <p style="text-align: center; color: #64748b; margin: 20px 0;">
        No hay evaluaciones para el rango de fechas seleccionado.
      </p>
    </div>
  </div>
  
<?php else: ?>
  <!-- Header Card: Selecciona Opción -->
  <div class="header-card">
    <div class="title">Selecciona una opción de búsqueda</div>
    <div class="description">Elige el tipo de búsqueda que deseas realizar</div>
  </div>
  
  <!-- Body Card: Mensaje de Información -->
  <div class="body-card">
    <div class="progress-section">
      <p style="text-align: center; color: #64748b; margin: 20px 0;">
        Elige el tipo de búsqueda que deseas realizar arriba.
      </p>
    </div>
  </div>
<?php endif; ?>

    </div>
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