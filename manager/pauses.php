<?php
// Configurar zona horaria para Honduras
date_default_timezone_set('America/Tegucigalpa');

require_once 'template/header.php';
require_once "../repository/supabase.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$supabase = new Supabase();

// Incluir el archivo de usuarios
require_once "../data/users.php";

// Obtener los empleados del manager logueado
$allEmployees = [];
$currentManagerId = $_SESSION['user_id'];

// Buscar el manager actual en el array de usuarios
foreach ($users as $userId => $user) {
    if ($user['id'] === $currentManagerId && $user['role'] === 'admin' && isset($user['employees'])) {
        foreach ($user['employees'] as $employee) {
            $employee['manager_name'] = $user['name'];
            $employee['manager_id'] = $user['id'];
            $employee['department'] = $user['DEPARTMENT'] ?? 'N/A';
            $allEmployees[] = $employee;
        }
        break; // Salir del bucle una vez que encontramos al manager actual
    }
}

// Obtener fechas del formulario o usar la fecha actual
$startDate = $_GET['startDate'] ?? date('Y-m-d');
$endDate = $_GET['endDate'] ?? date('Y-m-d');

// Validar fechas
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) $startDate = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) $endDate = date('Y-m-d');

// Obtener todas las pausas del rango de fechas de todos los empleados
$allPausesResponse = $supabase->select('pauses', '*', [
    'conditions' => 'employee_id.in.(' . implode(',', array_map(function($user) { return $user['id']; }, $allEmployees)) . '),start_time.gte.' . $startDate . 'T00:00:00,start_time.lte.' . $endDate . 'T23:59:59',
    'order' => 'start_time.desc'
]);

$allPauses = [];
if ($allPausesResponse['status'] === 200) {
    $allPauses = $allPausesResponse['data'];
}

// Obtener solo las pausas activas (sin importar la fecha para ver pausas activas)
$pausesResponse = $supabase->select('pauses', '*', [
    'conditions' => 'end_time.is.null'
]);

$activePauses = [];
if ($pausesResponse['status'] === 200 && isset($pausesResponse['data'])) {
    $activePauses = $pausesResponse['data'];
}

// Crear un mapa de usuarios con pausas activas
$usersWithActivePause = [];
foreach ($activePauses as $pause) {
    $usersWithActivePause[$pause['employee_id']] = $pause;
}

// Mostrar todos los empleados en la sección de trabajando para reportes
$usersWorking = $allEmployees;
$usersOnPause = [];

// Opcional: Si aún necesitas la lista de usuarios en pausa para otra funcionalidad
foreach ($allEmployees as $user) {
    if (isset($usersWithActivePause[$user['id']])) {
        $usersOnPause[] = $user;
    }
}
?>

