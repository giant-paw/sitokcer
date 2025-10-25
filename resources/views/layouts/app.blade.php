<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Sitokcer')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            height: 100vh;
            position: relative;
        }

        #sidebar-wrapper {
            width: 270px;
            flex-shrink: 0;
            background-color: #2c3e50;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            /* Samakan durasi transisi */
            z-index: 1000;
        }

        /* [MODIFIKASI] Target <html>, bukan .page-wrapper */
        html.sidebar-collapsed #sidebar-wrapper {
            width: 60px;
            /* Samakan dengan lebar collapsed di sidebar.css */
        }

        .main-panel-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            height: calc(100vh - var(--header-height));
        }

        #content-wrapper {
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 2rem;
            background: transparent;
            transition: margin-left 0.4s var(--transition-smooth);
            scroll-behavior: smooth;
        }

        /* [BARU] CSS untuk mencegah transisi saat halaman dimuat */
        html.no-transitions * {
            transition: none !important;
        }
    </style>

    <script>
        (function () {
            // Atur bahasa di <html>
            document.documentElement.lang = "{{ str_replace('_', '-', app()->getLocale()) }}";

            // Tambahkan kelas no-transitions untuk mematikan animasi saat load
            document.documentElement.classList.add('no-transitions');

            // Periksa localStorage dan terapkan kelas collapsed SEBELUM render
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>

    @stack('styles')
</head>

<body>

    <div class="page-wrapper">
        <aside id="sidebar-wrapper">
            @include('sidebar')
        </aside>

        <div class="main-panel-wrapper">
            <main id="content-wrapper">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>


    @stack('scripts')

    <script>
        window.addEventListener('load', () => {
            document.documentElement.classList.remove('no-transitions');
        });
    </script>
</body>

</html>

<!-- checkpoint -->