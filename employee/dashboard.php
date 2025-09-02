<?php
require_once 'template/header.php';
require_once "../repository/supabase_employee.php";
require_once "../data/users.php";
require_once "../data/evaluations.php";

$supabase = new Supabase();

$pauseReasons = [
  "Almuerzo",
  "Break 10 minutos",
  "Reunión",
  "Asunto personal",
  "Baño de afuera",
  "Reunión oficina",
  "Ayuda a un compañero",
  "Solicitando ayuda",
  "Baño de oficina",
  "Por Agua",
  "Otro"
];
?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
     <div class="card">
       <!-- Header Card -->
       <div class="header-card">
        <div class="title">Bienvenido, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!</div>
        <div class="description">Sistema de control de pausas</div>
        
        <!-- Formulario de Pausa en el Header -->
        <div class="pause-form-container">
          <div class="form-group">
            <label for="pauseReason">Razón de la pausa</label>
            <select class="form-control" id="pauseReason" required>
              <option value="">Seleccione una razón</option>
              <?php foreach ($pauseReasons as $reason): ?>
                <option value="<?= htmlspecialchars($reason) ?>"><?= htmlspecialchars($reason) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="switch-container">
            <label class="switch">
              <input type="checkbox" id="pauseToggle">
              <span class="slider round"></span>
            </label>
            <span class="switch-label">Switch de Pausa</span>
          </div>
          
          <!-- Indicador de pausa activa -->
          <div id="activePauseIndicator" class="active-pause-indicator" style="display: none;">
            <i data-feather="pause" class="pause-icon"></i>
            <span id="activePauseText">Pausa activa</span>
          </div>
        </div>
      </div>

      <!-- Body Card: Contenido principal -->
      <div class="body-card">
        <!-- Status Indicators -->
        <div class="status-container" style="display: none;">
          <!-- Recording Indicator -->
          <div class="status-indicator status-rec">
            <div class="status-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
              </svg>
              <div class="status-pulse"></div>
            </div>
            <span>REC</span>
          </div>
          
          <!-- Microphone Indicator -->
          <div class="status-indicator status-mic">
            <div class="status-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
              </svg>
              <div class="status-pulse"></div>
            </div>
            <span>MIC</span>
          </div>
          
          <!-- Live Monitoring -->
          <div class="status-indicator status-live">
            <div class="status-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              <div class="status-pulse"></div>
            </div>
            <span>LIVE</span>
          </div>
          
          <!-- User Status -->
          <div class="status-indicator status-user">
            <div class="status-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              <div class="status-pulse"></div>
            </div>
            <span>ON</span>
          </div>
        </div>

        <!-- Main Content -->
        <div class="card-column-two">
          <!-- Columna Izquierda: Solo Historial -->
          <div class="column-one">
           
            <!-- Body Card: Contenido del Historial -->
            <div class="body-card">
              <h3 class="title">Historial de Pausas</h3>
              <div id="pausesContainer" class="pauses-list">
                <!-- Las pausas se cargarán aquí dinámicamente -->
              </div>
            </div>
          </div>

          <!-- Columna Derecha: Área libre para futuras funcionalidades -->
          <div class="column-two">
            <!-- Header Card: Área Libre -->
            <div class="header-card">
              <div class="title">Área Libre</div>
            </div>
            <!-- Body Card: Contenido del Área Libre -->
            <div class="body-card">
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

    <!-- CSS optimizado incluido en el header -->

  
  </main>
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
        const activePauseIndicator = document.getElementById('activePauseIndicator');
        const activePauseText = document.getElementById('activePauseText');
        
        console.log('Elementos encontrados:', {
          pauseToggle: !!pauseToggle,
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
          activePauseIndicator.style.display = 'block';
          
          // También mostrar la pausa activa en el historial
          const activePauseItem = document.createElement('div');
          activePauseItem.className = 'pause-item active';
          activePauseItem.innerHTML = `
            <div class="pause-item-content">
            <div class="pause-icon active">
              <i data-feather="pause"></i>
            </div>
              <div class="pause-item-details">
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
            </div>
            <div class="pause-item-actions">
            <div class="pause-duration active">
              En curso
              </div>
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
              const activePauseIndicator = document.getElementById('activePauseIndicator');
              
              pauseToggle.checked = false;
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
            <div class="pause-item-content">
            <div class="pause-icon completed">
              <i data-feather="check"></i>
            </div>
              <div class="pause-item-details">
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
            </div>
            <div class="pause-item-actions">
            <div class="pause-duration">
              ${formatDuration(duration)}
              </div>
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
          
          if (result.success && result.pause) {
            // Actualizar UI para pausa activa
            pauseToggle.checked = true;
            
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
            const activePauseIndicator = document.getElementById('activePauseIndicator');
            
            // Limpiar estado
            activePauseId = null;
            pauseToggle.checked = false;
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
<?php
require_once 'template/footer.php';
?>