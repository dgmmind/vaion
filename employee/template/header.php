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
  <script src="sweetalert2.min.js"></script>
  <link rel="stylesheet" href="sweetalert2.min.css">
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

  <div class="section-title container">
    <div class="section-title-header">
      <h2><?php 
        $currentPage = basename($_SERVER['PHP_SELF']);
        switch($currentPage) {
          case 'dashboard.php':
            echo 'Dashboard';
            break;
          case 'evaluations.php':
            echo 'Evaluations';
            break;
          default:
            echo 'Dashboard';
        }
      ?></h2>
    </div>
    <div class="status-container">
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          <div class="status-pulse"></div>
        </div>
        <span>LIVE</span>
      </div>
      
      <!-- User Status -->
      <div class="status-indicator status-user">
        <div class="status-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          <div class="status-pulse"></div>
        </div>
        <span>ON</span>
      </div>
        
      </div>
    </div>
  </div>

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