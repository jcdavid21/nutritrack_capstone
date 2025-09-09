<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #81C784;
            --accent-color: #ffffffff;
            --text-color: #1B5E20;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
        }

        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Teachers:ital,wght@0,400..800;1,400..800&family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            margin-top: 80px;
            transition: margin-left 0.3s ease;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        image {
            height: 100%;
            width: 100%;
        }

        /* Mobile Header with Burger Menu */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--primary-color);
            color: white;
            z-index: 1002;
            padding: 0 20px;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .burger-menu {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .burger-menu:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .burger-menu i {
            transition: transform 0.3s ease;
        }

        .burger-menu.active i {
            transform: rotate(90deg);
        }

        .mobile-title {
            margin-left: 15px;
            font-size: 18px;
            font-weight: 600;
        }

        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            height: 100%;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, #486722ff 100%);
            color: white;
            overflow-y: auto;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .admin-sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
        }

        .logo-con {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-logo {
            margin-bottom: 10px;
            background-color: white;
            border-radius: 100%;
            padding: 10px;
            width: 100px;
            height: 100px;
        }

        .sidebar-logo img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }

        .sidebar-logo i {
            font-size: 32px;
            color: var(--accent-color);
        }

        .sidebar-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: white;
            transition: opacity 0.3s ease;
        }

        .sidebar-subtitle {
            font-size: 12px;
            color: var(--accent-color);
            margin: 5px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: opacity 0.3s ease;
        }

        .collapsed .sidebar-title,
        .collapsed .sidebar-subtitle {
            opacity: 0;
            visibility: hidden;
        }

        .sidebar-toggle {
            position: absolute;
            top: 10px;
            right: 2px;
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            border: 2px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 1001;
        }

        .sidebar-toggle:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-section {
            margin-bottom: 30px;
        }

        .nav-section-title {
            padding: 0 20px 10px 20px;
            font-size: 11px;
            color: var(--accent-color);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
            transition: opacity 0.3s ease;
        }

        .collapsed .nav-section-title {
            opacity: 0;
            visibility: hidden;
        }

        .nav-item {
            position: relative;
            margin-bottom: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(10px);
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            transform: translateX(0);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
            flex-shrink: 0;
        }

        .nav-text {
            font-weight: 500;
            font-size: 14px;
            transition: opacity 0.3s ease;
        }

        .collapsed .nav-text {
            opacity: 0;
            visibility: hidden;
        }

        .nav-badge {
            margin-left: auto;
            background: #dc3545;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
            transition: opacity 0.3s ease;
        }

        .collapsed .nav-badge {
            opacity: 0;
            visibility: hidden;
        }

        .sidebar-footer {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--primary-color);
            flex-shrink: 0;
        }

        .admin-info {
            flex: 1;
            transition: opacity 0.3s ease;
        }

        .admin-name {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            color: white;
        }

        .admin-role {
            font-size: 11px;
            color: var(--accent-color);
            margin: 2px 0 0 0;
        }

        .collapsed .admin-info {
            opacity: 0;
            visibility: hidden;
        }

        .logout-btn {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Tooltip for collapsed state */
        .collapsed .nav-item {
            position: relative;
        }

        .collapsed .nav-link {
            justify-content: center;
            padding: 15px 10px;
        }

        .collapsed .nav-link:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 10px;
            padding: 8px 12px;
            background: var(--primary-color);
            color: white;
            font-size: 12px;
            border-radius: 6px;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .collapsed .nav-link:hover::before {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 5px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 5px 5px 5px 0;
            border-color: transparent var(--primary-color) transparent transparent;
        }

        /* Mobile overlay when sidebar is expanded */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                margin-top: var(--header-height);
                margin-left: 0 !important;
            }

            .mobile-header {
                display: flex;
            }

            .admin-sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
                top: var(--header-height);
                height: calc(100% - var(--header-height));
                z-index: 1001;
            }

            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }

            /* Force full sidebar content on mobile */
            .admin-sidebar .sidebar-title,
            .admin-sidebar .sidebar-subtitle,
            .admin-sidebar .nav-section-title,
            .admin-sidebar .nav-text,
            .admin-sidebar .nav-badge,
            .admin-sidebar .admin-info {
                opacity: 1 !important;
                visibility: visible !important;
            }

            .admin-sidebar .nav-link {
                padding: 15px 20px !important;
                justify-content: flex-start !important;
            }

            .sidebar-toggle {
                display: none;
            }

            .sidebar-header {
                padding-top: 10px;
            }
        }

        /* Desktop behavior */
        @media (min-width: 769px) {
            .mobile-header {
                display: none;
            }

            .admin-sidebar {
                position: fixed;
                top: 0;
                height: 100%;
            }

            .admin-sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
            }

            .sidebar-toggle {
                display: flex;
            }
        }

        /* Animation for smooth transitions */
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
            }

            to {
                transform: translateX(0);
            }
        }

        .admin-sidebar {
            animation: slideIn 0.5s ease-out;
        }
    </style>
</head>

