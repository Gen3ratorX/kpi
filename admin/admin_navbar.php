<?php
require_once '../misc/admin_login_required.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/bootstrap.min.css">
    <link rel="stylesheet" href="../static/css/base.css">
    <link rel="stylesheet" href="../static/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title><?php echo $pageTitle; ?></title>
    <style>
        /* ===== MODERN NAVBAR STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f0f2f5;
        }

        /* Navbar Container */
        .modern-nav {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 0.8rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 2px solid rgba(108, 99, 255, 0.3);
        }

        .nav-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Brand / Logo */
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .nav-brand:hover {
            transform: scale(1.02);
        }

        .nav-brand img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(108, 99, 255, 0.3));
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
            transition: all 0.3s ease;
        }

        .nav-brand:hover img {
            filter: drop-shadow(0 0 20px rgba(108, 99, 255, 0.5));
            transform: rotate(-5deg);
        }

        .nav-brand h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #a78bfa, #6c63ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-brand .brand-sub {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.5);
            -webkit-text-fill-color: rgba(255, 255, 255, 0.5);
            display: block;
            font-weight: 300;
            letter-spacing: 1px;
        }

        /* Desktop Menu */
        #desktop-menu ul {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        #desktop-menu ul li {
            position: relative;
        }

        #desktop-menu ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
        }

        #desktop-menu ul li a i {
            font-size: 1.1rem;
        }

        #desktop-menu ul li a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        #desktop-menu ul li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #6c63ff, #a78bfa);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        #desktop-menu ul li a:hover::after {
            width: 60%;
        }

        /* Submenu */
        #desktop-menu ul li.submenu {
            position: relative;
        }

        #desktop-menu ul li.submenu > a::after {
            content: '▾';
            margin-left: 0.3rem;
            font-size: 0.7rem;
            transition: transform 0.3s ease;
        }

        #desktop-menu ul li.submenu:hover > a::after {
            transform: rotate(180deg);
        }

        #desktop-menu ul li.submenu ul {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 220px;
            background: rgba(26, 26, 46, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 0.5rem 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            flex-direction: column;
            gap: 0;
        }

        #desktop-menu ul li.submenu:hover ul {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        #desktop-menu ul li.submenu ul li {
            width: 100%;
        }

        #desktop-menu ul li.submenu ul li a {
            padding: 0.6rem 1.5rem;
            border-radius: 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }

        #desktop-menu ul li.submenu ul li a:hover {
            background: rgba(108, 99, 255, 0.1);
            color: #ffffff;
            border-left-color: #6c63ff;
            transform: translateX(5px);
        }

        #desktop-menu ul li.submenu ul li a i {
            width: 20px;
            font-size: 1rem;
        }

        /* Special Buttons */
        #desktop-menu ul li.logout a {
            color: #f87171;
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.2);
        }

        #desktop-menu ul li.logout a:hover {
            background: rgba(248, 113, 113, 0.2);
            color: #fca5a5;
            border-color: rgba(248, 113, 113, 0.3);
        }

        #desktop-menu ul li.create a {
            background: linear-gradient(135deg, #6c63ff, #8b7cf7);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        }

        #desktop-menu ul li.create a:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 99, 255, 0.5);
            background: linear-gradient(135deg, #7b73f5, #9a8cf9);
        }

        #desktop-menu ul li.create a::after {
            display: none;
        }

        /* Hamburger */
        #hamburger {
            display: none;
            flex-direction: column;
            gap: 6px;
            cursor: pointer;
            padding: 8px;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
        }

        #hamburger:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        #hamburger p {
            width: 28px;
            height: 2.5px;
            background: #ffffff;
            margin: 0;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        #hamburger p:nth-child(2) {
            width: 22px;
        }

        #hamburger p:nth-child(3) {
            width: 18px;
        }

        #hamburger.active p:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }

        #hamburger.active p:nth-child(2) {
            opacity: 0;
        }

        #hamburger.active p:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
            width: 28px;
        }

        /* Phone Menu */
        #phone-menu-wrapper {
            display: none;
            position: fixed;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            z-index: 2000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #phone-menu-wrapper.open {
            left: 0;
        }

        #phone-menu {
            width: 320px;
            max-width: 85%;
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            padding: 2rem 1.5rem;
            overflow-y: auto;
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.5);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        #phone-menu a.brand-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        #phone-menu .brand-link img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            padding: 8px;
            margin-bottom: 0.5rem;
        }

        #phone-menu .brand-link h1 {
            font-size: 1.1rem;
            color: #ffffff;
            text-align: center;
            margin: 0;
            font-weight: 600;
            background: linear-gradient(135deg, #a78bfa, #6c63ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        #phone-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #phone-menu ul li {
            margin-bottom: 0.3rem;
        }

        #phone-menu ul li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1.2rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        #phone-menu ul li a i {
            width: 24px;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
        }

        #phone-menu ul li a:hover {
            background: rgba(108, 99, 255, 0.1);
            color: #ffffff;
            transform: translateX(5px);
        }

        #phone-menu ul li a:hover i {
            color: #6c63ff;
        }

        #phone-menu ul li.logout a {
            color: #f87171;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: 1rem;
            padding-top: 1.2rem;
        }

        #phone-menu ul li.logout a i {
            color: #f87171;
        }

        #phone-menu ul li.create a {
            background: linear-gradient(135deg, #6c63ff, #8b7cf7);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
            margin-top: 0.5rem;
        }

        #phone-menu ul li.create a i {
            color: #ffffff;
        }

        #phone-menu ul li.create a:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(108, 99, 255, 0.5);
        }

        #alternate-close {
            flex: 1;
            cursor: pointer;
            min-height: 100vh;
        }

        /* User Profile Badge */
        .user-badge {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .user-badge:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c63ff, #a78bfa);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .user-role {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .nav-brand h1 {
                font-size: 1rem;
            }
            
            #desktop-menu ul li a {
                padding: 0.5rem 0.9rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 900px) {
            .modern-nav {
                padding: 0.6rem 1rem;
            }

            #desktop-menu {
                display: none !important;
            }

            #hamburger {
                display: flex;
            }

            #phone-menu-wrapper {
                display: flex !important;
            }

            .nav-brand h1 {
                font-size: 1rem;
            }

            .nav-brand img {
                width: 40px;
                height: 40px;
            }

            .user-badge {
                padding: 0.2rem 0.8rem;
            }

            .user-name {
                font-size: 0.75rem;
            }

            .user-avatar {
                width: 30px;
                height: 30px;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .modern-nav {
                padding: 0.5rem 0.8rem;
            }

            .nav-brand h1 {
                font-size: 0.85rem;
            }

            .nav-brand img {
                width: 35px;
                height: 35px;
            }

            .nav-brand .brand-sub {
                display: none;
            }

            .user-info {
                display: none;
            }

            #phone-menu {
                width: 280px;
                padding: 1.5rem 1rem;
            }
        }

        /* Scrollbar Styling */
        #phone-menu::-webkit-scrollbar {
            width: 4px;
        }

        #phone-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        #phone-menu::-webkit-scrollbar-thumb {
            background: #6c63ff;
            border-radius: 4px;
        }

        /* Toast Customization */
        .toast-container {
            z-index: 3000;
        }
    </style>
