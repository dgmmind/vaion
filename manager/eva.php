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
require_once "../repository/supabase.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

$dayId = $_GET['day_id'] ?? null;

$supabase = new Supabase();

// Buscar el manager
$manager = null;
foreach ($users as $key => $user) {
    if ($key === $managerId) {
        $manager = $user;
        break;
    }
}

if (!$manager) die("Manager no encontrado en usuarios");

// Traer días del manager
$days = array_filter(
    $supabase->select("days", "day_id, day_date, manager_id")["data"] ?? [],
    fn($d) => $d["manager_id"] === $managerId
);

// Validar día seleccionado
if ($dayId && !array_filter($days, fn($d) => $d['day_id'] == $dayId)) die("El día seleccionado no existe para este manager.");

// Mapa de empleados
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

$categoryItems = $category_item ?? [];
$categories = array_keys($categoryItems);
?>

<style>
.evaluations-container { margin:20px 0; }
.day-selector { margin-bottom:20px; }
.day-selector select { padding:8px; border:1px solid #d1d5db; border-radius:4px; min-width:200px; }
.progress-section { margin-top:30px; }
.progress-section h3 { margin-bottom:15px; }
.progress-grid { display:flex; gap:20px; flex-wrap:wrap; margin-top:15px; }
.progress-card { flex:1; min-width:200px; padding:15px; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; }
.progress-card h4 { margin:0 0 10px 0; color:#374151; }
.progress-bar-container { width:100%; height:12px; background:#e2e8f0; border-radius:6px; overflow:hidden; }
.progress-bar, .employee-progress-bar { height:100%; width:0%; transition:width .3s; color:#fff; font-size:10px; text-align:center; line-height:12px; }
.progress-bar { background:#3b82f6; }
.employee-progress-bar { background:#10b981; }
.data-table { overflow-x:auto; border-radius:8px; box-shadow:#DCE3F1 0 0 0 1px, #DCE3F1 0 1px 1px -.5px, rgba(42,51,70,.04) 0 2px 2px -1px, rgba(42,51,70,.03) 0 5px 5px -2.5px, rgba(42,51,70,.03) 0 10px 10px -5px, rgba(42,51,70,.03) 0 24px 24px -8px; }
.data-table table { border-collapse:separate; border-spacing:0; width:100%; text-align:center; font-size:14px; }
.data-table th, .data-table td { white-space:nowrap; padding:0 16px; color:#232731; font-size:12px; }
.data-table th { background:#c1cce72e; border-bottom:1px solid #c1cce7; font-weight:bold; height:40px; }
.data-table td { border-bottom:1px solid #c1cce7; border-right:1px solid #c1cce7; height:36px; position:relative; overflow:visible; }

/* Custom Select mejorado */
.custom-select { position:relative; min-width:120px; font-size:12px; cursor:pointer; display:inline-block; }
.select-selected {
  border:1px solid #c1cce7; border-radius:4px; padding:4px 28px 4px 8px;
  background:#fff; user-select:none; position:relative;
}
.select-selected i {
  position:absolute; right:8px; top:50%; transform:translateY(-50%);
  pointer-events:none; font-size:14px; color:#374151;
}
.select-items {
  position:absolute; top:100%; left:0; border:1px solid #c1cce7;
  border-radius:4px; background:#fff; display:none; z-index:1000;
  white-space:nowrap; width:auto; min-width:100%;
}
.select-items div { padding:6px 8px; cursor:pointer; }
.select-items div:hover { background:#f1f5f9; }
</style>

<main class="main-content">
  <div class="container card">
    <div class="header-card">
      <h1 class="title">Evaluaciones</h1>
      <p class="description">Gestiona las evaluaciones de tus empleados</p>
    </div>
    
    <div class="evaluations-container">
      <h3>Días del Manager</h3>
      <form method="GET" class="day-selector">
          <select name="day_id" onchange="this.form.submit()">
              <option value="">Selecciona un día</option>
              <?php foreach ($days as $day): ?>
                  <option value="<?= htmlspecialchars($day['day_id']) ?>" <?= $dayId == $day['day_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($day['day_date']) ?>
                  </option>
              <?php endforeach; ?>
          </select>
      </form>

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
                            <td class="checkbox-cell">
                              <input type="checkbox" disabled <?= $checked ? 'checked' : '' ?>>
                            </td>
                            <td class="select-cell" data-category="<?= htmlspecialchars($category) ?>" data-evaluation-id="<?= htmlspecialchars($evaluationId) ?>">
                                <div class="custom-select">
                                  <div class="select-selected">
                                    <?= htmlspecialchars($item) ?>
                                    <i data-feather="chevron-down"></i>
                                  </div>
                                  <div class="select-items">
                                    <?php foreach ($options as $subItem): ?>
                                      <div data-value="<?= htmlspecialchars($subItem) ?>"><?= htmlspecialchars($subItem) ?></div>
                                    <?php endforeach; ?>
                                  </div>
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
      </div>

      <div class="progress-section">
        <h3>Progreso por Categoría</h3>
        <div class="progress-grid">
          <?php foreach ($categories as $category): ?>
            <div class="progress-card">
              <h4><?= htmlspecialchars($category) ?></h4>
              <div class="progress-bar-container">
                <div class="progress-bar" data-category="<?= htmlspecialchars($category) ?>">0%</div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="progress-section">
        <h3>Progreso por Empleado</h3>
        <div class="progress-grid">
          <?php foreach ($employeesMap as $employeeId => $employeeName): ?>
            <div class="progress-card">
              <h4><?= htmlspecialchars($employeeName) ?></h4>
              <div class="progress-bar-container">
                <div class="employee-progress-bar" data-employee-id="<?= htmlspecialchars($employeeId) ?>">0%</div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script src="https://unpkg.com/feather-icons"></script>
<script>
class EvaluationApp {
    constructor(categories, dayId) {
        this.categories = categories;
        this.dayId = dayId;
        this.init();
    }

    init() {
        document.querySelectorAll("tbody tr").forEach(row => this.updateEmployeeAverage(row));
        this.updateCategoryProgress();
        // Ordenar listas inicialmente
        this.sortCategoryProgress();
        this.sortEmployeeProgress();
        this.bindEvents();
        feather.replace();
    }

    updateEmployeeAverage(row) {
        const total = Array.from(row.querySelectorAll("td[data-category] .select-selected"))
            .reduce((sum, el) => sum + (el.textContent.trim().replace(/▼|▲/g,'') === "PERFECTO" ? 100 : 0), 0);
        const percent = Math.round(total / this.categories.length);
        const employeeId = row.dataset.employeeId;
        const progressBar = document.querySelector(`.employee-progress-bar[data-employee-id="${employeeId}"]`);
        if (progressBar) { progressBar.style.width = percent + "%"; progressBar.textContent = percent + "%"; }
        // Reordenar lista de empleados tras actualizar
        this.sortEmployeeProgress();
    }

    updateCategoryProgress() {
        const rows = document.querySelectorAll("tbody tr");
        if (!rows.length) return;
        this.categories.forEach(category => {
            let perfectCount = 0;
            rows.forEach(r => {
                const sel = r.querySelector(`td[data-category="${category}"] .select-selected`);
                if (sel && sel.textContent.trim().includes("PERFECTO")) perfectCount++;
            });
            const percent = Math.round(perfectCount / rows.length * 100);
            const bar = document.querySelector(`.progress-bar[data-category="${category}"]`);
            if (bar) { bar.style.width = percent + "%"; bar.textContent = percent + "%"; }
        });
        // Reordenar lista de categorías tras actualizar
        this.sortCategoryProgress();
    }

    async saveEvaluation(evaluationId, item) {
        try {
            const res = await fetch("api.php?action=update_evaluation", {
                method:"POST", headers:{ "Content-Type":"application/json" },
                body: JSON.stringify({ evaluation_id:evaluationId, day_id:this.dayId, item })
            });
            const data = await res.json();
            if (!data.success) alert("Error al guardar: " + (data.error || "desconocido"));
        } catch (err) { console.error(err); alert("Error de red al guardar"); }
    }

    bindEvents() {
        document.querySelectorAll("td[data-category] .custom-select").forEach(cs => {
            const selected = cs.querySelector(".select-selected");
            const icon = selected.querySelector("i");
            const items = cs.querySelector(".select-items");

            selected.onclick = (e) => { 
                e.stopPropagation();
                const isOpen = items.style.display === "block";
                document.querySelectorAll(".select-items").forEach(si => si.style.display = "none");
                document.querySelectorAll(".select-selected i").forEach(ic => ic.setAttribute("data-feather", "chevron-down"));

                if (!isOpen) {
                    items.style.display = "block";
                    icon.setAttribute("data-feather", "chevron-up");
                } else {
                    items.style.display = "none";
                    icon.setAttribute("data-feather", "chevron-down");
                }
                feather.replace();
            };

            items.querySelectorAll("div").forEach(div => {
                div.onclick = async () => {
                    selected.childNodes[0].nodeValue = div.textContent + " ";
                    items.style.display = "none";
                    icon.setAttribute("data-feather", "chevron-down");
                    feather.replace();

                    const td = cs.closest("td");
                    const checkbox = td.previousElementSibling.querySelector("input[type=checkbox]");
                    checkbox.checked = div.dataset.value === "PERFECTO";

                    const row = td.closest("tr");
                    this.updateEmployeeAverage(row);
                    this.updateCategoryProgress();
                    await this.saveEvaluation(td.dataset.evaluationId, div.dataset.value);
                };
            });

            document.addEventListener("click", () => {
                items.style.display="none";
                icon.setAttribute("data-feather", "chevron-down");
                feather.replace();
            });
        });
    }
}

// Helpers de ordenamiento: de mayor a menor
EvaluationApp.prototype.sortCategoryProgress = function() {
    const grid = document.querySelectorAll('.progress-section .progress-grid')[0];
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
    const sections = document.querySelectorAll('.progress-section');
    if (sections.length < 2) return;
    const grid = sections[1].querySelector('.progress-grid');
    if (!grid) return;
    const cards = Array.from(grid.children);
    cards.sort((a, b) => {
        const pa = parseInt(a.querySelector('.employee-progress-bar')?.textContent || '0');
        const pb = parseInt(b.querySelector('.employee-progress-bar')?.textContent || '0');
        return pb - pa; // desc
    });
    cards.forEach(c => grid.appendChild(c));
};

<?php if ($dayId): ?>
new EvaluationApp(<?= json_encode($categories) ?>, <?= json_encode($dayId) ?>);
<?php endif; ?>
</script>

<?php require_once 'template/footer.php'; ?>
