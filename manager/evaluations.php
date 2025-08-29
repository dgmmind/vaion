<?php
/**
 * Manager Evaluations Page
 * 
 * Esta página permite a los managers ver y gestionar las evaluaciones
 * de sus empleados para días específicos.
 * 
 * Funcionalidades:
 * - Filtra días por manager_id de la sesión (MGR001, MGR002, etc.)
 * - Muestra evaluaciones solo de empleados del manager
 * - Permite actualizar evaluaciones en tiempo real
 * - Calcula promedios por empleado y por categoría
 * 
 * Nota: Usa $managerId que viene del header
 */

require_once 'template/header.php';

// Ahora $managerId está disponible desde el header
require_once "../repository/supabase.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

$dayId = $_GET['day_id'] ?? null;

$supabase = new Supabase();

// Buscar el manager en el array de usuarios por la clave (MGR001, MGR002, etc.)
$manager = null;
$managerKey = null;
foreach ($users as $key => $user) {
    if ($key === $managerId) {
        $manager = $user;
        $managerKey = $key;
        break;
    }
}

if (!$manager) {
    die("Manager no encontrado en usuarios");
}

// Traer días del manager
$days = array_filter(
    $supabase->select("days", "day_id, day_date, manager_id")["data"] ?? [],
    fn($d) => $d["manager_id"] === $managerId
);

// Validar día seleccionado
if ($dayId && !array_filter($days, fn($d) => $d['day_id'] == $dayId)) {
    die("El día seleccionado no existe para este manager.");
}

// Mapa de empleados del manager
$employeesMap = array_column($manager["employees"] ?? [], "name", "id");

// Traer evaluaciones
$evaluations = [];
if ($dayId) {
    foreach ($supabase->select("evaluations", "evaluation_id, employee_id, category, checked, item, day_id")["data"] ?? [] as $e) {
        if (isset($employeesMap[$e["employee_id"]]) && $e["day_id"] == $dayId) {
            $evaluations[$e['employee_id']][$e['category']] = [
                'evaluation_id' => $e['evaluation_id'],
                'item' => $e['item'],
                'checked' => (bool)$e['checked']
            ];
        }
    }
}

// Usar la variable correcta de evaluations.php
$categoryItems = $category_item ?? [];
$categories = array_keys($categoryItems);

