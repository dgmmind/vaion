<?php
require_once 'template/header.php';
require_once "../repository/supabase.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

$supabase = new Supabase();

$pauseReasons = [
  "Almuerzo",
  "Break corto",
  "Reunión",
  "Asunto personal",
  "Otro"
];

?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <div class="dashboard-card">
        <!-- Main grid section with background -->
        <div class="grid-section">
        <!-- Header -->
        <div class="card-header">
          <div class="d-flex justify-between items-center">
            <div>
              <h1 class="title">Bienvenido, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!</h1>
              <p class="text-gray-600">Sistema de control de pausas</p>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
          <!-- Columna Izquierda: Estado de Pausa e Historial -->
          <div class="left-column">
            <!-- Sección 1: Estado de Pausa -->
            <div class="dashboard-section">
              <div class="section-header">
                <h2>Estado de Pausa</h2>
              </div>
              <div class="section-body">
                <div class="pause-status-container">
                  <!-- Estado actual de la pausa -->
                  <div id="currentPauseStatus" class="current-status">
                    <div class="status-info">
                      <span id="pauseStatusText" class="status-text">En línea</span>
                      <span id="pauseReasonText" class="reason-text"></span>
                    </div>
                    <div class="toggle-container">
                      <label class="switch">
                        <input type="checkbox" id="pauseToggle">
                        <span class="slider round"></span>
                      </label>
                    </div>
                  </div>
                  
                  <!-- Formulario para iniciar pausa -->
                  <div id="pauseForm" class="pause-form">
                    <div class="form-group">
                      <label for="pauseReason">Razón de la pausa</label>
                      <select class="form-control" id="pauseReason" required>
                        <option value="">Seleccione una razón</option>
                        <?php foreach ($pauseReasons as $reason): ?>
                          <option value="<?= htmlspecialchars($reason) ?>"><?= htmlspecialchars($reason) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  
                  <!-- Indicador de pausa activa -->
                  <div id="activePauseIndicator" class="active-pause-indicator" style="display: none;">
                    <i data-feather="pause" class="pause-icon"></i>
                    <span id="activePauseText">Pausa activa</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sección 2: Historial Reciente -->
            <div class="dashboard-section">
              <div class="section-header">
                <h2>Historial Reciente</h2>
              </div>
              <div class="section-body">
                <div id="pausesContainer" class="pauses-list">
                  <!-- Las pausas se cargarán aquí dinámicamente -->
                </div>
              </div>
            </div>
          </div>

          <!-- Columna Derecha: Área libre para futuras funcionalidades -->
          <div class="right-column">
            <div class="dashboard-section">
              <div class="section-header">
                <h2>Área Libre</h2>
              </div>
              <div class="section-body">
                <div class="free-area">
                  <p class="text-gray-500 text-center py-8">
                    <i data-feather="plus-circle" class="w-8 h-8 mx-auto mb-2 text-gray-400"></i>
                    Área disponible para futuras funcionalidades
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <style>
    /* Grid Section Styles */
    .grid-section {
      background-image:
        linear-gradient(rgba(226, 232, 240, 0.5) 1px, transparent 1px),
        linear-gradient(90deg, rgba(226, 232, 240, 0.5) 1px, transparent 1px);
      background-size: 20px 20px;
      background-position: 0 0;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      padding: 1.5rem;
    }
    
    .dashboard-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .dashboard-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      margin: 20px 0;
    }

    .left-column {
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    .right-column {
      display: flex;
      flex-direction: column;
    }
    
    /* Grid Section Styles */
    .grid-section {
      background-image:
        linear-gradient(rgba(226, 232, 240, 0.5) 1px, transparent 1px),
        linear-gradient(90deg, rgba(226, 232, 240, 0.5) 1px, transparent 1px);
      background-size: 20px 20px;
      background-position: 0 0;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      padding: 1.5rem;
    }
    
    .dashboard-section {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
      border: 1px solid #e2e8f0;
      margin-bottom: 1.5rem;
    }
    
    .dashboard-section:last-child {
      margin-bottom: 0;
    }
    
    .section-header {
      padding: 1rem 1.25rem;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
    }
    
    .section-header h2 {
      margin: 0;
      font-size: 1.125rem;
      font-weight: 600;
      color: #1e293b;
    }
    
    .section-body {
      padding: 1.25rem;
      background: white;
      border-radius: 0 0 8px 8px;
    }
    
    .pauses-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1rem;
    }
    
    .card {
      margin-bottom: 1.5rem;
      background: white;
      border-radius: 0.5rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    
    .card-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #e2e8f0;
      background: #f8fafc;
    }
    
    .card-header h1, 
    .card-header h2 {
      margin: 0;
      font-size: 1.25rem;
      font-weight: 600;
      color: #1e293b;
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    .pauses-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1rem;
      padding: 0.5rem;
    }
    
    .pause-card {
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 0.5rem;
      padding: 1rem;
      transition: all 0.2s ease;
    }
    
    .pause-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    }
    
    .pause-card.active {
      border-left: 4px solid #3b82f6;
    }
    
    .switch-container {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-top: 1.75rem;
    }
    
    .status-text {
      font-size: 0.875rem;
      color: #64748b;
      font-weight: 500;
    }
    
    .form-row {
      display: flex;
      gap: 2rem;
      align-items: flex-start;
    }
    
    .form-group {
      flex: 1;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #475569;
      font-size: 0.875rem;
    }
    
    .form-control {
      width: 100%;
      padding: 0.5rem 0.75rem;
      border: 1px solid #e2e8f0;
      border-radius: 0.375rem;
      background-color: white;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
      border-color: #93c5fd;
      outline: 0;
      box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.5);
    }
    
    /* Switch styles */
    .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }
    
    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #e2e8f0;
      transition: .4s;
      border-radius: 34px;
    }
    
    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    
    input:checked + .slider {
      background-color: #3b82f6;
    }
    
    input:focus + .slider {
      box-shadow: 0 0 1px #3b82f6;
    }
    
    input:checked + .slider:before {
      transform: translateX(26px);
    }

    /* Estilos para el nuevo diseño */
    .pause-status-container {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .current-status {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      background: #f8fafc;
      border-radius: 0.5rem;
      border: 1px solid #e2e8f0;
    }

    .status-info {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .status-text {
      font-size: 1rem;
      font-weight: 600;
      color: #1e293b;
    }

    .reason-text {
      font-size: 0.875rem;
      color: #64748b;
    }

    .toggle-container {
      display: flex;
      align-items: center;
    }

    .pause-form {
      padding: 1rem;
      background: white;
      border-radius: 0.5rem;
      border: 1px solid #e2e8f0;
    }

    .active-pause-indicator {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1rem;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 0.5rem;
      color: #dc2626;
      font-weight: 500;
    }

    .active-pause-indicator .pause-icon {
      width: 1.25rem;
      height: 1.25rem;
      stroke-width: 2;
    }

    .pauses-list {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .pause-item {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      padding: 1rem;
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 0.5rem;
      transition: all 0.2s ease;
    }

    .pause-item:hover {
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .pause-icon.completed {
      width: 2rem;
      height: 2rem;
      background: #374151;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      flex-shrink: 0;
    }

    .pause-icon.completed i {
      width: 1rem;
      height: 1rem;
      stroke-width: 3;
    }

    .pause-icon.active {
      width: 2rem;
      height: 2rem;
      background: #dc2626;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      flex-shrink: 0;
    }

    .pause-icon.active i {
      width: 1rem;
      height: 1rem;
      stroke-width: 3;
    }

    .pause-item.active {
      border-color: #fecaca;
      background: #fef2f2;
    }

    .pause-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .pause-reason {
      font-weight: 600;
      color: #1e293b;
      font-size: 1rem;
    }

    .pause-times {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
      font-size: 0.875rem;
    }

    .time-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .time-label {
      color: #94a3b8;
      min-width: 3rem;
    }

    .time-value {
      color: #374151;
      font-weight: 500;
    }

    .pause-duration {
      font-weight: 600;
      color: #374151;
      font-size: 0.875rem;
      align-self: flex-start;
      margin-top: 0.25rem;
    }

    .pause-duration.active {
      color: #dc2626;
    }

    .view-more-btn {
      width: 100%;
      margin-top: 1rem;
      padding: 0.75rem;
      text-align: center;
      font-size: 0.875rem;
      font-weight: 500;
      color: #3b82f6;
      background: transparent;
      border: 1px solid #3b82f6;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .view-more-btn:hover {
      background: #3b82f6;
      color: white;
    }

    .free-area {
      min-height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .free-area i {
      color: #9ca3af;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const pausesContainer = document.getElementById('pausesContainer');
      let activePauseId = null;

      // Cargar datos iniciales - SOLO UNA PETICIÓN
      loadInitialPauseData();

      // Inicializar Feather Icons
      if (window.feather) {
        window.feather.replace();
      }

      // Función para cargar los datos de pausa
      async function loadInitialPauseData() {
        try {
          const response = await fetch('api.php?action=getPauses');
          
          if (!response.ok) {
            throw new Error('Error al cargar los datos de pausa');
          }
          
          const result = await response.json();
          
          if (result.success) {
            // Procesar pausas pasadas (solo las que ya terminaron)
            const pastPauses = result.pauses.filter(p => p.end_time);
            renderPauses(pastPauses);
            
            // Buscar pausa activa (sin end_time)
            const activePause = result.pauses.find(p => p.end_time === null);
            
            if (activePause) {
              updatePauseUI(true, activePause);
              activePauseId = activePause.pause_id;
            } else {
              // Solo actualizar la UI si no hay pausa activa y no se está procesando una finalización
              if (activePauseId === null) {
                updatePauseUI(false);
              }
            }
          }
          
        } catch (error) {
          console.error('Error al cargar datos de pausa:', error);
          // No mostrar alerta para no molestar al usuario
        }
      }
      
      // Actualizar la interfaz de usuario según el estado de la pausa
      function updatePauseUI(isActive, pauseData = null) {
        console.log('updatePauseUI llamado:', { isActive, pauseData });
        
        const pauseToggle = document.getElementById('pauseToggle');
        const pauseStatusText = document.getElementById('pauseStatusText');
        const pauseReasonText = document.getElementById('pauseReasonText');
        const activePauseIndicator = document.getElementById('activePauseIndicator');
        const activePauseText = document.getElementById('activePauseText');
        
        console.log('Elementos encontrados:', {
          pauseToggle: !!pauseToggle,
          pauseStatusText: !!pauseStatusText,
          pauseReasonText: !!pauseReasonText,
          activePauseIndicator: !!activePauseIndicator,
          activePauseText: !!activePauseText
        });
        
        // Actualizar el estado del toggle
        pauseToggle.checked = isActive;
        
        if (isActive && pauseData) {
          // Mostrar información de la pausa activa
          const startTime = new Date(pauseData.start_time);
          
          // Actualizar el indicador de pausa activa
          activePauseText.textContent = `Pausa activa`;
          pauseStatusText.textContent = 'En pausa';
          pauseReasonText.textContent = pauseData.reason || 'Sin motivo especificado';
          activePauseIndicator.style.display = 'block';
          
          // También mostrar la pausa activa en el historial
          const activePauseItem = document.createElement('div');
          activePauseItem.className = 'pause-item active';
          activePauseItem.innerHTML = `
            <div class="pause-icon active">
              <i data-feather="pause"></i>
            </div>
            <div class="pause-details">
              <div class="pause-reason">${pauseData.reason || 'Sin motivo especificado'}</div>
              <div class="pause-times">
                <div class="time-row">
                  <span class="time-label">Inicio:</span>
                  <span class="time-value">${formatDate(startTime)}</span>
                </div>
                <div class="time-row">
                  <span class="time-label">Fin:</span>
                  <span class="time-value">En curso</span>
                </div>
              </div>
            </div>
            <div class="pause-duration active">
              En curso
            </div>
          `;
          
          // Insertar al principio del historial
          if (pausesContainer.firstChild) {
            pausesContainer.insertBefore(activePauseItem, pausesContainer.firstChild);
          } else {
            pausesContainer.appendChild(activePauseItem);
          }
          
          // Inicializar los iconos de Feather en el nuevo elemento
          if (window.feather) {
            window.feather.replace();
          }
          
          activePauseId = pauseData.pause_id || pauseData.id;
          
        } else {
          // Limpiar el estado de pausa
          activePauseId = null;
          pauseToggle.checked = false;
          pauseStatusText.textContent = 'En línea';
          pauseReasonText.textContent = '';
          activePauseIndicator.style.display = 'none';
          
          // Remover la pausa activa del historial si existe
          const activePauseItem = pausesContainer.querySelector('.pause-item.active');
          if (activePauseItem) {
            activePauseItem.remove();
          }
        }
        

      }

      // Load pauses history
      async function loadPauses() {
        try {
          const response = await fetch(`api.php?action=getPauses`);
          const result = await response.json();
          
          if (result.success) {
            renderPauses(result.pauses);
            
            // Verificar si hay pausa activa después de cargar el historial
            const activePause = result.pauses.find(p => p.end_time === null);
            if (!activePause && activePauseId !== null) {
              // Si no hay pausa activa pero activePauseId no es null, limpiar el estado
              activePauseId = null;
              const pauseToggle = document.getElementById('pauseToggle');
              const pauseStatusText = document.getElementById('pauseStatusText');
              const pauseReasonText = document.getElementById('pauseReasonText');
              const activePauseIndicator = document.getElementById('activePauseIndicator');
              
              pauseToggle.checked = false;
              pauseStatusText.textContent = 'En línea';
              pauseReasonText.textContent = '';
              activePauseIndicator.style.display = 'none';
            }
          }
        } catch (error) {
          console.error('Error loading pauses:', error);
        }
      }

      // Render active pause
      function renderActivePause(pause) {
        const activePauseIndicator = document.getElementById('activePauseIndicator');
        const activePauseText = document.getElementById('activePauseText');
        
        if (pause) {
          const startTime = new Date(pause.start_time);
          
          activePauseText.textContent = `Pausa activa`;
          activePauseIndicator.style.display = 'block';
          

        } else {
          activePauseIndicator.style.display = 'none';

        }
      }
      
      // Render pauses in the UI
      function renderPauses(pauses) {
        // Solo procesar pausas pasadas, no modificar el estado activo aquí
        const pastPauses = pauses.filter(p => p.end_time);
        
        // No renderizar pausa activa aquí, ya se maneja en processActivePause
        // para evitar conflictos con el estado del switch
        
        // Render past pauses
        pausesContainer.innerHTML = '';
        
        if (pastPauses.length === 0) {
          pausesContainer.innerHTML = `
            <div class="text-center py-8 text-gray-400">
              <i data-feather="clock" class="w-6 h-6 mx-auto mb-2"></i>
              <p class="text-sm">No hay pausas registradas</p>
            </div>
          `;
          
          if (window.feather) {
            window.feather.replace();
          }
          return;
        }
        
        pastPauses.slice(0, 10).forEach((pause, index) => {
          const pauseCard = document.createElement('div');
          const startTime = new Date(pause.start_time);
          const endTime = new Date(pause.end_time);
          const duration = (endTime - startTime) / 1000;
          
          pauseCard.className = 'pause-item';
          pauseCard.innerHTML = `
            <div class="pause-icon completed">
              <i data-feather="check"></i>
            </div>
            <div class="pause-details">
              <div class="pause-reason">${pause.reason}</div>
              <div class="pause-times">
                <div class="time-row">
                  <span class="time-label">Inicio:</span>
                  <span class="time-value">${formatDate(startTime)}</span>
                </div>
                <div class="time-row">
                  <span class="time-label">Fin:</span>
                  <span class="time-value">${formatDate(endTime)}</span>
                </div>
              </div>
            </div>
            <div class="pause-duration">
              ${formatDuration(duration)}
            </div>
          `;
          
          pausesContainer.appendChild(pauseCard);
        });
        
        // Add view more button if there are more than 10 pauses
        if (pastPauses.length > 10) {
          const viewMore = document.createElement('button');
          viewMore.className = 'view-more-btn';
          viewMore.textContent = 'Ver más pausas';
          viewMore.onclick = () => {
            // Implement view more functionality here
            console.log('View more clicked');
          };
          pausesContainer.appendChild(viewMore);
        }
        
        // Refresh Feather icons
        if (window.feather) {
          window.feather.replace();
        }
      }

      // Helper function to format time
      function formatTime(date) {
        return date.toLocaleTimeString('es-ES', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        });
      }

      // Helper function to format date for pause history
      function formatDate(date) {
        return date.toLocaleDateString('es-ES', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        }) + ' ' + date.toLocaleTimeString('es-ES', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        });
      }

      // Format duration in seconds to HH:MM:SS
      function formatDuration(seconds) {
        // Ensure we don't get negative values
        seconds = Math.max(0, Math.floor(seconds));
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        // Formato como en la imagen: mostrar solo segundos si es menos de 1 minuto
        if (seconds < 60) {
          return `${seconds}s`;
        }
        
        // Si es más de 1 minuto, mostrar MM:SS
        if (hours === 0) {
          return `${minutes}:${String(secs).padStart(2, '0')}`;
        }
        
        // Si hay horas, mostrar HH:MM:SS
        return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
      }

      // Verificar pausa activa al cargar
      async function checkActivePause() {
        try {
          console.log('Verificando pausa activa...');
          const response = await fetch('api.php?action=getActivePause');
          
          if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
          }
          
          const result = await response.json();
          console.log('Resultado de verificación de pausa activa:', result);
          
          const pauseToggle = document.getElementById('pauseToggle');
          const pauseStatusText = document.getElementById('pauseStatusText');
          const pauseReasonText = document.getElementById('pauseReasonText');
          
          if (result.success && result.pause) {
            // Actualizar UI para pausa activa
            pauseToggle.checked = true;
            pauseStatusText.textContent = 'Pausa activa';
            pauseReasonText.textContent = result.pause.reason || '';
            
            // Asegurarse de que el contenedor de pausa activa esté visible
            document.getElementById('activePauseIndicator').style.display = 'block';
            
            // Renderizar la pausa activa
            renderActivePause(result.pause);
            
            // Actualizar el ID de la pausa activa
            activePauseId = result.pause.id || result.pause.pause_id;
            
            console.log('Pausa activa encontrada:', result.pause);
          } else {
            // Restablecer estado por defecto
            pauseToggle.checked = false;
            pauseStatusText.textContent = 'Pausa inactiva';
            pauseReasonText.textContent = ''; // Limpiar la razón
            document.getElementById('activePauseIndicator').style.display = 'none';
            activePauseId = null;
            console.log('No hay pausa activa');
          }
          
          return result;
        } catch (error) {
          console.error('Error checking active pause:', error);
          // Reset to default state on error
          const pauseToggle = document.getElementById('pauseToggle');
          pauseToggle.checked = false;
          document.getElementById('pauseStatusText').textContent = 'Error verificando estado';
          return { success: false, error: error.message };
        }
      }

      // Manejador único para el botón de pausa
      const pauseToggle = document.getElementById('pauseToggle');
      
      // Remover cualquier manejador previo para evitar duplicados
      const newPauseToggle = pauseToggle.cloneNode(true);
      pauseToggle.parentNode.replaceChild(newPauseToggle, pauseToggle);
      
      // Agregar el manejador al nuevo elemento
      newPauseToggle.addEventListener('change', handlePauseToggle);
      
      // Manejar el cambio en el toggle de pausa
      async function handlePauseToggle() {
        const isStartingPause = this.checked;
        const reason = document.getElementById('pauseReason').value;
        const pauseStatusText = document.getElementById('pauseStatusText');
        
        // Validar si se está iniciando una pausa sin razón
        if (isStartingPause && !reason.trim()) {
          alert('Por favor ingresa un motivo para la pausa');
          this.checked = false;
          return;
        }
        
        const action = isStartingPause ? 'startPause' : 'endPause';
        
        try {
          // Deshabilitar el toggle mientras se procesa la solicitud
          this.disabled = true;
          
          const formData = new FormData();
          formData.append('action', action);
          
          // Si es una pausa existente, incluir el ID
          if (!isStartingPause && activePauseId) {
            formData.append('pause_id', activePauseId);
          }
          
          // Solo agregar la razón si se está iniciando una pausa
          if (isStartingPause) {
            formData.append('reason', reason);
          }
          
          const response = await fetch('api.php', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
          });
          
          if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
          }
          
          const result = await response.json();
          
          if (!result.success) {
            throw new Error(result.message || 'Error al procesar la pausa');
          }
          
          // Actualizar la interfaz según la acción
          if (isStartingPause) {
            // Pausa iniciada exitosamente
            activePauseId = result.pause_id;
            updatePauseUI(true, {
              ...result,
              start_time: new Date().toISOString(),
              reason: reason
            });
          } else {
            // Pausa finalizada exitosamente
            
            // Limpiar la UI inmediatamente
            const pauseToggle = document.getElementById('pauseToggle');
            const pauseStatusText = document.getElementById('pauseStatusText');
            const pauseReasonText = document.getElementById('pauseReasonText');
            const activePauseIndicator = document.getElementById('activePauseIndicator');
            
            // Limpiar estado
            activePauseId = null;
            pauseToggle.checked = false;
            pauseStatusText.textContent = 'En línea';
            pauseReasonText.textContent = '';
            activePauseIndicator.style.display = 'none';
            

            
            // Verificar el estado real de la base de datos y actualizar historial
            setTimeout(async () => {
              try {
                // Solo recargar los datos una vez - loadInitialPauseData ya incluye renderPauses
                await loadInitialPauseData();
              } catch (error) {
                console.error('Error al recargar datos después de finalizar pausa:', error);
              }
            }, 500); // Pequeño delay para asegurar que la base de datos se haya actualizado
          }
          
        } catch (error) {
          console.error('Error:', error);
          // Revertir el estado del toggle si hay un error
          this.checked = !isStartingPause;
          alert('Error al conectar con el servidor: ' + error.message);
        } finally {
          // Volver a habilitar el toggle
          this.disabled = false;
        }
      }

    });
    </script>
  </main>

<?php
require_once 'template/footer.php';
?>