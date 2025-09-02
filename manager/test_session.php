<?php
require_once 'template/header.php';
?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <div class="card">
      <p class="description">Datos Manager</p>
      
      <div style="margin: 20px 0; padding: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3>Información de Sesión:</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin: 10px 0; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e2e8f0;">
            <strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'No definido'); ?>
          </li>
          <li style="margin: 10px 0; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e2e8f0;">
            <strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? 'No definido'); ?>
          </li>
          <li style="margin: 10px 0; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e2e8f0;">
            <strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['name'] ?? 'No definido'); ?>
          </li>
          <li style="margin: 10px 0; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e2e8f0;">
            <strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['role'] ?? 'No definido'); ?>
          </li>
          <li style="margin: 10px 0; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e2e8f0;">
            <strong>Manager ID:</strong> <?php echo htmlspecialchars($_SESSION['manager_id'] ?? 'No definido'); ?>
          </li>
          <li style="margin: 10px 0; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e2e8f0;">
            <strong>Variable $managerId:</strong> <?php echo htmlspecialchars($managerId ?? 'No definida'); ?>
          </li>
        </ul>
      </div>
      
      <div style="margin: 20px 0;">
        <h3>Navegación:</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="dashboard.php" style="padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px;">Dashboard</a>
          <a href="create_day.php" style="padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px;">Create Day</a>
          <a href="evaluations.php" style="padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px;">Evaluations</a>
          <a href="reports.php" style="padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px;">Reports</a>
        </div>
      </div>
      </div>
    </div>
  </main>

<?php
require_once 'template/footer.php';
?>