<!-- Main Content -->
<main class="main-content">
  <div class="container">
    <div class="card">
      <!-- Header Card -->
      <div class="header-card">
        <div class="title">Empleados Trabajando - <?php echo htmlspecialchars($users[$currentManagerId]['name'] ?? 'Manager'); ?></div>
        <div class="description">Estado actual de tus empleados</div>
        
        <!-- Filtro de fechas -->
        <div class="date-filter" style="margin-top: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <div>
            <label for="startDate" style="display: block; margin-bottom: 5px; font-weight: 500;">Fecha Inicio:</label>
            <input type="date" id="startDate" name="startDate" value="<?php echo htmlspecialchars($startDate); ?>" class="form-control" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
          </div>
          <div>
            <label for="endDate" style="display: block; margin-bottom: 5px; font-weight: 500;">Fecha Fin:</label>
            <input type="date" id="endDate" name="endDate" value="<?php echo htmlspecialchars($endDate); ?>" class="form-control" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
          </div>
          <div style="display: flex; align-items: flex-end; gap: 5px;">
            <button id="filterButton" class="btn btn-primary" style="padding: 5px 15px; height: 38px; margin-top: 18px;">
              <i data-feather="filter"></i> Filtrar
            </button>
            <button id="resetFilter" class="btn btn-secondary" style="padding: 5px 15px; height: 38px; margin-top: 18px;">
              <i data-feather="refresh-cw"></i> Hoy
            </button>
          </div>
        </div>
      </div>
      
      <!-- Body Card -->
      <div class="body-card">
        <div class="table-container">
          <!-- Sección de Empleados Trabajando -->
          <div class="full-width-section">
            <h3>Empleados Trabajando (<?php echo count($usersWorking); ?>)</h3>
            <?php if (empty($usersWorking)): ?>
              <div class="description">No hay empleados trabajando actualmente</div>
            <?php else: ?>
              <div class="data-table">
                <table>
                  <thead>
                    <tr>
                      <th>EMPLEADO</th>
                      <th>DEPARTAMENTO</th>
                      <th>ESTADO</th>
                      <th>TOTAL PAUSAS</th>
                      <th>TOTAL TIEMPO</th>
                      <th>ACCIONES</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($usersWorking as $user): ?>
                      <tr>
                        <td class="text-left font-weight-bold">
                          <div class="user-avatar">
                            <i data-feather="user"></i>
                          </div>
                          <?php echo htmlspecialchars($user['name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['department']); ?></td>
                        <td>
                          <span class="status-working">Trabajando</span>
                        </td>
                        <td class="total-column">
                          <?php 
                            // Contar TODAS las pausas de este usuario (activas y completadas)
                            $userPauses = 0;
                            foreach ($allPauses as $p) {
                              if ($p['employee_id'] == $user['id']) {
                                $userPauses++;
                              }
                            }
                            echo $userPauses;
                          ?>
                        </td>
                        <td class="total-column">
                          <?php 
                            // Calcular el tiempo total en pausa para este usuario
                            $totalSeconds = 0;
                            foreach ($allPauses as $p) {
                              if ($p['employee_id'] == $user['id'] && $p['end_time']) {
                                $start = strtotime($p['start_time']);
                                $end = strtotime($p['end_time']);
                                $totalSeconds += ($end - $start);
                              }
                            }
                            
                            // Convertir segundos a horas, minutos y segundos
                            $hours = floor($totalSeconds / 3600);
                            $minutes = floor(($totalSeconds % 3600) / 60);
                            $seconds = $totalSeconds % 60;
                            
                            // Formatear el tiempo
                            $timeString = '';
                            if ($hours > 0) {
                                $timeString .= $hours . 'h ';
                            }
                            if ($minutes > 0 || $hours > 0) {
                                $timeString .= $minutes . 'm ';
                            }
                            $timeString .= $seconds . 's';
                            
                            echo $timeString;
                          ?>
                        </td>
                        <td>
                          <button class="button action-btn" title="Ver detalles" onclick="showUserModal('<?php echo htmlspecialchars($user['id']); ?>', '<?php echo htmlspecialchars($user['name']); ?>', '<?php echo htmlspecialchars($user['username']); ?>')">
                            <i data-feather="eye"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<style>
.status-working {
  display: inline-block;
  padding: 4px 8px;
  background-color: #e6f7e6;
  color: #2e7d32;
  border-radius: 4px;
  font-size: 0.9em;
  font-weight: 500;
}

.user-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: #f0f0f0;
  margin-right: 8px;
  vertical-align: middle;
}

.user-avatar i {
  width: 18px;
  height: 18px;
  color: #666;
}

.text-left {
  text-align: left;
}

.font-weight-bold {
  font-weight: 600;
}

.total-column {
  text-align: center;
  font-weight: 500;
}

.action-btn {
  background: none;
  border: none;
  color: #5c6bc0;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.action-btn:hover {
  background-color: rgba(92, 107, 192, 0.1);
}

.action-btn i {
  width: 18px;
  height: 18px;
}

/* Estilos para el modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 24px;
  border-radius: 8px;
  max-width: 800px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #eee;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0;
  line-height: 1;
}

.close-btn:hover {
  color: #333;
}

/* Estilos para la tabla de pausas */
.pauses-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 16px;
}