<body>
    <!-- Mobile Header with Burger Menu -->
    <header class="mobile-header">
        <button class="burger-menu" onclick="toggleMobileSidebar()" id="burgerMenu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mobile-title">Admin Panel</div>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

    <aside class="admin-sidebar collapsed" id="adminSidebar">
        <div class="sidebar-header">
            <div class="logo-con">
                <div class="sidebar-logo">
                    <img src="../assets/logo.png" alt="logo">
                </div>
            </div>
            <p class="sidebar-subtitle">Admin Panel</p>
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-chevron-right" id="toggleIcon"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item">
                    <a href="./dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" data-tooltip="Dashboard">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Bulletin Management</div>
                <div class="nav-item">
                    <a href="./announcements.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'announcements.php') ? 'active' : ''; ?>" data-tooltip="Announcements">
                        <div class="nav-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <span class="nav-text">Announcements</span>
                        <span class="nav-badge">
                            <?php 
                                $anc_count = "SELECT COUNT(*) as count FROM tbl_announcements";
                                $result_anc_count = mysqli_query($conn, $anc_count);
                                $count_anc = 0;
                                if ($result_anc_count && $result_anc_count->num_rows > 0) {
                                    $data_anc_count = $result_anc_count->fetch_assoc();
                                    $count_anc = $data_anc_count['count'];
                                }
                                echo $count_anc;
                            ?>
                        </span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="./event_scheduler.php" class="nav-link  <?php echo (basename($_SERVER['PHP_SELF']) == 'event_scheduler.php') ? 'active' : ''; ?>" data-tooltip="Event Scheduler">
                        <div class="nav-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <span class="nav-text">Event Scheduler</span>
                        <span class="nav-badge">
                            <?php 
                                $event_count = "SELECT COUNT(*) as count FROM tbl_events WHERE event_date >= CURDATE()";
                                $result_event_count = mysqli_query($conn, $event_count);
                                $count_event = 0;
                                if ($result_event_count && $result_event_count->num_rows > 0) {
                                    $data_event_count = $result_event_count->fetch_assoc();
                                    $count_event = $data_event_count['count'];
                                }
                                echo $count_event;
                            ?>
                        </span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="./modules.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'modules.php') ? 'active' : ''; ?>" data-tooltip="Educational Resources">
                        <div class="nav-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="nav-text">Educational Resources</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Profile & Child Data</div>
                <div class="nav-item">
                    <a href="./user_management.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'user_management.php') ? 'active' : ''; ?>" data-tooltip="User Management">
                        <div class="nav-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="nav-text">User Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="./child_records.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'child_records.php') ? 'active' : ''; ?>" data-tooltip="Manage Child Records">
                        <div class="nav-icon">
                            <i class="fas fa-child"></i>
                        </div>
                        <span class="nav-text">Manage Child Records</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="./vaccine_records.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'vaccine_records.php') ? 'active' : ''; ?>" data-tooltip="Manage Vaccine Records">
                        <div class="nav-icon">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <span class="nav-text">Vaccine Records</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Reports Management</div>
                <div class="nav-item">
                    <a href="./flagged_records.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'flagged_records.php') ? 'active' : ''; ?>" data-tooltip="Flagged Records">
                        <div class="nav-icon">
                            <i class="fas fa-flag"></i>
                        </div>
                        <span class="nav-text">Flagged Records</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="./reports.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>" data-tooltip="Reports">
                        <div class="nav-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="nav-text">Reports</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Activity History</div>
                <div class="nav-item">
                    <a href="./audit_trail.php" class="nav-link" data-tooltip="Audit Trail">
                        <div class="nav-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <span class="nav-text">Audit Trail</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-avatar">A</div>
                <div class="admin-info">
                    <p class="admin-name">Admin User</p>
                    <p class="admin-role">Administrator</p>
                </div>
                <button class="logout-btn" onclick="logout()" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- Your page content goes here -->

    <script>
        // Mobile sidebar functions
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const burgerMenu = document.getElementById('burgerMenu');

            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            burgerMenu.classList.toggle('active');

            // Prevent body scroll when sidebar is open
            if (sidebar.classList.contains('mobile-open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const burgerMenu = document.getElementById('burgerMenu');

            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            burgerMenu.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Desktop sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const toggleIcon = document.getElementById('toggleIcon');

            sidebar.classList.toggle('collapsed');

            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.className = 'fas fa-chevron-right';
                document.body.style.marginLeft = '80px';
            } else {
                toggleIcon.className = 'fas fa-chevron-left';
                document.body.style.marginLeft = '280px';
            }

            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = "../components/logout.php"
            }
        }



        // Handle responsive behavior
        function handleResize() {
            const sidebar = document.getElementById('adminSidebar');
            const toggleIcon = document.getElementById('toggleIcon');
            const overlay = document.getElementById('sidebarOverlay');

            if (window.innerWidth <= 768) {
                // Mobile view - reset everything and ensure full content
                sidebar.classList.remove('mobile-open');
                sidebar.classList.remove('collapsed'); // Remove collapsed class on mobile
                overlay.classList.remove('active');
                document.body.style.overflow = '';
                document.body.style.marginLeft = '0';
            } else {
                // Desktop view
                overlay.classList.remove('active');
                document.body.style.overflow = '';

                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    toggleIcon.className = 'fas fa-chevron-right';
                    document.body.style.marginLeft = '80px';
                } else {
                    sidebar.classList.remove('collapsed');
                    toggleIcon.className = 'fas fa-chevron-left';
                    document.body.style.marginLeft = '280px';
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            handleResize();
        });

        // Handle window resize
        window.addEventListener('resize', handleResize);

        // Close mobile sidebar when clicking on nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeMobileSidebar();
                }
            });
        });

        // Handle escape key to close mobile sidebar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && window.innerWidth <= 768) {
                closeMobileSidebar();
            }
        });
    </script>
</body>

</html>