?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      
      <div class="card">
        <div class="header-card">
            <h3>Días del Manager</h3>
          <form method="GET" class="day-selector">
            <select name="day_id" onchange="this.form.submit()">
                <option value="">Selecciona un día</option>
                <?php foreach ($days as $day): ?>
                    <option value="<?= htmlspecialchars($day['day_id']) ?>"
                        <?= $dayId == $day['day_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($day['day_date']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
          </form>
        </div>
        <div class="body-card">
          <div class="table-container">
            <?php if ($dayId): ?>
        <div class="data-table tree-wrapper">
          <table>
              <thead>
                  <tr>
                      <th>Empleado</th>
                      <?php foreach ($categories as $category): ?>
                          <th colspan="2"><?= htmlspecialchars($category) ?></th>
                      <?php endforeach; ?>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($employeesMap as $employeeId => $employeeName): ?>
                      <tr data-employee-id="<?= htmlspecialchars($employeeId) ?>">
                          <td><?= htmlspecialchars($employeeName) ?></td>
                          <?php foreach ($categories as $category): 
                              $eval = $evaluations[$employeeId][$category] ?? [];
                              $item = $eval['item'] ?? "PERFECTO";
                              $checked = $eval['checked'] ?? ($item === "PERFECTO");
                              $evaluationId = $eval['evaluation_id'] ?? null;
                              $options = $categoryItems[$category] ?? [];
                              if ($item && !in_array($item, $options)) array_unshift($options, $item);
                          ?>
                             <td class="checkbox-cell <?= $checked ? 'checked-green' : 'checked-red' ?>">
                                <label>
                                    <input disabled type="checkbox" <?= $checked ? 'checked' : '' ?>>
                                    <span class="feather-icon" data-feather="<?= $checked ? 'check-square' : 'square' ?>"></span>
                                </label>
                            </td>

                              <td class="select-cell" data-category="<?= htmlspecialchars($category) ?>" data-evaluation-id="<?= htmlspecialchars($evaluationId) ?>">
                                  <select>
                                      <?php foreach ($options as $subItem): ?>
                                          <option value="<?= htmlspecialchars($subItem) ?>" <?= $subItem === $item ? 'selected' : '' ?>>
                                              <?= htmlspecialchars($subItem) ?>
                                          </option>
                                      <?php endforeach; ?>
                                  </select>
                              </td>
                          <?php endforeach; ?>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
        </div>
        <div class="card-column-two">
          <div class="column-one" data-section="employees">
            <h3>Progreso por Empleado</h3>
            <div class="progress-grid">
              <?php foreach ($employeesMap as $employeeId => $employeeName): ?>
                <div class="progress-card">
                  <div class="card-header">
                    <h4><?= htmlspecialchars($employeeName) ?></h4>
                  </div>
                  <div class="progress-bar-container">
                    <div class="employee-progress-bar" data-employee-id="<?= htmlspecialchars($employeeId) ?>">0%</div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="column-two" data-section="categories">
            <h3>Progreso por Categoría</h3>
            <div class="progress-grid">
              <?php foreach ($categories as $category): ?>
                <div class="progress-card">
                  <div class="card-header">
                    <h4><?= htmlspecialchars($category) ?></h4>
                  </div>
                  <div class="progress-bar-container">
                    <div class="progress-bar" data-category="<?= htmlspecialchars($category) ?>">0%</div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
      </div>
    </div>
  </main>

  <script>
class EvaluationApp {
    constructor(categories, dayId) {
        this.categories = categories;
        this.dayId = dayId;
        this.init();
    }

    init() {
        // Actualizar todos los promedios al inicio
        document.querySelectorAll("tbody tr").forEach(row => {
            this.updateEmployeeAverage(row);
        });
        this.updateCategoryProgress();
        // Ordenar listas inicialmente
        this.sortCategoryProgress();
        this.sortEmployeeProgress();
        this.bindEvents();
    }

    updateEmployeeAverage(row) {
        const total = Array.from(row.querySelectorAll("td[data-category] select"))
            .reduce((sum, select) => sum + (select.value === "PERFECTO" ? 100 : 0), 0);
        const percent = Math.round(total / this.categories.length);
        const employeeId = row.dataset.employeeId;
        
        const progressBar = document.querySelector(`.employee-progress-bar[data-employee-id="${employeeId}"]`);
        if (progressBar) {
            progressBar.style.width = percent + "%";
            progressBar.textContent = percent + "%";
        }
        // Reordenar lista de empleados tras actualizar
        this.sortEmployeeProgress();
    }

    updateCategoryProgress() {
        const rows = document.querySelectorAll("tbody tr");
        if (rows.length === 0) return;
        
        this.categories.forEach(category => {
            let perfectCount = 0;
            rows.forEach(r => {
                const select = r.querySelector(`td[data-category="${category}"] select`);
                if (select && select.value === "PERFECTO") {
                    perfectCount++;
                }
            });
            const percent = Math.round(perfectCount / rows.length * 100);
            
            const progressBar = document.querySelector(`.progress-bar[data-category="${category}"]`);
            if (progressBar) {
                progressBar.style.width = percent + "%";
                progressBar.textContent = percent + "%";
            }
        });
        // Reordenar lista de categorías tras actualizar
        this.sortCategoryProgress();
    }

    async saveEvaluation(evaluationId, item) {
        try {
            const res = await fetch("api.php?action=update_evaluation", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ evaluation_id: evaluationId, day_id: this.dayId, item })
            });
            const data = await res.json();
            
            if (!data.success) alert("Error al guardar: " + (data.error || "desconocido"));
            return data;
        } catch (error) {
            console.error(error);
            alert("Error de red al guardar");
            }
    }

    bindEvents() {
    document.querySelectorAll("td[data-category] select").forEach(select => {
        select.addEventListener("change", async () => {
            const td = select.closest("td");
            const checkbox = td.previousElementSibling.querySelector("input[type=checkbox]");
            const icon = checkbox.nextElementSibling;
            const row = td.closest("tr");
            const value = select.value;
            const evaluationId = td.dataset.evaluationId;

            try {
                const res = await this.saveEvaluation(evaluationId, value);
                
                if (res.success || res.success === true ) {
                    // Solo marcar el checkbox si la petición fue exitosa
                    checkbox.checked = value === "PERFECTO";

                    // Cambiar icono Feather
                    icon.setAttribute("data-feather", checkbox.checked ? "check-square" : "square");

                    // Cambiar clase de la celda
                    const checkboxCell = checkbox.closest('td');
                    if (checkbox.checked) {
                        checkboxCell.classList.add('checked-green');
                        checkboxCell.classList.remove('checked-red');
                    } else {
                        checkboxCell.classList.add('checked-red');
                        checkboxCell.classList.remove('checked-green');
                    }

                    feather.replace();

                    this.updateEmployeeAverage(row);
                    this.updateCategoryProgress();
                    
                    // mostrar mensaje de exito arriba a la derecha ventana pequeña 
                    if (res.success || res.success === true ) {
                        Swal.fire({
                            title: "Guardado exitosamente!",
                            icon: "success",
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }

                } else {
                    Swal.fire({
                        title: "Error al guardar!",
                        icon: "error",
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    select.value = checkbox.checked ? "PERFECTO" : select.value; 
                }
            } catch (err) {
                console.error(err);
                alert("Error de red al guardar");
                select.value = checkbox.checked ? "PERFECTO" : select.value; 
            }
        });
    });
}


}

// Helpers de ordenamiento: de mayor a menor
EvaluationApp.prototype.sortCategoryProgress = function() {
    const grid = document.querySelector('.column-two[data-section="categories"] .progress-grid');
    if (!grid) return;
    const cards = Array.from(grid.children);
    cards.sort((a, b) => {
        const pa = parseInt(a.querySelector('.progress-bar')?.textContent || '0');
        const pb = parseInt(b.querySelector('.progress-bar')?.textContent || '0');
        return pb - pa; // desc
    });
    cards.forEach(c => grid.appendChild(c));
};

EvaluationApp.prototype.sortEmployeeProgress = function() {
    const grid = document.querySelector('.column-one[data-section="employees"] .progress-grid');
    if (!grid) return;
    const cards = Array.from(grid.children);
    cards.sort((a, b) => {
        const pa = parseInt(a.querySelector('.employee-progress-bar')?.textContent || '0');
        const pb = parseInt(b.querySelector('.employee-progress-bar')?.textContent || '0');
        return pb - pa; // desc
    });
    cards.forEach(c => grid.appendChild(c));
};

// Añadir insignias a los primeros 3 de cada grid
function applyBadgesToGrid(grid, type) {
    if (!grid) return;
    const cards = Array.from(grid.children);
    const badges = [
        { cls: 'badge-gold', icon: 'award', label: '1º' },
        { cls: 'badge-diamond', icon: 'zap', label: '2º' },
        { cls: 'badge-silver', icon: 'star', label: '3º' }
    ];
    cards.forEach((card, idx) => {
        const header = card.querySelector('.card-header');
        if (!header) return;
        // limpiar previo
        header.querySelectorAll('.badge').forEach(b => b.remove());
        const b = document.createElement('span');
        if (idx < 3) {
            b.className = `badge ${badges[idx].cls}`;
            b.innerHTML = `<i data-feather="${badges[idx].icon}"></i> ${badges[idx].label}`;
        } else {
            b.className = 'badge badge-bronze';
            b.innerHTML = `<i data-feather="award"></i>`;
        }
        header.appendChild(b);
    });
    if (typeof feather !== 'undefined') feather.replace();
}

// Hookear a las funciones existentes para añadir insignias después de ordenar
const _origSortCat = EvaluationApp.prototype.sortCategoryProgress;
EvaluationApp.prototype.sortCategoryProgress = function() {
    _origSortCat.call(this);
    const grid = document.querySelector('.column-two[data-section="categories"] .progress-grid');
    applyBadgesToGrid(grid, 'categories');
};

const _origSortEmp = EvaluationApp.prototype.sortEmployeeProgress;
EvaluationApp.prototype.sortEmployeeProgress = function() {
    _origSortEmp.call(this);
    const grid = document.querySelector('.column-one[data-section="employees"] .progress-grid');
    applyBadgesToGrid(grid, 'employees');
};

<?php if ($dayId): ?>
new EvaluationApp(<?= json_encode($categories) ?>, <?= json_encode($dayId) ?>);
<?php endif; ?>
</script>

<?php
require_once 'template/footer.php';
?>