.pauses-table th,
.pauses-table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.pauses-table th {
  font-weight: 600;
  color: #555;
  background-color: #f9f9f9;
}

.pauses-table tr:hover {
  background-color: #f5f5f5;
}

.pause-reason {
  color: #333;
  font-weight: 500;
}

.pause-duration {
  color: #666;
  font-family: monospace;
}

/* Estilos para los botones de acción */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  font-size: 0.9rem;
}

.btn-primary {
  background-color: #5c6bc0;
  color: white;
}

.btn-primary:hover {
  background-color: #3f51b5;
}

.btn-outline {
  background: none;
  border: 1px solid #ddd;
  color: #555;
}

.btn-outline:hover {
  background-color: #f5f5f5;
}

.btn + .btn {
  margin-left: 8px;
}

/* Estilos para el contador de tiempo */
.timer {
  font-family: monospace;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1976d2;
}

/* Estilos para la tarjeta de resumen */
.summary-card {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 20px;
  border-left: 4px solid #5c6bc0;
}

.summary-title {
  font-size: 1rem;
  font-weight: 600;
  color: #555;
  margin: 0 0 8px 0;
}

.summary-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
  margin: 0;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

/* Estilos responsivos */
@media (max-width: 768px) {
  .modal-content {
    width: 95%;
    padding: 16px;
  }
  
  .pauses-table th,
  .pauses-table td {
    padding: 8px 12px;
  }
  
  .summary-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<script src="https://unpkg.com/feather-icons"></script>
<script>
// Inicializar Feather Icons
feather.replace();

// Manejar el filtrado por fechas
document.getElementById('filterButton').addEventListener('click', function() {
  const startDate = document.getElementById('startDate').value;
  const endDate = document.getElementById('endDate').value;
  
  if (startDate && endDate) {
    window.location.href = `?startDate=${startDate}&endDate=${endDate}`;
  }
});

// Resetear filtro a la fecha actual
document.getElementById('resetFilter').addEventListener('click', function() {
  const today = new Date().toISOString().split('T')[0];
  window.location.href = `?startDate=${today}&endDate=${today}`;
});

// Función para mostrar el modal del usuario
function showUserModal(userId, userName, userUsername) {
  // Usar la API del superadmin
  fetch('../superadmin/api.php?action=get_user_pauses&user_id=' + userId)
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const pauses = data.data;
        let modalContent = `
          <div class="modal-header">
            <h3 class="modal-title">Detalles de ${userName}</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
          </div>
          <div class="modal-body">
            <div class="user-info">
              <h4>${userName}</h4>
              <p>Usuario: ${userUsername}</p>
            </div>
            
            <div class="summary-grid">
              <div class="summary-card">
                <p class="summary-title">Total de Pausas</p>
                <p class="summary-value">${pauses.length}</p>
              </div>
              
              <div class="summary-card">
                <p class="summary-title">Tiempo Total en Pausa</p>
                <p class="summary-value" id="totalPauseTime">Calculando...</p>
              </div>
            </div>
            
            <h4>Historial de Pausas</h4>
            <div class="table-responsive">
              <table class="pauses-table">
                <thead>
                  <tr>
                    <th>Fecha/Hora Inicio</th>
                    <th>Fecha/Hora Fin</th>
                    <th>Duración</th>
                    <th>Razón</th>
                  </tr>
                </thead>
                <tbody>
        `;
        
        // Calcular tiempo total y llenar la tabla
        let totalPauseTime = 0;
        
        if (pauses.length > 0) {
          pauses.forEach(pause => {
            const startTime = new Date(pause.start_time);
            const endTime = pause.end_time ? new Date(pause.end_time) : null;
            
            let duration = 0;
            let durationText = 'En progreso';
            
            if (endTime) {
              duration = Math.round((endTime - startTime) / 1000); // en segundos
              const hours = Math.floor(duration / 3600);
              const minutes = Math.floor((duration % 3600) / 60);
              const seconds = duration % 60;
              
              durationText = '';
              if (hours > 0) durationText += hours + 'h ';
              if (minutes > 0 || hours > 0) durationText += minutes + 'm ';
              durationText += seconds + 's';
              
              totalPauseTime += duration;
            }
            
            modalContent += `
              <tr>
                <td>${startTime.toLocaleString()}</td>
                <td>${endTime ? endTime.toLocaleString() : 'En progreso'}</td>
                <td class="pause-duration">${durationText}</td>
                <td class="pause-reason">${pause.reason || 'Sin razón especificada'}</td>
              </tr>
            `;
          });
          
          // Calcular el tiempo total formateado
          const totalHours = Math.floor(totalPauseTime / 3600);
          const totalMinutes = Math.floor((totalPauseTime % 3600) / 60);
          const totalSeconds = totalPauseTime % 60;
          
          let totalTimeString = '';
          if (totalHours > 0) totalTimeString += totalHours + 'h ';
          if (totalMinutes > 0 || totalHours > 0) totalTimeString += totalMinutes + 'm ';
          totalTimeString += totalSeconds + 's';
          
          // Actualizar el tiempo total
          document.getElementById('totalPauseTime').textContent = totalTimeString;
          
        } else {
          modalContent += `
            <tr>
              <td colspan="4" style="text-align: center;">No hay registros de pausas</td>
            </tr>
          `;
          document.getElementById('totalPauseTime').textContent = '0s';
        }
        
        modalContent += `
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Cerrar</button>
          </div>
        `;
        
        document.getElementById('userModalContent').innerHTML = modalContent;
        document.getElementById('userModal').style.display = 'flex';
        
        // Actualizar Feather Icons en el modal
        feather.replace();
      } else {
        alert('Error al cargar los datos del usuario');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error al cargar los datos del usuario');
    });
}

