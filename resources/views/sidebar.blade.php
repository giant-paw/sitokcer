{{-- resources/views/sidebar.blade.php --}}
<aside class="sidebar">
    <ul class="sidebar-menu">
        {{-- Home --}}
        <li class="{{ request()->routeIs('home') ? 'active-link' : '' }}">
            <a href="{{ route('home') }}">
                <i class="bi bi-house-door-fill menu-icon"></i>
                <span>Home</span>
            </a>
        </li>

        {{-- Dashboards --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-grid-1x2-fill menu-icon"></i>
                <span>Dashboards</span>
                <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->routeIs('dashboard.distribusi') ? 'active-link' : '' }}">
                    <a href="{{ route('dashboard.distribusi') }}">Distribusi</a>
                </li>
                <li class="{{ request()->routeIs('dashboard.nwa') ? 'active-link' : '' }}">
                    <a href="{{ route('dashboard.nwa') }}">NWA</a>
                </li>
                <li class="{{ request()->routeIs('dashboard.produksi') ? 'active-link' : '' }}">
                    <a href="{{ route('dashboard.produksi') }}">Produksi</a>
                </li>
                <li class="{{ request()->routeIs('dashboard.sosial') ? 'active-link' : '' }}">
                    <a href="{{ route('dashboard.sosial') }}">Sosial</a>
                </li>
            </ul>
        </li>

        {{-- Tim Sosial --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('sosial.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Tim Sosial</span>
                <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->routeIs('sosial.tahunan.*') ? 'active-link' : '' }}">
                    <a href="{{ route('sosial.tahunan.index') }}">Sosial Tahunan</a>
                </li>

                <li class="menu-item has-dropdown {{ request()->routeIs('sosial.seruti.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->routeIs('sosial.seruti.*') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.seruti.index') }}">Seruti</a>
                        </li>
                    </ul>
                </li>

                <li class="menu-item has-dropdown {{ request()->routeIs('sosial.semesteran.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Semesteran</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->routeIs('sosial.semesteran.*') && request()->route('kategori') == 'Sakernas' ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.semesteran.index', 'Sakernas') }}">Sakernas</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('sosial.semesteran.*') && request()->route('kategori') == 'Susenas' ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.semesteran.index', 'Susenas') }}">Susenas</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        {{-- Tim Distribusi --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('tim-distribusi.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-truck menu-icon"></i>
                <span>Tim Distribusi</span>
                <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->routeIs('tim-distribusi.tahunan.*') ? 'active-link' : '' }}">
                    <a href="{{ route('tim-distribusi.tahunan.index') }}">Distribusi Tahunan</a>
                </li>

                <li
                    class="menu-item has-dropdown {{ request()->routeIs('tim-distribusi.triwulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulanan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->routeIs('tim-distribusi.triwulanan.index') && request()->route('jenisKegiatan') == 'spunp' ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => 'spunp']) }}">SPUNP</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-distribusi.triwulanan.index') && request()->route('jenisKegiatan') == 'shkk' ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => 'shkk']) }}">SHKK</a>
                        </li>
                    </ul>
                </li>

                <li
                    class="menu-item has-dropdown {{ request()->routeIs('tim-distribusi.bulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Distribusi Bulanan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->routeIs('tim-distribusi.bulanan.index') && request()->route('jenisKegiatan') == 'vhts' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'vhts']) }}">VHTS</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-distribusi.bulanan.index') && request()->route('jenisKegiatan') == 'hkd' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'hkd']) }}">HKD</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-distribusi.bulanan.index') && request()->route('jenisKegiatan') == 'shpb' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'shpb']) }}">SHPB</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-distribusi.bulanan.index') && request()->route('jenisKegiatan') == 'shp' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'shp']) }}">SHP</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-distribusi.bulanan.index') && request()->route('jenisKegiatan') == 'shpj' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'shpj']) }}">SHPJ</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-distribusi.bulanan.index') && request()->route('jenisKegiatan') == 'shpbg' ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-distribusi.bulanan.index', ['jenisKegiatan' => 'shpbg']) }}">SHPBG</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        {{-- Tim Produksi --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('tim-produksi.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-graph-up-arrow menu-icon"></i>
                <span>Tim Produksi</span>
                <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->routeIs('tim-produksi.tahunan.*') ? 'active-link' : '' }}">
                    <a href="{{ route('tim-produksi.tahunan.index') }}">Produksi Tahunan</a>
                </li>

                <li
                    class="menu-item has-dropdown {{ request()->routeIs('tim-produksi.caturwulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Caturwulan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">

                        <!-- Ubinan Padi -->
                        <li class="{{ request()->is('tim-produksi/caturwulanan/upp*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'ubinan padi palawija']) }}">Ubinan
                                Padi Palawija</a>
                        </li>

                        <!-- Ubinan UTP -->
                        <li class="{{ request()->is('tim-produksi/caturwulanan/uup*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'updating utp palawija']) }}">Updating
                                UTP Palawija</a>
                        </li>
                    </ul>
                </li>

                <li
                    class="menu-item has-dropdown {{ request()->routeIs('tim-produksi.triwulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('tim-produksi/triwulanan/sktr*') ? 'active-link' : '' }}"><a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sktr']) }}">SKTR</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/tpi*') ? 'active-link' : '' }}"><a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'tpi']) }}">TPI</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphbst*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphbst']) }}">SPHBST</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphtbf*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphtbf']) }}">SPHTBF</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphth*') ? 'active-link' : '' }}"><a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphth']) }}">SPHTH</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/airbersih*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'airbersih']) }}">Air
                                Bersih</a>
                        </li>
                    </ul>
                </li>

                <li class="menu-item has-dropdown {{ request()->routeIs('tim-produksi.bulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Bulanan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'ksapadi' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'ksapadi']) }}">KSA
                                Padi</a></li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'ksajagung' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'ksajagung']) }}">KSA
                                Jagung</a></li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'lptb' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'lptb']) }}">LPTB</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'sphsbs' ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'sphsbs']) }}">SPHSBS</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'sppalawija' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'sppalawija']) }}">SP
                                Palawija</a></li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'perkebunan' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'perkebunan']) }}">Perkebunan
                                Bulanan</a></li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'ibs' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'ibs']) }}">IBS
                                Bulanan</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        {{-- Tim NWA --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('nwa.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-bar-chart-line-fill menu-icon"></i>
                <span>Tim NWA</span>
                <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->routeIs('nwa.tahunan.*') ? 'active-link' : '' }}">
                    <a href="{{ route('nwa.tahunan.index') }}">NWA Tahunan</a>
                </li>
                <li class="menu-item has-dropdown {{ request()->routeIs('nwa.triwulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>NWA Triwulanan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->routeIs('nwa.triwulanan.index') && request()->route('jenis') == 'sklnp' ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'sklnp') }}">SKLNP</a></li>
                        <li
                            class="{{ request()->routeIs('nwa.triwulanan.index') && request()->route('jenis') == 'snaper' ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'snaper') }}">Snaper</a></li>
                        <li
                            class="{{ request()->routeIs('nwa.triwulanan.index') && request()->route('jenis') == 'sktnp' ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'sktnp') }}">SKTNP</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        {{-- Rekapitulasi --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('rekapitulasi.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-file-earmark-text-fill menu-icon"></i>
                <span>Rekapitulasi</span>
                <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->routeIs('rekapitulasi.pencacah.*') ? 'active-link' : '' }}">
                    <a href="{{ route('rekapitulasi.pencacah.index') }}">Rekap Pencacah</a>
                </li>
                <li class="{{ request()->routeIs('rekapitulasi.pengawas.*') ? 'active-link' : '' }}">
                    <a href="{{ route('rekapitulasi.pengawas.index') }}">Rekap Pengawas</a>
                </li>
            </ul>
        </li>

        {{-- Master Petugas --}}
        <li class="{{ request()->routeIs('master.petugas.*') ? 'active-link' : '' }}">
            <a href="{{ route('master.petugas.index') }}">
                <i class="bi bi-person-badge-fill menu-icon"></i>
                <span>Master Petugas</span>
            </a>
        </li>

        {{-- Master Kegiatan --}}
        <li class="{{ request()->routeIs('master.kegiatan.*') ? 'active-link' : '' }}">
            <a href="{{ route('master.kegiatan.index') }}">
                <i class="bi bi-clipboard-data-fill menu-icon"></i>
                <span>Master Kegiatan</span>
            </a>
        </li>

        {{-- User --}}
        <li class="{{ request()->routeIs('user') ? 'active-link' : '' }}">
            <a href="{{ route('user') }}">
                <i class="bi bi-person-fill-gear menu-icon"></i>
                <span>User</span>
            </a>
        </li>
    </ul>
</aside>

<style>
    /* ===== RESET & BASE ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* ===== SIDEBAR CONTAINER ===== */
    .sidebar {
        width: 270px;
        background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        color: #fff;
        height: 100%;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    /* Scrollbar Styling */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
        transition: background 0.3s ease;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* ===== SIDEBAR MENU ===== */
    .sidebar-menu {
        list-style: none;
        padding: 15px 12px;
        flex-grow: 1;
    }

    .sidebar-menu>li {
        margin-bottom: 4px;
    }

    /* ===== MENU LINKS ===== */
    .sidebar-menu li a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        padding: 11px 14px;
        border-radius: 8px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.925rem;
        font-weight: 500;
        position: relative;
    }

    .sidebar-menu>li>a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        transform: translateX(3px);
    }

    /* Accent Bar on Hover */
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
        transition: height 0.25s ease;
    }

    .sidebar-menu>li>a:hover::before {
        height: 65%;
    }

    /* ===== MENU ICON ===== */
    .menu-icon {
        font-size: 1.15rem;
        width: 22px;
        text-align: center;
        flex-shrink: 0;
        opacity: 0.9;
    }

    /* ===== DROPDOWN TOGGLE ===== */
    .dropdown-toggle {
        width: 100%;
        cursor: pointer;
        position: relative;
    }

    .dropdown-toggle span {
        flex-grow: 1;
    }

    /* === [BARU] ARROW ICON === */
    .dropdown-arrow-icon {
        margin-left: auto;
        flex-shrink: 0;
        font-size: 0.8rem;
        /* Sesuaikan ukuran */
        color: rgba(255, 255, 255, 0.6);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* [BARU] Style untuk arrow di submenu (sedikit lebih kecil) */
    .submenu .dropdown-toggle .dropdown-arrow-icon {
        font-size: 0.75rem;
    }

    /* ===== SUBMENU ===== */
    .submenu {
        list-style: none;
        padding: 0;
        margin: 0 0 0 18px;
        border-left: 2px solid rgba(255, 255, 255, 0.12);
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1),
            padding 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .submenu li {
        margin-bottom: 0;
    }

    .submenu a {
        font-size: 0.875rem;
        padding: 9px 14px;
        color: rgba(255, 255, 255, 0.75);
        font-weight: 400;
        border-radius: 6px;
        margin: 3px 0;
        gap: 10px;
    }

    .submenu a:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.08);
        transform: translateX(3px);
    }

    .submenu a::before {
        display: none;
    }

    /* Nested Submenu Styling */
    .submenu .submenu {
        margin-left: 12px;
        border-left-color: rgba(255, 255, 255, 0.08);
    }

    .submenu .submenu a {
        font-size: 0.85rem;
        padding: 8px 12px;
        color: rgba(255, 255, 255, 0.7);
    }

    /* ===== ACTIVE STATES ===== */

    /* Active Dropdown */
    .menu-item.has-dropdown.active>.submenu {
        max-height: 1500px;
        /* Nilai besar untuk menampung semua item */
        padding: 6px 0;
    }

    .menu-item.has-dropdown.active>.dropdown-toggle {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    /* === [BARU] Rotasi untuk Ikon Arrow === */
    .menu-item.has-dropdown.active>.dropdown-toggle .dropdown-arrow-icon {
        transform: rotate(90deg);
        color: rgba(255, 255, 255, 0.9);
    }

    /* Active Link (Current Page) */
    .sidebar-menu li.active-link>a {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: #fff;
        box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        font-weight: 600;
    }

    .sidebar-menu li.active-link>a:hover {
        background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%);
        transform: translateX(0);
    }

    .sidebar-menu li.active-link>a::before {
        height: 0;
    }

    .sidebar-menu li.active-link>a .menu-icon {
        opacity: 1;
    }

    /* Active Submenu Link */
    .submenu li.active-link>a {
        color: #3498db;
        font-weight: 600;
        background: rgba(52, 152, 219, 0.12);
    }

    .submenu li.active-link>a:hover {
        background: rgba(52, 152, 219, 0.2);
    }

    /* ===== RESPONSIVE ADJUSTMENTS ===== */
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
        }
    }

    .sidebar-menu a.dropdown-toggle::after {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.sidebar .dropdown-toggle');

        // Handle dropdown toggle clicks
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();

                const parentMenuItem = this.closest('.menu-item.has-dropdown');
                const parentLevel = parentMenuItem.parentElement;

                // Close other dropdowns at the same level
                const siblings = parentLevel.querySelectorAll(
                    ':scope > .menu-item.has-dropdown.active');
                siblings.forEach(sibling => {
                    if (sibling !== parentMenuItem) {
                        sibling.classList.remove('active');
                    }
                });

                // Toggle current dropdown
                parentMenuItem.classList.toggle('active');
            });
        });

        // Auto-open parent dropdowns for active submenu items
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
