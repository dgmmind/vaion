<?php
session_start();
require_once '../includes/auth_check.php';

// Verificar que el usuario tenga rol de manager
requireRole('admin');

// Verificar que el manager_id esté en la sesión
if (!isset($_SESSION["manager_id"])) {
    // Si no hay manager_id, redirigir al login
    header("Location: ../index.php");
    exit();
}

$managerId = $_SESSION["manager_id"];
?>





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VAION | Plataforma de Evaluaciones</title>
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
          <button class="button" data-dropdown="notificationDropdown"><i data-feather="bell"></i></button>
          <div class="dropdown" id="notificationDropdown">
            <div class="dropdown-item"><i data-feather="mail"></i><span>New Message</span></div>
            <div class="dropdown-item"><i data-feather="user-plus"></i><span>New Request</span></div>
            <div class="dropdown-divider"></div>
            <div class="dropdown-item"><i data-feather="settings"></i><span>Notification Settings</span></div>
          </div>
        </div>

        <div class="dropdown-wrapper">
          <button class="button" data-dropdown="messageDropdown"><i data-feather="message-square"></i></button>
          <div class="dropdown" id="messageDropdown">
            <div class="dropdown-item"><i data-feather="send"></i><span>New Message</span></div>
            <div class="dropdown-item"><i data-feather="inbox"></i><span>Inbox</span></div>
            <div class="dropdown-item"><i data-feather="archive"></i><span>Archived</span></div>
          </div>
        </div>

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
        <a href="pauses.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'pauses.php' ? 'active' : ''; ?>" data-tab="pauses"><i data-feather="clock"></i> Pausas</a>
        <a href="create_day.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'create_day.php' ? 'active' : ''; ?>" data-tab="create-day"><i data-feather="calendar"></i> Create Day</a>
        <a href="evaluations.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'evaluations.php' ? 'active' : ''; ?>" data-tab="evaluations"><i data-feather="briefcase"></i> Evaluations</a>
        <a href="metricts.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'metricts.php' ? 'active' : ''; ?>" data-tab="metricts"><i data-feather="bar-chart"></i> Metricts</a>
        <a href="reports.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" data-tab="reports"><i data-feather="mail"></i> Reports</a>
        <a href="test_session.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'test_session.php' ? 'active' : ''; ?>" data-tab="test" style="background: #fef3c7; color: #92400e;"><i data-feather="help-circle"></i> Profile</a>
      </div>
    </div>
  </nav>