// Función para cerrar el modal
function closeModal() {
  document.getElementById('userModal').style.display = 'none';
}

// Cerrar el modal al hacer clic fuera del contenido
window.onclick = function(event) {
  const modal = document.getElementById('userModal');
  if (event.target === modal) {
    closeModal();
  }
}
</script>

<!-- Modal para mostrar detalles del usuario -->
<div id="userModal" class="modal-overlay" style="display: none;">
  <div class="modal-content" style="max-width: 600px;">
    <div id="userModalContent">
      <!-- El contenido se cargará dinámicamente -->
    </div>
  </div>
</div>

<?php require_once 'template/footer.php'; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<style>
.status-working {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #dcfce7;
  color: #166534;
}

.action-btn {
  width: 32px;
  height: 32px;
  background: #f3f4f6;
  border: none;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.2s;
}

.action-btn:hover {
  background: #e5e7eb;
}

.action-btn i {
  width: 16px;
  height: 16px;
  color: #6b7280;
}

.description {
  color: #6b7280;
  font-style: italic;
  text-align: center;
  padding: 20px;
}

/* Estilos para el modal de usuario */
.pauses-list {
  margin-top: 15px;
}

.pauses-list h4 {
  margin-bottom: 15px;
  color: #1f2937;
  font-size: 16px;
  font-weight: 600;
}

.pauses-list .data-table {
  margin-top: 10px;
}

.pauses-list .data-table th,
.pauses-list .data-table td {
  padding: 8px 12px;
  font-size: 12px;
}

.pauses-list .data-table th {
  background-color: #f3f4f6;
  font-weight: 600;
}

/* Modal más ancho para la tabla */
#userModal .modal-content {
  max-width: 700px;
  width: 95%;
}

#userModal .modal-content h3 {
  color: #1f2937;
  margin-bottom: 10px;
}

#userModal .modal-content p {
  margin-bottom: 15px;
  color: #6b7280;
}

#userModal .modal-content strong {
  color: #374151;
}

