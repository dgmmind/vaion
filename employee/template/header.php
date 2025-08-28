<?php
session_start();
require_once '../includes/auth_check.php';

// Verificar que el usuario tenga rol de manager
requireRole('user');

// Verificar que el manager_id esté en la sesión
if (!isset($_SESSION["user_id"])) {
    // Si no hay manager_id, redirigir al login
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION["user_id"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Minimal Modern Header</title>
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
      <button class="mobile-menu"><i data-feather="menu"></i></button>
    </div>
  </header>



  <!-- Navigation Tabs -->
  <nav class="nav-container">
    <div class="container">
      <div class="tabs">
        <a href="dashboard.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" data-tab="dashboard"><i data-feather="sidebar"></i> Dashboard</a>
        <a href="evaluations.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'evaluations.php' ? 'active' : ''; ?>" data-tab="evaluations"><i data-feather="briefcase"></i> Evaluations</a>
      </div>
    </div>
  </nav>

<style>
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  }
  70% {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
  }
}

/* Responsive para indicadores */
@media (max-width: 640px) {
  .status-indicators {
    gap: 12px;
  }
  
  .status-indicator {
    padding: 4px 8px;
  }
  
  .status-indicator span {
    font-size: 10px;
  }
}
</style>