<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegant Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Refined color palette - more elegant and subtle */
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-tertiary: #242424;
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;
            --accent-primary: #a855f7;
            --accent-secondary: #8b5cf6;
            --border-color: #27272a;
            --subtle-glow: rgba(168, 85, 247, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            /* Removed animated background, keeping it clean */
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Simplified sidebar with elegant styling */
        .sidebar {
            width: 280px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            transform: translateX(0);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 32px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo {
            width: 36px;
            height: 36px;
            background: var(--accent-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            color: white;
            /* Removed excessive glow effects */
        }

        .brand-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            /* Simplified typography without gradients */
        }

        .search-container {
            padding: 0 24px 24px;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px 12px 40px;
            color: var(--text-primary);
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: var(--accent-primary);
            /* Subtle focus state without glow */
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
        }

        .nav-menu {
            flex: 1;
            padding: 8px 0;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            font-weight: 400;
            font-size: 14px;
            margin: 2px 12px;
            border-radius: 6px;
        }

        .nav-item:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: var(--accent-primary);
            color: white;
        }

        .nav-section {
            margin-top: 32px;
        }

        .nav-section-title {
            padding: 0 24px 12px;
            font-size: 11px;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-footer {
            padding: 24px;
            border-top: 1px solid var(--border-color);
        }

        /* Simplified upgrade card */
        .upgrade-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .upgrade-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .upgrade-desc {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .upgrade-btn {
            width: 100%;
            background: var(--accent-primary);
            border: none;
            border-radius: 8px;
            padding: 10px;
            color: white;
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upgrade-btn:hover {
            background: var(--accent-secondary);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--bg-tertiary);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-profile:hover {
            background: #2a2a2a;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--accent-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            font-size: 14px;
            color: white;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .user-email {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Simplified main content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .main-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-primary);
            /* Clean typography without gradients */
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .menu-toggle {
            display: none;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 16px;
            cursor: pointer;
            padding: 8px;
            transition: all 0.2s ease;
        }

        .menu-toggle:hover {
            background: #2a2a2a;
        }

        .view-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
        }

        .view-dropdown {
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            font-size: 13px;
        }

        .content-area {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }

        /* Simplified navigation tabs */
        .nav-tabs {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
            border-bottom: 1px solid var(--border-color);
        }

        .nav-tab {
            padding: 12px 0;
            color: var(--text-secondary);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
            font-weight: 400;
            font-size: 14px;
        }

        .nav-tab:hover {
            color: var(--text-primary);
        }

        .nav-tab.active {
            color: var(--accent-primary);
            border-bottom-color: var(--accent-primary);
        }

        /* Clean content placeholder */
        .content-placeholder {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 64px 48px;
            text-align: center;
            color: var(--text-secondary);
        }

        .placeholder-icon {
            font-size: 48px;
            margin-bottom: 24px;
            color: var(--accent-primary);
        }

        .content-placeholder h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .content-placeholder p {
            font-size: 14px;
            line-height: 1.5;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.visible {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .main-header {
                padding: 20px;
            }

            .page-title {
                font-size: 24px;
            }

            .content-area {
                padding: 24px;
            }

            .nav-tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 8px;
                gap: 20px;
            }

            .nav-tab {
                flex-shrink: 0;
            }

            .content-placeholder {
                padding: 48px 24px;
            }
        }

        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.visible {
            display: block;
        }

        /* Simplified scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">A</div>
                <span class="brand-name">ACESPACE</span>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" placeholder="Search...">
                </div>
            </div>

            <nav class="nav-menu">
                <a href="#" class="nav-item">
                    <span class="icon">🏠</span>
                    <span>Home</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">📊</span>
                    <span>Analytics</span>
                </a>
                <a href="#" class="nav-item active">
                    <span class="icon">📋</span>
                    <span>Projects</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">⚙️</span>
                    <span>Settings</span>
                </a>

                <div class="nav-section">
                    <div class="nav-section-title">Workspace</div>
                    <a href="#" class="nav-item">
                        <span class="icon">📚</span>
                        <span>Documentation</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="icon">👥</span>
                        <span>Team</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="icon">💬</span>
                        <span>Messages</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="icon">❓</span>
                        <span>Support</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="upgrade-card">
                    <div class="upgrade-title">Upgrade to Pro</div>
                    <div class="upgrade-desc">Get access to advanced features and unlimited projects.</div>
                    <button class="upgrade-btn">Upgrade Now</button>
                </div>

                <div class="user-profile">
                    <div class="user-avatar">JD</div>
                    <div class="user-info">
                        <div class="user-name">John Doe</div>
                        <div class="user-email">john@acespace.com</div>
                    </div>
                    <span style="color: var(--text-muted);">⌄</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <header class="main-header">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 class="page-title">Projects</h1>
                </div>
                <div class="header-actions">
                    <div class="view-selector">
                        <span style="color: var(--text-secondary);">View:</span>
                        <select class="view-dropdown">
                            <option>Grid</option>
                            <option>List</option>
                            <option>Timeline</option>
                        </select>
                        <span style="color: var(--text-muted);">⌄</span>
                    </div>
                    <button style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 6px; padding: 8px 12px; color: var(--text-primary); cursor: pointer; transition: all 0.2s ease; font-size: 13px;">
                        + New Project
                    </button>
                </div>
            </header>

            <div class="content-area">
                <nav class="nav-tabs">
                    <a href="#" class="nav-tab active">All Projects</a>
                    <a href="#" class="nav-tab">Active</a>
                    <a href="#" class="nav-tab">In Progress</a>
                    <a href="#" class="nav-tab">Completed</a>
                    <a href="#" class="nav-tab">Archived</a>
                </nav>

                <div class="content-placeholder">
                    <div class="placeholder-icon">📁</div>
                    <h3>Your Projects</h3>
                    <p>This is your elegant dashboard base. Clean, minimal, and ready for your content. Add your projects, data, and functionality to this refined interface.</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('visible');
            overlay.classList.toggle('visible');
        }

        function closeSidebar() {
            sidebar.classList.remove('visible');
            overlay.classList.remove('visible');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', closeSidebar);

        // Navigation items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Tab navigation
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });

        // Responsive behavior
        function handleResize() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        }

        window.addEventListener('resize', handleResize);
    </script>
</body>
</html>