/* Estilos para el resumen de totales */
.totals-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin: 20px 0;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.total-item {
  text-align: center;
  padding: 10px;
  background: white;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.total-item strong {
  display: block;
  margin-bottom: 5px;
  color: #374151;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.total-item:last-child {
  color: #dc2626;
  font-weight: 600;
}

/* Estilos para las razones de pausas activas */
.active-reason {
  color: #ea580c !important;
  font-weight: 600;
}

.active-duration {
  color: #dc2626;
  font-weight: 600;
}

.active-status {
  color: #dc2626;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
}

.status-active {
  background: #fef3c7;
  color: #92400e;
}

.status-completed {
  background: #d1fae5;
  color: #065f46;
}

/* Estilos para las columnas de totales */
.total-column {
  background: #f8f9fa;
  font-weight: 600;
  color: #374151;
  text-align: center;
}

.total-column:last-child {
  color: #059669;
}

/* Estilo para "En progreso" */
.in-progress {
  color: #dc2626;
  font-weight: 600;
  font-style: italic;
}

/* Estilos para avatares de usuario */
.user-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #e5e7eb;
  border-radius: 50%;
  margin-right: 10px;
  color: #6b7280;
}

.user-avatar i {
  width: 18px;
  height: 18px;
}

/* Estilos para la tabla de usuarios sin pausas */
.no-pauses-title {
  color: #dc2626;
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 10px;
}

.no-pauses-description {
  color: #dc2626;
  font-style: italic;
  margin-bottom: 15px;
}

.no-pauses-row {
  background: #fef2f2 !important;
}

.no-pauses-row:hover {
  background: #fee2e2 !important;
}

.status-warning {
  background: #fef2f2;
  color: #dc2626;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar Feather Icons
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
});

// Actualizar contadores de tiempo en tiempo real
function updateTimers() {
  const now = Math.floor(Date.now() / 1000); // Tiempo actual en segundos
  
  document.querySelectorAll('.in-progress[data-start-time]').forEach(element => {
    const startTime = parseInt(element.getAttribute('data-start-time'));
    const elapsedSeconds = now - startTime;
    
    // Convertir a horas, minutos y segundos
    const hours = Math.floor(elapsedSeconds / 3600);
    const minutes = Math.floor((elapsedSeconds % 3600) / 60);
    const seconds = elapsedSeconds % 60;
    
    // Formatear el tiempo
    let timeString = '';
    if (hours > 0) timeString += `${hours}h `;
    if (minutes > 0 || hours > 0) timeString += `${minutes}m `;
    timeString += `${seconds}s`;
    
    element.textContent = timeString;
  });
}

// Función para actualizar los datos de la página
async function updatePageData() {
  try {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Actualizar los contadores
    updateTimers();
    
    // Aquí puedes agregar más lógica para actualizar otros datos si es necesario
  } catch (error) {
    console.error('Error al actualizar datos:', error);
  }
}

// Actualizar contadores cada 5 segundos
setInterval(updatePageData, 5000);

// Actualizar inmediatamente al cargar
updatePageData();

