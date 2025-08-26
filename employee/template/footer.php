
<div id="reasonModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
      <div style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 400px;">
          <h3>Motivo de la pausa</h3>
          <textarea id="pauseReason" rows="4" style="width: 100%; margin: 10px 0; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
          <div style="display: flex; justify-content: flex-end; gap: 10px;">
              <button id="cancelPause" style="padding: 8px 16px; background: #e2e8f0; border: none; border-radius: 4px; cursor: pointer;">Cancelar</button>
              <button id="confirmPause" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Confirmar</button>
          </div>
      </div>
  </div>
  <script src="../assets/js/main.js"></script>
  <script>
    feather.replace();
  </script>

  
</body>
</html>
