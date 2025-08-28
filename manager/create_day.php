<?php
require_once 'template/header.php';
?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <div class="header-card">
        <h1 class="title">Registrar Day</h1>
        <p class="description">Crea un nuevo día para evaluaciones</p>
      </div>
      
      <form id="dayForm" style="margin-top: 20px;">
          <div style="margin-bottom: 15px;">
              <label style="display: block; margin-bottom: 5px; font-weight: 500;">Day Date:</label>
              <input type="date" id="day_date" name="day_date" required style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; width: 200px;">
          </div>

          <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Guardar</button>
      </form>

      <script>
      document.getElementById('dayForm').addEventListener('submit', async function(e) {
          e.preventDefault();
          
          const formData = new FormData();
          formData.append('day_date', document.getElementById('day_date').value);
          
          try {
              const response = await fetch('api.php?action=create_day', {
                  method: 'POST',
                  body: formData
              });
              
              const result = await response.json();
              
              if (result.day) {
                  const day = result.day;
                  Swal.fire({
                      title: "Exitoso!",
                      text: "Día creado exitosamente!",
                      icon: "success",
                      position: "top-end",
                      showConfirmButton: false,
                      timer: 1500
                  });
                  setTimeout(() => {
                      location.reload();
                  }, 1600);
              } else {
                  Swal.fire({
                      title: "Error!",
                      text: "Error al crear el día",
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
      </script>

      <hr style="margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;">

      <h2 style="margin-bottom: 15px;">Ver días registrados</h2>
      <div id="resultado"></div>
    </div>
  </main>

  <script>
    document.addEventListener("DOMContentLoaded", async () => {
        const res = await fetch("api.php?action=list_days");
        const data = await res.json();

        const contenedor = document.getElementById("resultado");
        contenedor.innerHTML = "";

        // verificamos que existan días
        if (!data.days || data.days.length === 0) {
            contenedor.textContent = "No hay días registrados.";
            return;
        }

        // mostramos el manager
        const titulo = document.createElement("h3");
        titulo.textContent = "Manager: " + data.manager_id;
        contenedor.appendChild(titulo);

        // listamos días
        data.days.forEach(day => {
            const link = document.createElement("a");
            link.href = "evaluations.php?day_id=" + day.day_id; 
            link.textContent = "Día " + day.day_id + " - " + day.day_date;
            link.style.cssText = "color: #3b82f6; text-decoration: none; display: block; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; margin: 5px 0; transition: all 0.2s;";
            link.onmouseover = function() { this.style.background = '#f8fafc'; };
            link.onmouseout = function() { this.style.background = 'transparent'; };

            const div = document.createElement("div");
            div.appendChild(link);
            contenedor.appendChild(div);
        });
    });
  </script>

<?php
require_once 'template/footer.php';
?>