// Función para mostrar el modal del usuario
function showUserModal(userId, userName, userUsername) {
  // Obtener las fechas del filtro actual
  const startDate = document.getElementById('startDate').value;
  const endDate = document.getElementById('endDate').value;
  
  // Usar la API del manager con los parámetros de fecha
  fetch(`api.php?action=getUserPauses&userId=${userId}&startDate=${startDate}&endDate=${endDate}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const allPauses = data.pauses || [];
        const activePauses = allPauses.filter(pause => !pause.end_time);
        displayUserModal(userName, userUsername, allPauses, activePauses);
      } else {
        console.error('Error al obtener datos:', data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
}

// Función para mostrar el modal con los datos
function displayUserModal(userName, userUsername, pauses, activePauses) {
  const modal = document.getElementById('userModal');
  const modalContent = document.getElementById('userModalContent');
  
  // Crear el contenido del modal
  let content = `
    <h3><i data-feather="user"></i> ${userName}</h3>
    <p><strong><i data-feather="at-sign"></i> Username:</strong> ${userUsername}</p>
  `;
  
  // Función para formatear segundos a Xh Ym Zs
  function formatDuration(seconds) {
    if (seconds === 0) return '0s';
    
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    let result = '';
    if (hours > 0) result += `${hours}h `;
    if (minutes > 0 || hours > 0) result += `${minutes}m `;
    result += `${secs}s`;
    
    return result.trim();
  }
  
  // Calcular totales
  const totalPauses = pauses ? pauses.length : 0;
  const totalTimeInPauseSeconds = pauses ? pauses.reduce((total, pause) => {
    if (pause.end_time) {
      const start = new Date(pause.start_time);
      const end = new Date(pause.end_time);
      return total + Math.floor((end - start) / 1000); // segundos totales
    }
    return total;
  }, 0) : 0;
  const totalActivePauses = activePauses ? activePauses.length : 0;
  
  // Mostrar resumen de totales
  content += `
    <div class="totals-summary">
      <div class="total-item">
        <strong>Total de Pausas:</strong> ${totalPauses}
      </div>
      <div class="total-item">
        <strong>Tiempo Total en Pausa:</strong> ${formatDuration(totalTimeInPauseSeconds)}
      </div>
      <div class="total-item">
        <strong>Pausas Activas:</strong> ${totalActivePauses}
      </div>
    </div>
  `;
  
  // Mostrar todas las pausas del usuario
  content += `<h4>Todas las Pausas del Usuario</h4>`;
  
  if (pauses && pauses.length > 0) {
    content += `
      <div class="table-container">
        <div class="data-table">
          <table>
            <thead>
              <tr>
                <th>FECHA</th>
                <th>INICIO</th>
                <th>FIN</th>
                <th>RAZÓN</th>
                <th>DURACIÓN</th>
                <th>ESTADO</th>
              </tr>
            </thead>
            <tbody>
    `;
    
    pauses.forEach(pause => {
      const startTime = new Date(pause.start_time);
      const endTime = pause.end_time ? new Date(pause.end_time) : null;
      const durationInSeconds = endTime ? Math.floor((endTime - startTime) / 1000) : null;
      const duration = endTime ? formatDuration(durationInSeconds) : 'En curso';
      const isActive = !endTime;
      const pauseDate = startTime.toLocaleDateString('es-ES');
      
      content += `
        <tr>
          <td>${pauseDate}</td>
          <td>${startTime.toLocaleTimeString()}</td>
          <td>${endTime ? endTime.toLocaleTimeString() : '<span class="active-status">En curso</span>'}</td>
          <td class="${isActive ? 'active-reason' : ''}">${pause.reason || 'Sin razón'}</td>
          <td>${duration}</td>
          <td>
            <span class="status-badge ${isActive ? 'status-active' : 'status-completed'}">
              ${isActive ? 'Activa' : 'Completada'}
            </span>
          </td>
        </tr>
      `;
    });
    
    content += `
            </tbody>
          </table>
        </div>
      </div>
    `;
  } else {
    content += '<p>No hay pausas registradas para este usuario.</p>';
  }
  
  modalContent.innerHTML = content;
  modal.style.display = 'flex';
  
  // Reemplazar los iconos de Feather después de mostrar el modal
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
}

// Función para cerrar el modal
function closeUserModal() {
  document.getElementById('userModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
  const modal = document.getElementById('userModal');
  if (event.target === modal) {
    closeUserModal();
  }
}
</script>

<!-- Modal para mostrar datos del usuario -->
<div id="userModal" class="modal-overlay" style="display: none;">
  <div class="modal-content" style="max-width: 600px;">
    <div id="userModalContent">
      <!-- El contenido se cargará dinámicamente -->
    </div>
    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="closeUserModal()">Cerrar</button>
    </div>
  </div>
</div>

<?php
require_once 'template/footer.php';
?>
