<?php
require_once 'template/header.php';
?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <div class="card">
        <div class="header-card">
          <h1 class="title">Registrar Day</h1>
          <p class="description">Crea un nuevo día para evaluaciones</p>
        </div>
        
        <div class="body-card">
          <div class="card-column-two">
            <!-- Left Column - Form -->
            <div class="column-one">
              <form id="dayForm" class="day-form">
                <div>
                  <label>Fecha del día:</label>
                  <input type="date" id="day_date" name="day_date" required>
                </div>
                <button type="submit">
                  Crear día
                </button>
              </form>

              <!-- Current Week Section -->
              <div class="week-section">
                <h3>Días Laborables</h3>
                <div id="current-week" class="week-grid">
                  <!-- Days will be populated by JavaScript -->
                </div>
              </div>
            </div>

            <!-- Right Column - Other Days List -->
            <div class="column-two">
              <h3>Otras Fechas</h3>
              <div id="show-days" class="other-days"></div>
            </div>
          </div>
        </div>
      </div>

      <script>
      // Function to format date from YYYY-MM-DD to Spanish format
      function formatDateSpanish(dateStr) {
          try {
              if (!dateStr) return 'Fecha no disponible';
              
              // Parse the date string (YYYY-MM-DD format from database)
              const [year, month, day] = dateStr.split('-').map(Number);
              
              // Create a date object (local time)
              const date = new Date(year, month - 1, day);
              
              // Verify the date is valid
              if (isNaN(date.getTime())) {
                  console.error('Invalid date:', dateStr);
                  return 'Fecha inválida';
              }
              
              // Define day and month names in Spanish
              const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
              const monthNames = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
              
              // Format the date
              const dayName = dayNames[date.getDay()];
              const dayNum = date.getDate();
              const monthName = monthNames[date.getMonth()];
              const yearNum = date.getFullYear();
              
              return `${dayName} ${dayNum} ${monthName} ${yearNum}`;
              
          } catch (error) {
              console.error('Error formatting date:', error, 'Input:', dateStr);
              return 'Error en fecha';
          }
      }
      
      // Function to create day card
      function createDayCard(day) {
          const dayCard = document.createElement('div');
          dayCard.className = 'week-day';
          
          // Ensure we're using the correct ID (day_id or id)
          const dayId = day.day_id || day.id;
          dayCard.dataset.dayId = dayId;
          
          // Store the original date string for sorting
          dayCard.setAttribute('data-date', day.day_date);
          
          // Use our formatDateSpanish function for consistent date display
          const formattedDate = formatDateSpanish(day.day_date);
          
          dayCard.innerHTML = `
              <div class="week-day-content">
                  <span class="week-day-text">${formattedDate}</span>
                  <a href="evaluations.php?day_id=${dayId}" class="week-day-link">Ver</a>
              </div>
          `;
          
          return dayCard;
      }

      // Function to add a new day to the list without reloading
      function addNewDay(day) {
          const currentWeekContainer = document.getElementById('current-week');
          const daysContainer = document.getElementById('show-days');
          
          // Create the day card
          const dayCard = createDayCard(day);
          
          // Check if the day is in the current week
          if (isInCurrentWeek(day.day_date)) {
              // Add to current week container and sort
              currentWeekContainer.appendChild(dayCard);
              sortDayElements(currentWeekContainer);
          } else {
              // Add to other days container and sort
              daysContainer.appendChild(dayCard);
              sortDayElements(daysContainer);
          }
      }
      
      // Helper function to sort day elements by date
      function sortDayElements(container) {
          const days = Array.from(container.children);
          
          // Store the date string in a data attribute when creating the element
          days.sort((a, b) => {
              const dateA = a.getAttribute('data-date') || '';
              const dateB = b.getAttribute('data-date') || '';
              // Sort in descending order (newest first)
              return dateB.localeCompare(dateA);
          });
          
          // Re-append elements in sorted order
          days.forEach(day => container.appendChild(day));
      }

      // Function to render days from the database
      function renderCurrentWeek(days) {
          const currentWeekContainer = document.getElementById('current-week');
          const otherDaysContainer = document.getElementById('show-days');
          
          // Clear previous content
          currentWeekContainer.innerHTML = '';
          otherDaysContainer.innerHTML = '';
          
          if (!days || days.length === 0) {
              currentWeekContainer.textContent = 'No hay días registrados.';
              return;
          }
          
          // Separate current week days from other days
          const currentWeekDays = [];
          const otherDays = [];
          
          days.forEach(day => {
              if (isInCurrentWeek(day.day_date)) {
                  currentWeekDays.push(day);
              } else {
                  otherDays.push(day);
              }
          });
          
          // Sort both arrays by date (most recent first)
          const sortByDate = (a, b) => b.day_date.localeCompare(a.day_date);
          currentWeekDays.sort(sortByDate);
          otherDays.sort(sortByDate);
          
          // Render current week days
          if (currentWeekDays.length > 0) {
              currentWeekDays.forEach(day => {
                  const dayCard = createDayCard(day);
                  currentWeekContainer.appendChild(dayCard);
              });
          } else {
              currentWeekContainer.textContent = 'No hay días registrados esta semana.';
          }
          
          // Render other days
          if (otherDays.length > 0) {
              otherDays.forEach(day => {
                  const dayCard = createDayCard(day);
                  otherDaysContainer.appendChild(dayCard);
              });
          }
      }

      // Function to check if a date is in the current week
      function isInCurrentWeek(dateString) {
          try {
              if (!dateString) return false;
              
              // Parse the input date string (YYYY-MM-DD)
              const [year, month, day] = dateString.split('-').map(Number);
              const date = new Date(year, month - 1, day);
              
              // Verify the date is valid
              if (isNaN(date.getTime())) {
                  console.error('Invalid date in isInCurrentWeek:', dateString);
                  return false;
              }
              
              const now = new Date();
              const currentDay = now.getDay();
              
              // Create a clean date object for today (without time)
              const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
              
              // Calculate Monday of current week
              const monday = new Date(today);
              monday.setDate(today.getDate() - (currentDay === 0 ? 6 : currentDay - 1));
              
              // Calculate next Monday (start of next week)
              const nextMonday = new Date(monday);
              nextMonday.setDate(monday.getDate() + 7);
              
              // Create a clean date object for the check date (without time)
              const checkDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
              
              // Check if the date is in the current week (Monday to Sunday)
              return checkDate >= monday && checkDate < nextMonday;
              
          } catch (error) {
              console.error('Error in isInCurrentWeek:', error, 'Input:', dateString);
              return false;
          }
      }

      // Function to get the current week's dates (Monday to Friday)
      function getWeekRange() {
          const now = new Date();
          // Create a date in local timezone without time component
          const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
          const currentDay = today.getDay();
          const monday = new Date(today);
          
          // Set to Monday of current week
          monday.setDate(today.getDate() - (currentDay === 0 ? 6 : currentDay - 1));
          
          const weekDays = [];
          
          // Only add Monday to Friday (5 days)
          for (let i = 0; i < 5; i++) {
              const day = new Date(monday);
              day.setDate(monday.getDate() + i);
              // Ensure we're working with local date only (no time component)
              const localDay = new Date(day.getFullYear(), day.getMonth(), day.getDate());
              weekDays.push(localDay);
          }
          
          return weekDays;
      }

      // Handle form submission
      // Function to check if a date is a weekend
      function isWeekend(dateString) {
          // Create date in local timezone
          const date = new Date(dateString + 'T12:00:00'); // Use noon to avoid timezone issues
          const dayOfWeek = date.getDay();
          return dayOfWeek === 0 || dayOfWeek === 6; // 0 = Sunday, 6 = Saturday
      }

      document.getElementById('dayForm').addEventListener('submit', async function(e) {
          e.preventDefault();
          
          const dateInput = document.getElementById('day_date').value;
          console.log(dateInput);
          // Check if the selected date is a weekend
          if (isWeekend(dateInput)) {
              Swal.fire({
                  title: "Día no válido",
                  text: "No se pueden crear días los fines de semana (sábado o domingo). Por favor selecciona un día entre lunes y viernes.",
                  icon: "warning",
                  position: "top-end",
                  showConfirmButton: false,
                  timer: 4000
              });
              return;
          }
          
          // Format the date as YYYY-MM-DD to ensure no timezone issues
          const [year, month, day] = dateInput.split('-');
          const formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;

          console.log(formattedDate);
          
          const formData = new FormData();
          formData.append('day_date', formattedDate);
        
          try {
              const response = await fetch('api.php?action=create_day', {
                  method: 'POST',
                  body: formData
              });
              
              const result = await response.json();
              
              if (result.day) {
                  const day = result.day;
                  // Add the new day to the list without reloading
                  addNewDay(day);
                  
                  Swal.fire({
                      title: "¡Éxito!",
                      text: "Día creado exitosamente!",
                      icon: "success",
                      position: "top-end",
                      showConfirmButton: false,
                      timer: 1500
                  });
              } else if (response.status === 400 && result.error) {
                  Swal.fire({
                      title: "Error",
                      text: result.error,
                      icon: "warning",
                      position: "top-end",
                      showConfirmButton: false,
                      timer: 3000
                  });
              } else {
                  Swal.fire({
                      title: "Error!",
                      text: result.error || "Error al crear el día",
                      icon: "error",
                      position: "top-end",
                      showConfirmButton: false,
                      timer: 1500
                  });
              }
          } catch (error) {
              console.error('Error:', error);
              alert('Error al conectar con el servidor');
          }
      });

      // Function to format date as YYYY-MM-DD
      function formatDate(dateString) {
          const date = new Date(dateString);
          const year = date.getFullYear();
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const day = String(date.getDate()).padStart(2, '0');
          return `${year}-${month}-${day}`;
      }

      document.addEventListener("DOMContentLoaded", async () => {
          try {
              const res = await fetch("api.php?action=list_days");
              const data = await res.json();

              const currentWeekContainer = document.getElementById("current-week");
              const otherDaysContainer = document.getElementById("show-days");
              
              // Clear containers
              currentWeekContainer.innerHTML = "";
              otherDaysContainer.innerHTML = "";

              // Check if there are any days
              if (!data.days || data.days.length === 0) {
                  otherDaysContainer.textContent = "No hay días registrados.";
                  return;
              }

              // Sort days by date (newest first)
              const sortedDays = [...data.days].sort((a, b) => {
                  return new Date(b.day_date) - new Date(a.day_date);
              });

              // Create containers for current week and other days
              const otherDaysList = document.createElement("div");
              otherDaysList.className = "other-days-list";

              // Separate current week days from other days
              const currentWeekDays = [];
              const otherDays = [];
              
              sortedDays.forEach(day => {
                  if (isInCurrentWeek(day.day_date)) {
                      currentWeekDays.push(day);
                  } else {
                      otherDays.push(day);
                  }
              });

              // Render current week
              renderCurrentWeek(currentWeekDays);

              // Add other days to the list
              otherDays.forEach(day => {
                  const dayCard = createDayCard(day);
                  otherDaysList.appendChild(dayCard);
              });

              // Append other days to the container
              otherDaysContainer.appendChild(otherDaysList);
              
              // If no other days, show message
              if (otherDays.length === 0) {
                  otherDaysContainer.textContent = "No hay otras fechas registradas.";
              }
          } catch (error) {
              console.error('Error loading days:', error);
              const otherDaysContainer = document.getElementById("show-days");
              otherDaysContainer.textContent = "Error al cargar los días. Por favor, recarga la página.";
          }
      });
  </script>

<?php
require_once 'template/footer.php';
?>