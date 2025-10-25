<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Sitokcer')</title>

    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* ===== PREMIUM GLOBAL STYLES ===== */
        :root {
            --sidebar-width: 280px;
            --header-height: 64px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.18);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.1);
            --transition-smooth: cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            overflow: hidden;
            height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== PAGE WRAPPER ===== */
        .page-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
        }

        /* ===== PREMIUM HEADER ===== */
        .header-navbar {
            height: var(--header-height);
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            position: relative;
            z-index: 1030;
            transition: all 0.3s var(--transition-smooth);
        }

        .header-navbar .container-fluid {
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
        }

        /* Sidebar Toggle Button */
        #sidebar-toggle-button {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: #64748b;
            transition: all 0.3s var(--transition-smooth);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        #sidebar-toggle-button::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s var(--transition-smooth);
            border-radius: 12px;
        }

        #sidebar-toggle-button:hover {
            transform: scale(1.05);
            color: #fff;
        }

        #sidebar-toggle-button:hover::before {
            opacity: 1;
        }

        #sidebar-toggle-button i {
            position: relative;
            z-index: 1;
            font-size: 1.75rem;
            transition: transform 0.3s var(--transition-smooth);
        }

        #sidebar-toggle-button:active i {
            transform: scale(0.9);
        }

        /* Logo */
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            margin-left: 1rem;
            transition: transform 0.3s var(--transition-smooth);
        }

        .navbar-brand:hover {
            transform: translateX(4px);
        }

        .navbar-brand img {
            height: 38px;
            width: 38px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.1));
        }

        .navbar-brand span {
            font-size: 1.35rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Header Title (Center) */
        .header-title {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            letter-spacing: -0.02em;
        }

        /* User Menu */
        .user-menu {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            border-radius: 12px;
            background: transparent;
            border: 1px solid transparent;
            color: #475569;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s var(--transition-smooth);
            cursor: pointer;
        }

        .user-dropdown-toggle:hover {
            background: rgba(103, 126, 234, 0.08);
            border-color: rgba(103, 126, 234, 0.2);
            color: #667eea;
        }

        .user-dropdown-toggle i {
            font-size: 1.5rem;
        }

        /* ===== CONTENT BODY ===== */
        .content-body-wrapper {
            display: flex;
            flex-grow: 1;
            overflow: hidden;
            position: relative;
            height: calc(100vh - var(--header-height));
        }

        /* ===== PREMIUM SIDEBAR ===== */
        #sidebar-wrapper {
            width: var(--sidebar-width);
            height: 100%;
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-right: 1px solid var(--glass-border);
            box-shadow: var(--shadow-lg);
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.4s var(--transition-smooth);
            position: relative;
            z-index: 1020;
        }

        #sidebar-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(103, 126, 234, 0.3);
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(103, 126, 234, 0.5);
        }

        /* Sidebar Hidden State (Desktop) */
        .content-body-wrapper.sidebar-hidden #sidebar-wrapper {
            transform: translateX(-100%);
        }

        /* ===== MAIN CONTENT ===== */
        #content-wrapper {
            flex: 1;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 2rem;
            background: transparent;
            transition: margin-left 0.4s var(--transition-smooth);
            scroll-behavior: smooth;
        }

        #content-wrapper::-webkit-scrollbar {
            width: 8px;
        }

        #content-wrapper::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.02);
        }

        #content-wrapper::-webkit-scrollbar-thumb {
            background: rgba(103, 126, 234, 0.3);
            border-radius: 10px;
        }

        #content-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(103, 126, 234, 0.5);
        }

        /* ===== SIDEBAR OVERLAY (Mobile) ===== */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1010;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s var(--transition-smooth),
                visibility 0.3s var(--transition-smooth);
        }

        .content-body-wrapper.sidebar-open .sidebar-overlay {
            opacity: 1;
            visibility: visible;
        }

        /* ===== RESPONSIVE MOBILE ===== */
        @media (max-width: 991.98px) {
            .header-navbar .container-fluid {
                padding: 0 1rem;
            }

            .navbar-brand span {
                font-size: 1.2rem;
            }

            .header-title {
                font-size: 0.95rem;
                max-width: 200px;
                text-align: center;
            }

            .user-dropdown-toggle span {
                display: none;
            }

            #sidebar-wrapper {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                transform: translateX(-100%);
                z-index: 1020;
            }

            .content-body-wrapper.sidebar-open #sidebar-wrapper {
                transform: translateX(0);
            }

            #content-wrapper {
                padding: 1.25rem;
            }
        }

        @media (max-width: 575.98px) {
            .navbar-brand span {
                display: none;
            }

            .header-title {
                font-size: 0.9rem;
                left: 60%;
            }

            #content-wrapper {
                padding: 1rem;
            }
        }

        /* ===== LOADING ANIMATION ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #content-wrapper>* {
            animation: fadeInUp 0.5s var(--transition-smooth);
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="page-wrapper">
        <!-- ===== PREMIUM HEADER ===== -->
        <header class="navbar navbar-expand navbar-light header-navbar">
            <div class="container-fluid">
                <!-- Left: Toggle + Logo -->
                <div class="d-flex align-items-center">
                    <button class="btn" type="button" id="sidebar-toggle-button" aria-label="Toggle Sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <a href="{{ route('home') }}" class="navbar-brand">
                        <img src="{{ asset('logo.png') }}" alt="Logo Sitokcer">
                        <span>Sitokcer</span>
                    </a>
                </div>

                <!-- Center: Page Title -->
                <div class="header-title">
                    @yield('header-title', 'Dashboard')
                </div>

                <!-- Right: User Menu -->
                <div class="user-menu">
                    <div class="dropdown">
                        <button class="user-dropdown-toggle dropdown-toggle" type="button" id="userDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span>{{ Auth::user()->name ?? 'User' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2"
                            aria-labelledby="userDropdown" style="min-width: 200px;">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#">
                                    <i class="bi bi-person"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#">
                                    <i class="bi bi-gear"></i> Settings
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                {{-- <form action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                                    @csrf
                                    <button type="submit"
                                        class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form> --}}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- ===== CONTENT BODY ===== -->
        <div class="content-body-wrapper">
            <!-- Sidebar -->
            <aside id="sidebar-wrapper">
                @include('sidebar')
            </aside>

            <!-- Overlay (Mobile) -->
            <div class="sidebar-overlay" id="sidebar-overlay"></div>

            <!-- Main Content -->
            <main id="content-wrapper">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Premium Sidebar Script with Swipe Gesture -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('sidebar-toggle-button');
            const wrapper = document.querySelector('.content-body-wrapper');
            const overlay = document.getElementById('sidebar-overlay');
            const sidebar = document.getElementById('sidebar-wrapper');

            // Toggle Sidebar Function
            const toggleSidebar = () => {
                const isMobile = window.innerWidth <= 991.98;

                if (isMobile) {
                    wrapper.classList.toggle('sidebar-open');
                } else {
                    wrapper.classList.toggle('sidebar-hidden');
                }
            };

            // Click Events
            if (toggleButton) toggleButton.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);

            // ===== SWIPE GESTURE SUPPORT (Mobile) ===== 
            let touchStartX = 0;
            let touchEndX = 0;
            let touchStartY = 0;
            let touchEndY = 0;

            const handleSwipe = () => {
                const isMobile = window.innerWidth <= 991.98;
                if (!isMobile) return;

                const swipeDistanceX = touchEndX - touchStartX;
                const swipeDistanceY = Math.abs(touchEndY - touchStartY);

                // Horizontal swipe (not vertical scroll)
                if (Math.abs(swipeDistanceX) > 50 && swipeDistanceY < 50) {
                    // Swipe Right: Open Sidebar
                    if (swipeDistanceX > 0 && touchStartX < 50 && !wrapper.classList.contains('sidebar-open')) {
                        wrapper.classList.add('sidebar-open');
                    }
                    // Swipe Left: Close Sidebar
                    else if (swipeDistanceX < 0 && wrapper.classList.contains('sidebar-open')) {
                        wrapper.classList.remove('sidebar-open');
                    }
                }
            };

            document.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                touchStartY = e.changedTouches[0].screenY;
            }, {
                passive: true
            });

            document.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                touchEndY = e.changedTouches[0].screenY;
                handleSwipe();
            }, {
                passive: true
            });

            // Close sidebar on window resize (desktop)
            window.addEventListener('resize', () => {
                if (window.innerWidth > 991.98) {
                    wrapper.classList.remove('sidebar-open');
                }
            });

            // Prevent body scroll when sidebar open (mobile)
            const body = document.body;
            const observer = new MutationObserver(() => {
                if (wrapper.classList.contains('sidebar-open')) {
                    body.style.overflow = 'hidden';
                } else {
                    body.style.overflow = '';
                }
            });
            observer.observe(wrapper, {
                attributes: true,
                attributeFilter: ['class']
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
