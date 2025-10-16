<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sitokcer')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            overflow-y: hidden;
            background-color: #f8f9fa;
            font-family: sans-serif;
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .header-navbar {
            height: 60px;
            padding: 0 1rem;
            display: flex;
            align-items: center;
        }

        .content-body-wrapper {
            display: flex;
            flex-grow: 1;
            overflow: hidden;
        }

        #sidebar-wrapper {
            min-width: 270px;
            max-width: 270px;
            transition: margin 0.3s ease-in-out;
            background-color: white;
        }

        .content-body-wrapper.sidebar-collapsed #sidebar-wrapper {
            margin-left: -270px;
        }

        #content-wrapper {
            width: 100%;
            height: 100%;
            overflow-y: auto;
            padding: 1.5rem;
        }

        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                margin-left: -270px;
                position: fixed;
                height: 100%;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1045;
            }

            .content-body-wrapper.sidebar-collapsed #sidebar-wrapper {
                margin-left: 0;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                display: none;
                opacity: 0;
                transition: opacity 0.3s ease-linear;
            }

            .content-body-wrapper.sidebar-collapsed .sidebar-overlay {
                display: block;
                opacity: 1;
            }

            .header-navbar {
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
                height: 60px;
            }

            .navbar-brand img {
                height: 32px;
            }

            .navbar-brand span {
                font-size: 1.1rem;
            }

        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="page-wrapper">
        <header class="navbar navbar-expand navbar-light bg-white shadow-sm header-navbar py-0">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-secondary p-0 me-3" type="button" id="sidebar-toggle-button">
                        <i class="bi bi-list" style="font-size: 1.9rem;"></i>
                    </button>
                    <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center">
                        <img src="{{ asset('logo.png') }}" alt="Logo Sitokcer" style="height: 32px;">
                        <span class="fw-bold fs-5 ms-2" style="font-family: 'Poppins', sans-serif;">Sitokcer</span>
                    </a>
                </div>

                <div class="d-flex align-items-center ms-auto">
                    <div class="h6 fw-semibold text-dark mb-0">
                        @yield('header-title', 'Dashboard')
                    </div>
                </div>
            </div>
        </header>



        <div class="content-body-wrapper">
            <aside id="sidebar-wrapper">
                @include('sidebar')
            </aside>
            <div class="sidebar-overlay" id="sidebar-overlay"></div>
            <main id="content-wrapper">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('sidebar-toggle-button');
            const wrapper = document.querySelector('.content-body-wrapper');
            const overlay = document.getElementById('sidebar-overlay');
            const toggleSidebar = () => wrapper.classList.toggle('sidebar-collapsed');
            if (toggleButton) toggleButton.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);
        });
    </script>
    <script src="{{ asset('js/sidebar.js') }}"></script>

    @stack('scripts')
</body>

</html>