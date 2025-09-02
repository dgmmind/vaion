<?php
session_start();
require_once '../includes/auth_check.php';

// Verificar que el usuario tenga rol de superadmin
requireRole('superadmin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VAION | Super Admin</title>
  <link rel="stylesheet" href="../assets/css/main.css">
  <script src="https://unpkg.com/feather-icons"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="container navbar">
      <div class="logo">VAION</div>
      <div class="actions" id="actions">
        <div class="dropdown-wrapper">
          <button class="button" data-dropdown="userDropdown"><i data-feather="user"></i></button>
          <div class="dropdown" id="userDropdown">
            <div class="dropdown-item"><i data-feather="user"></i><span>Profile</span></div>
            <div class="dropdown-item"><i data-feather="settings"></i><span>Settings</span></div>
            <div class="dropdown-divider"></div>
            <a href="../settings/logout.php" class="dropdown-item danger logout-btn" id="logout"><i data-feather="log-out"></i><span>Cerrar Sesión</span></a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Navigation Tabs -->
  <nav class="nav-container">
    <div class="container">
      <div class="tabs">
        <a href="dashboard.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" data-tab="dashboard"><i data-feather="sidebar"></i> Dashboard</a>
        <a href="monitoring.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'monitoring.php' ? 'active' : ''; ?>" data-tab="monitoring"><i data-feather="monitor"></i> Monitoring</a>
      </div>
    </div>
  </nav>
