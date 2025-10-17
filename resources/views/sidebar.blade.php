{{-- resources/views/sidebar.blade.php --}}
<aside class="sidebar">
    {{-- Logo dan Nama Situs --}}
    <!-- <div class="sidebar-header">
        <a href="{{ route('home') }}" class="sidebar-logo-link">
            <img src="{{ asset('logo.png') }}" alt="Logo Sitokcer" class="sidebar-logo">
            <h1 class="sidebar-title">Sitokcer</h1>
        </a>
    </div> -->

    <ul class="sidebar-menu">
        <li class="{{ request()->is('/') ? 'active-link' : '' }}">
            <a href="{{ route('home') }}">
                <i class="bi bi-house-door-fill menu-icon"></i>
                <span>Home</span>
            </a>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('dashboard*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-grid-1x2-fill menu-icon"></i>
                <span>Dashboards</span>

            </a>
            <ul class="submenu">
                <li class="{{ request()->is('dashboard-distribusi') ? 'active-link' : '' }}"><a
                        href="{{ route('dashboard.distribusi') }}">Distribusi</a></li>
                <li class="{{ request()->is('dashboard-nwa') ? 'active-link' : '' }}"><a
                        href="{{ route('dashboard.nwa') }}">NWA</a></li>
                <li class="{{ request()->is('dashboard-produksi') ? 'active-link' : '' }}"><a
                        href="{{ route('dashboard.produksi') }}">Produksi</a></li>
                <li class="{{ request()->is('dashboard-sosial') ? 'active-link' : '' }}"><a
                        href="{{ route('dashboard.sosial') }}">Sosial</a></li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('sosial*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Tim Sosial</span>

            </a>
            <ul class="submenu">
                <li class="{{ request()->is('sosial/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('sosial.tahunan.index') }}">Sosial Tahunan</a>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('sosial/kegiatan-triwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>

                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('sosial') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.seruti.index') }}">Seruti</a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('sosial/kegiatan-semesteran*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Semesteran</span>

                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('sosial/kegiatan-semesteran/sakernas*') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.semesteran.index') }}">Sakernas</a>
                        </li>
                        <li class="{{ request()->is('sosial/kegiatan-semesteran/susenas*') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.susenas') }}">Susenas</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <!-- Tim { Distribusi } -->

        <li class="menu-item has-dropdown {{ request()->is('distribusi*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-truck menu-icon"></i>
                <span>Tim Distribusi</span>

            </a>
            <ul class="submenu">

                <!-- Distribusi Tahunan  -->
                <li class="{{ request()->is('tim-distribusi/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('tim-distribusi.tahunan.index') }}">Distribusi Tahunan</a>
                </li>

                <!-- Distribusi Triwulanan  -->
                <li
                    class="menu-item has-dropdown {{ request()->is('tim-distribusi/triwulan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulanan</span>

                    </a>

                    <ul class="submenu">
                        {{-- Link SPUNP --}}
                        <li class="{{ request()->is('tim-distribusi/triwulanan/spunp*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => 'spunp']) }}">SPUNP</a>
                        </li>

                        {{-- Link SHKK --}}
                        <li class="{{ request()->is('tim-distribusi/triwulanan/shkk*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => 'shkk']) }}">SHKK</a>
                        </li>
                    </ul>
                    
                </li>
            
                <!-- Distribusi Bulanan -->

                <li class="menu-item has-dropdown {{ request()->is('tim-distribusi/bulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Distribusi Bulanan</span>
                    </a>
                    
                    <ul class="submenu">
                        {{-- Link VHTS --}}
                        <li class="{{ request()->is('tim-distribusi/bulanan/vhts*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'vhts']) }}">VHTS</a>
                        </li>

                        {{-- Link HKD --}}
                        <li class="{{ request()->is('tim-distribusi/bulanan/hkd*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'hkd']) }}">HKD</a>
                        </li>

                        {{-- Link SHPB --}}
                        <li class="{{ request()->is('tim-distribusi/bulanan/shpb*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'shpb']) }}">SHPB</a>
                        </li>

                        {{-- Link SHP --}}
                        <li class="{{ request()->is('tim-distribusi/bulanan/shp*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'shp']) }}">SHP</a>
                        </li>

                        {{-- Link SHPJ --}}
                        <li class="{{ request()->is('tim-distribusi/bulanan/shpj*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'shpj']) }}">SHPJ</a>
                        </li>

                        {{-- Link SHPBG --}}
                        <li class="{{ request()->is('tim-distribusi/bulanan/shpbg*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'shpbg']) }}">SHPBG</a>
                        </li>
                    </ul>

                </li>
            </ul>
        </li>

        <!-- TIM { PRODUKSI } -->

        <li class="menu-item has-dropdown {{ request()->is('tim-produksi*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-graph-up-arrow menu-icon"></i>
                <span>Tim Produksi</span>

            </a>
            <ul class="submenu">
                
                <!-- Produksi Tahunan  -->
                <li class="{{ request()->is('tim-produksi/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('tim-produksi.tahunan.index') }}">Produksi Tahunan</a>
                </li>
                
                <!-- Produksi Caturwulanan  -->
                <li
                    class="menu-item has-dropdown {{ request()->is('tim-produksi/caturwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Caturwulan</span>

                    </a>
                    <ul class="submenu">

                        <!-- Ubinan Padi -->
                        <li
                            class="{{ request()->is('tim-produksi/caturwulanan/upp*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'ubinan padi palawija']) }}">Ubinan Padi Palawija</a>
                        </li>

                        <!-- Ubinan UTP -->
                        <li
                            class="{{ request()->is('tim-produksi/caturwulanan/uup*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'ubinan utp palawija']) }}">Ubinan UTP Palawija</a>
                        </li>
                    </ul>
                </li>

                <!-- Kegiatan TRIWULAN -->

                <li class="menu-item has-dropdown {{ request()->is('tim-produksi/triwulan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>

                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('tim-produksi/triwulanan/sktr*') ? 'active-link' : '' }}"><a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sktr']) }}">SKTR</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/tpi*') ? 'active-link' : '' }}"><a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'tpi']) }}">TPI</a></li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphbst*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphbst']) }}">SPHBST</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphtbf*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphtbf']) }}">SPHTBF</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphth*') ? 'active-link' : '' }}"><a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphth']) }}">SPHTH</a></li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/air-bersih*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'airbersih']) }}">Air Bersih</a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('produksi/kegiatan-bulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Bulanan</span>

                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/ksapadi*') ? 'active-link' : '' }}">
                            <a href="#">KSA Padi</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/ksajagung*') ? 'active-link' : '' }}">
                            <a href="#">KSA Jagung</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/lptb*') ? 'active-link' : '' }}"><a
                                href="#">LPTB</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/sphsbs*') ? 'active-link' : '' }}"><a
                                href="#">SPHSBS</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/sppalawija*') ? 'active-link' : '' }}">
                            <a href="#">SP Palawija</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/perkebunan*') ? 'active-link' : '' }}">
                            <a href="#">Perkebunan Bulanan</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/ibs*') ? 'active-link' : '' }}"><a
                                href="#">IBS Bulanan</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('nwa*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-bar-chart-line-fill menu-icon"></i>
                <span>Tim NWA</span>

            </a>
            <ul class="submenu">
                <li class="{{ request()->is('nwa/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('nwa.tahunan.index') }}">NWA Tahunan</a>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('nwa/triwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>NWA Triwulanan</span>

                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('nwa/triwulanan/sklnp*') ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'sklnp') }}">SKLNP</a>
                        </li>
                        <li class="{{ request()->is('nwa/triwulanan/snaper*') ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'snaper') }}">Snaper</a>
                        </li>
                        <li class="{{ request()->is('nwa/triwulanan/sktnp*') ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'sktnp') }}">SKTNP</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('rekapitulasi*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-file-earmark-text-fill menu-icon"></i>
                <span>Rekapitulasi</span>

            </a>
            <ul class="submenu">
                <li class="{{ request()->is('rekapitulasi/pencacah*') ? 'active-link' : '' }}">
                    <a href="{{ route('rekapitulasi.pencacah') }}">Rekap Pencacah</a>
                </li>
                <li class="{{ request()->is('rekapitulasi/pengawas*') ? 'active-link' : '' }}">
                    <a href="{{ route('rekapitulasi.pengawas') }}">Rekap Pengawas</a>
                </li>
            </ul>
        </li>

        {{-- Ganti href-nya menjadi route('master.petugas.index') --}}
        <li class="{{ request()->is('master-petugas*') ? 'active-link' : '' }}">
            <a href="{{ route('master.petugas.index') }}">
                <i class="bi bi-person-badge-fill menu-icon"></i>
                <span>Master Petugas</span>
            </a>
        </li>

        <li class="{{ request()->is('master-kegiatan*') ? 'active-link' : '' }}">
            <a href="{{ route('master.kegiatan') }}">
                <i class="bi bi-clipboard-data-fill menu-icon"></i>
                <span>Master Kegiatan</span>
            </a>
        </li>

        <li class="{{ request()->is('user*') ? 'active-link' : '' }}">
            <a href="{{ route('user') }}">
                <i class="bi bi-person-fill-gear menu-icon"></i>
                <span>User</span>
            </a>
        </li>
    </ul>
</aside>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .sidebar {
        width: 270px;
        background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        color: white;
        height: 100%;
        padding: 0;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .sidebar-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-logo-link {
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    .sidebar-logo {
        height: 40px;
        width: 40px;
        object-fit: contain;
    }

    .sidebar-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin: 0 0 0 0.75rem;
        font-family: 'Poppins', sans-serif;
    }

    .sidebar-menu {
        list-style: none;
        padding: 15px 12px;
        flex-grow: 1;
    }

    .sidebar-menu>li {
        margin-bottom: 6px;
    }

    .sidebar-menu li a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        padding: 12px 16px;
        border-radius: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem;
        font-weight: 500;
        position: relative;
    }

    .sidebar-menu>li>a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        transform: translateX(4px);
    }

    .sidebar-menu>li>a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 0;
        background: #3498db;
        border-radius: 0 3px 3px 0;
        transition: height 0.3s ease;
    }

    .sidebar-menu>li>a:hover::before {
        height: 70%;
    }

    .menu-icon {
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
        flex-shrink: 0;
        opacity: 0.9;
    }

    .menu-item.has-dropdown>a {
        cursor: pointer;
    }

    .dropdown-toggle {
        width: 100%;
        justify-content: space-between;
    }

    .dropdown-toggle span {
        flex-grow: 1;
    }

    .arrow {
        font-size: 0.8rem;
        margin-left: auto;
        transition: transform 0.3s ease;
        flex-shrink: 0;
        opacity: 0.7;
    }

    .submenu {
        list-style: none;
        padding: 0;
        margin: 0;
        margin-left: 20px;
        border-left: 2px solid rgba(255, 255, 255, 0.15);
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .submenu li {
        margin-bottom: 0;
    }

    .submenu a {
        font-size: 0.88rem;
        padding: 10px 16px;
        color: rgba(255, 255, 255, 0.7);
        font-weight: 400;
        border-radius: 6px;
        margin: 4px 0;
    }

    .submenu a:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.08);
        transform: translateX(4px);
    }

    .submenu a::before {
        display: none;
    }

    .menu-item.has-dropdown.active>.submenu {
        max-height: 500px;
        padding: 8px 0;
    }

    .menu-item.has-dropdown.active>.dropdown-toggle {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .menu-item.has-dropdown.active .arrow {
        transform: rotate(90deg);
        opacity: 1;
    }

    .sidebar-menu li.active-link>a {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
    }

    .sidebar-menu li.active-link>a:hover {
        background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%);
        transform: translateX(0);
    }

    .sidebar-menu li.active-link>a::before {
        height: 0;
    }

    .submenu li.active-link>a {
        color: #3498db;
        font-weight: 600;
        background: rgba(52, 152, 219, 0.15);
    }

    .submenu li.active-link>a:hover {
        background: rgba(52, 152, 219, 0.25);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownToggles = document.querySelectorAll('.sidebar .dropdown-toggle');

        dropdownToggles.forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                const parentMenuItem = this.closest('.menu-item.has-dropdown');
                const parentLevel = parentMenuItem.parentElement; // ul induk

                // Tutup hanya dropdown lain di level yang sama
                const siblings = parentLevel.querySelectorAll('.menu-item.has-dropdown.active');
                siblings.forEach(function (sibling) {
                    if (sibling !== parentMenuItem) {
                        sibling.classList.remove('active');
                    }
                });

                // Toggle menu yang diklik
                parentMenuItem.classList.toggle('active');
            });
        });

        // Buka semua parent dropdown yang punya submenu aktif
        const activeSubmenuItem = document.querySelector('.submenu .active-link');
        if (activeSubmenuItem) {
            let current = activeSubmenuItem.closest('.menu-item.has-dropdown');
            while (current) {
                current.classList.add('active');
                current = current.parentElement.closest('.menu-item.has-dropdown');
            }
        }
    });
</script>
