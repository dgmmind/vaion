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

// Obtener todos los empleados de todos los managers
$allEmployees = [];
foreach ($users as $userId => $user) {
    if ($user['role'] === 'admin' && isset($user['employees'])) {
        foreach ($user['employees'] as $employee) {
            $employee['manager_name'] = $user['name'];
            $employee['manager_id'] = $user['id'];
            $employee['department'] = $user['DEPARTMENT'] ?? 'N/A';
            $allEmployees[] = $employee;
        }
    }
}

// Fecha del día que estamos monitoreando (automática)
$monitoringDate = date('Y-m-d'); // Detecta automáticamente el día actual

// Obtener todas las pausas del día actual de todos los empleados
$allPausesResponse = $supabase->select('pauses', '*', [
    'conditions' => 'employee_id.in.(' . implode(',', array_map(function($user) { return $user['id']; }, $allEmployees)) . '),start_time.gte.' . $monitoringDate . 'T00:00:00,start_time.lt.' . $monitoringDate . 'T23:59:59'
]);

$allPauses = [];
if ($allPausesResponse['status'] === 200 && isset($allPausesResponse['data'])) {
    $allPauses = $allPausesResponse['data'];
}

// Obtener solo las pausas activas del día actual
$pausesResponse = $supabase->select('pauses', '*', [
    'conditions' => 'end_time.is.null,start_time.gte.' . $monitoringDate . 'T00:00:00,start_time.lt.' . $monitoringDate . 'T23:59:59'
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

// Separar usuarios en dos grupos
$usersOnPause = [];
$usersWorking = [];

foreach ($allEmployees as $user) {
    if (isset($usersWithActivePause[$user['id']])) {
        $usersOnPause[] = $user;
    } else {
        $usersWorking[] = $user;
    }
}
?>

<!-- Main Content -->
<main class="main-content">
  <div class="container">
    <div class="card">
      <!-- Header Card -->
      <div class="header-card">
        <div class="title">Monitoreo en Tiempo Real - Super Admin</div>
        <div class="description">Estado actual de todos los empleados del sistema</div>
      </div>
      
      <!-- Body Card -->
      <div class="body-card">
        <div class="table-container">
          <h3>Estado de Empleados</h3>
          
          <div class="card-column-two">
            <!-- Columna 1: Usuarios con Pausas -->
            <div class="column-one">
              <h3>Empleados en Pausa (<?php echo count($usersOnPause); ?>)</h3>
              <?php if (empty($usersOnPause)): ?>
                <div class="description">Todos los empleados están trabajando</div>
              <?php else: ?>
                <div class="data-table">
                  <table>
                    <thead>
                      <tr>
                        <th>EMPLEADO</th>
                        <th>DEPARTMENT</th>
                        <th>RAZÓN</th>
                        <th>INICIO</th>
                        <th>TOTAL PAUSAS</th>
                        <th>TOTAL TIEMPO</th>
                        <th>ACCIONES</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($usersOnPause as $user): ?>
                        <?php $pause = $usersWithActivePause[$user['id']]; ?>
                        <tr>
                          <td class="text-left font-weight-bold">
                            <div class="user-avatar">
                              <i data-feather="user"></i>
                            </div>
                            <?php echo htmlspecialchars($user['name']); ?>
                          </td>
                          <td><?php echo htmlspecialchars($user['department']); ?></td>
                          <td class="active-reason"><?php echo htmlspecialchars($pause['reason'] ?? 'Sin razón especificada'); ?></td>
                          <td><?php echo date('d/m/Y H:i', strtotime($pause['start_time'])); ?></td>
                          <td class="total-column"><?php 
                            // Contar TODAS las pausas de este usuario específico
                            $userTotalPauses = 0;
                            foreach ($allPauses as $p) {
                              if ($p['employee_id'] == $user['id']) {
                                $userTotalPauses++;
                              }
                            }
                            echo $userTotalPauses;
                          ?></td>
                          <td class="total-column"><?php 
                            // Si está en pausa activa, mostrar contador en tiempo real
                            if (!isset($pause['end_time']) || $pause['end_time'] === null) {
                              $startTime = strtotime($pause['start_time']);
                              echo '<span class="in-progress" data-start-time="' . $startTime . '">Calculando...</span>';
                            } else {
                              // Calcular tiempo total en pausa de este usuario específico (solo pausas completadas)
                              $userTotalTime = 0;
                              foreach ($allPauses as $p) {
                                if ($p['employee_id'] == $user['id'] && $p['end_time']) {
                                  $start = strtotime($p['start_time']);
                                  $end = strtotime($p['end_time']);
                                  $userTotalTime += round(($end - $start) / 60);
                                }
                              }
                              echo $userTotalTime . ' min';
                            }
                          ?></td>
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
              
              <!-- Tabla de Usuarios sin Pausas dentro de la columna ONE -->
              <?php
                // Usuarios que no han tenido pausas en la fecha monitoreada
                $usersWithoutPauses = [];
                
                foreach ($allEmployees as $user) {
                    $hasPausesToday = false;
                    foreach ($allPauses as $pause) {
                        if ($pause['employee_id'] == $user['id']) {
                            $hasPausesToday = true;
                            break;
                        }
                    }
                    if (!$hasPausesToday) {
                        $usersWithoutPauses[] = $user;
                    }
                }
              ?>
              
              <?php if (!empty($usersWithoutPauses)): ?>
                <div class="table-container" style="margin-top: 20px;">
                  <h3 class="no-pauses-title">⚠️ Usuarios sin Pausas el <?php echo date('d/m/Y', strtotime($monitoringDate)); ?> (<?php echo count($usersWithoutPauses); ?>)</h3>
                  <p class="no-pauses-description">Estos usuarios no han registrado pausas en la fecha monitoreada, lo cual es anormal</p>
                  
                  <div class="data-table">
                    <table>
                      <thead>
                        <tr>
                          <th>EMPLEADO</th>
                          <th>DEPARTMENT</th>
                          <th>ESTADO</th>
                          <th>ACCIONES</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($usersWithoutPauses as $user): ?>
                          <tr class="no-pauses-row">
                            <td class="text-left font-weight-bold">
                              <div class="user-avatar">
                                <i data-feather="user"></i>
                              </div>
                              <?php echo htmlspecialchars($user['name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['department']); ?></td>
                            <td>
                              <span class="status-warning">Sin Pausas</span>
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
                </div>
              <?php endif; ?>
            </div>

            <!-- Columna 2: Usuarios Trabajando -->
            <div class="column-two">
              <h3>Empleados Trabajando (<?php echo count($usersWorking); ?>)</h3>
              <?php if (empty($usersWorking)): ?>
                <div class="description">Todos los empleados están en pausa</div>
              <?php else: ?>
                <div class="data-table">
                  <table>
                    <thead>
                      <tr>
                        <th>EMPLEADO</th>
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
                          <td>
                            <span class="status-working">Working</span>
                          </td>
                          <td class="total-column"><?php 
                            // Contar TODAS las pausas de este usuario (activas y completadas)
                            $userPauses = 0;
                            foreach ($allPauses as $p) {
                              if ($p['employee_id'] == $user['id']) {
                                $userPauses++;
                              }
                            }
                            echo $userPauses;
                          ?></td>
                          <td class="total-column"><?php 
                            // Calcular tiempo total en pausa de este usuario específico (solo pausas completadas)
                            $totalSeconds = 0;
                            foreach ($allPauses as $p) {
                              if ($p['employee_id'] == $user['id'] && $p['end_time']) {
                                $start = new DateTime($p['start_time']);
                                $end = new DateTime($p['end_time']);
                                $interval = $start->diff($end);
                                $totalSeconds += $interval->h * 3600 + $interval->i * 60 + $interval->s;
                              }
                            }
                            
                            // Formatear el tiempo total
                            $hours = floor($totalSeconds / 3600);
                            $minutes = floor(($totalSeconds % 3600) / 60);
                            $seconds = $totalSeconds % 60;
                            
                            $timeString = '';
                            if ($hours > 0) $timeString .= $hours . 'h ';
                            if ($minutes > 0 || $hours > 0) $timeString .= $minutes . 'm ';
                            $timeString .= $seconds . 's';
                            
                            echo $timeString;
                          ?></td>
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

// Actualizar contadores cada segundo
setInterval(updateTimers, 1000);

// Actualizar inmediatamente al cargar
updateTimers();

// Auto-refresh cada 1 minuto
setInterval(function() {
  // Solo actualizar si la página está visible
  if (!document.hidden) {
    location.reload();
  }
}, 60000); // 1 minuto

// Función para mostrar el modal del usuario
function showUserModal(userId, userName, userUsername) {
  // Usar la API del superadmin
  fetch(`api.php?action=getUserPauses&userId=${userId}`)
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