</head>

<body>
    <!-- Information Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast" id="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-info text-white">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong class="me-auto">Information</strong>
                <small>Now</small>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastBody">
                Hello, world! This is a toast message.
            </div>
        </div>
    </div>

    <!-- Modern Navigation -->
    <nav class="modern-nav">
        <div class="nav-content">
            <!-- Left: Hamburger + Brand -->
            <div class="d-flex align-items-center gap-3">
                <!-- Hamburger -->
                <div id="hamburger" role="button" aria-label="Toggle menu">
                    <p></p>
                    <p></p>
                    <p></p>
                </div>

                <!-- Brand -->
                <a href="./index.php" class="nav-brand">
                    <img src="../static/images/logo.png" alt="Logo">
                    <div>
                        <h1>Target Performance Appraisal</h1>
                        <span class="brand-sub">KPI Management System</span>
                    </div>
                </a>
            </div>

            <!-- Center: Desktop Menu -->
            <div id="desktop-menu">
                <ul>
                    <li>
                        <a href="./index.php">
                            <i class="bi bi-house-fill"></i> Home
                        </a>
                    </li>
                    <li class="submenu">
                        <a href="#">
                            <i class="bi bi-grid-fill"></i> Extras
                        </a>
                        <ul>
                            <li>
                                <a href="./employees.php">
                                    <i class="bi bi-people-fill"></i> Employees
                                </a>
                            </li>
                            <li>
                                <a href="./roles.php">
                                    <i class="bi bi-person-badge-fill"></i> Roles
                                </a>
                            </li>
                            <li>
                                <a href="./departments.php">
                                    <i class="bi bi-building-fill"></i> Departments
                                </a>
                            </li>
                            <li>
                                <a href="./units.php">
                                    <i class="bi bi-diagram-3-fill"></i> Units
                                </a>
                            </li>
                            <li>
                                <a href="./risk.php">
                                    <i class="bi bi-graph-down-arrow"></i> Turnover Risk
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="create">
                        <a href="create.php">
                            <i class="bi bi-plus-circle-fill"></i> Create
                        </a>
                    </li>
                    <li class="logout">
                        <a href="../misc/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Right: User Badge -->
            <div class="user-badge">
                <div class="user-avatar">
                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'A'; ?>
                </div>
                <div class="user-info">
                    <span class="user-name">
                        <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?>
                    </span>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
        </div>

        <!-- Phone Menu Overlay -->
        <div id="phone-menu-wrapper">
            <div id="phone-menu">
                <a href="./index.php" class="brand-link">
                    <img src="../static/images/logo.png" alt="Logo">
                    <h1>Target Performance Appraisal</h1>
                </a>
                <ul>
                    <li>
                        <a href="./index.php">
                            <i class="bi bi-house-fill"></i> Home
                        </a>
                    </li>
                    <li>
                        <a href="./employees.php">
                            <i class="bi bi-people-fill"></i> Employees
                        </a>
                    </li>
                    <li>
                        <a href="./roles.php">
                            <i class="bi bi-person-badge-fill"></i> Roles
                        </a>
                    </li>
                    <li>
                        <a href="./departments.php">
                            <i class="bi bi-building-fill"></i> Departments
                        </a>
                    </li>
                    <li>
                        <a href="./units.php">
                            <i class="bi bi-diagram-3-fill"></i> Units
                        </a>
                    </li>
                    <li>
                        <a href="./risk.php">
                            <i class="bi bi-graph-down-arrow"></i> Turnover Risk
                        </a>
                    </li>
                    <li class="create">
                        <a href="create.php">
                            <i class="bi bi-plus-circle-fill"></i> Create
                        </a>
                    </li>
                    <li class="logout">
                        <a href="../misc/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
            <div id="alternate-close"></div>
        </div>
    </nav>

    <!-- ====== JAVASCRIPT ====== -->
    <script src="../static/js/jquery-3.6.0.min.js"></script>
    <script src="../static/js/bootstrap.bundle.min.js"></script>
    <script src="../static/js/base.js"></script>
    <script>
        $(document).ready(function() {
            // ===== HAMBURGER TOGGLE =====
            const $hamburger = $('#hamburger');
            const $phoneMenu = $('#phone-menu-wrapper');
            const $alternateClose = $('#alternate-close');

            // Open menu
            $hamburger.on('click', function(e) {
                e.stopPropagation();
                $(this).toggleClass('active');
                $phoneMenu.toggleClass('open');
                $('body').css('overflow', $phoneMenu.hasClass('open') ? 'hidden' : 'auto');
            });

            // Close menu - alternate close (overlay)
            $alternateClose.on('click', function() {
                $hamburger.removeClass('active');
                $phoneMenu.removeClass('open');
                $('body').css('overflow', 'auto');
            });

            // Close menu - escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $phoneMenu.hasClass('open')) {
                    $hamburger.removeClass('active');
                    $phoneMenu.removeClass('open');
                    $('body').css('overflow', 'auto');
                }
            });

            // Close menu - window resize (if going to desktop)
            $(window).on('resize', function() {
                if (window.innerWidth > 900 && $phoneMenu.hasClass('open')) {
                    $hamburger.removeClass('active');
                    $phoneMenu.removeClass('open');
                    $('body').css('overflow', 'auto');
                }
            });

            // ===== SUBMENU FIX FOR TOUCH DEVICES =====
            // On touch devices, click to toggle submenu instead of hover
            if ('ontouchstart' in window) {
                $('.submenu > a').on('click', function(e) {
                    e.preventDefault();
                    const $parent = $(this).parent('.submenu');
                    const $submenu = $parent.find('ul');
                    
                    // Close other submenus
                    $('.submenu').not($parent).find('ul').css({
                        'opacity': '0',
                        'visibility': 'hidden',
                        'transform': 'translateY(10px)'
                    });
                    
                    // Toggle this submenu
                    if ($submenu.css('visibility') === 'visible') {
                        $submenu.css({
                            'opacity': '0',
                            'visibility': 'hidden',
                            'transform': 'translateY(10px)'
                        });
                    } else {
                        $submenu.css({
                            'opacity': '1',
                            'visibility': 'visible',
                            'transform': 'translateY(0)'
                        });
                    }
                });
            }

            console.log('✅ Modern Navigation loaded successfully!');
        });
    </script>
</body>
</html>