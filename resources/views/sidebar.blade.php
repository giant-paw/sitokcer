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
            <a href="#" class="dropdown-toggle" aria-expanded="false">
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
            <a href="#" class="dropdown-toggle" aria-expanded="false">
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Tim Sosial</span>
                <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->routeIs('sosial.tahunan.*') ? 'active-link' : '' }}">
                    <a href="{{ route('sosial.tahunan.index') }}">Sosial Tahunan</a>
                </li>

                {{-- SOSIAL TRIWULAN --}}
                <li class="menu-item has-dropdown {{ request()->is('sosial/triwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" aria-expanded="false">
                        <span>Kegiatan Triwulan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('sosial/triwulanan/seruti*') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.triwulanan.index', ['jenisKegiatan' => 'seruti']) }}">Seruti</a>
                        </li>
                    </ul>
                </li>

                {{-- SOSIAL SEMESTERAN --}}
                <li class="menu-item has-dropdown {{ request()->routeIs('sosial.semesteran.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" aria-expanded="false">
                        <span>Kegiatan Semesteran</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('sosial/semesteran/sakernas*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('sosial.semesteran.index', ['jenisKegiatan' => 'sakernas']) }}">Sakernas</a>
                        </li>
                        <li class="{{ request()->is('sosial/semesteran/susenas*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('sosial.semesteran.index', ['jenisKegiatan' => 'susenas']) }}">Susenas</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        {{-- Tim Distribusi --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('tim-distribusi.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle" aria-expanded="false">
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
                    <a href="#" class="dropdown-toggle" aria-expanded="false">
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
                    <a href="#" class="dropdown-toggle" aria-expanded="false">
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
            <a href="#" class="dropdown-toggle" aria-expanded="false">
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
                    <a href="#" class="dropdown-toggle" aria-expanded="false">
                        <span>Kegiatan Caturwulan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('tim-produksi/caturwulanan/upp*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'ubinan padi palawija']) }}">Ubinan
                                Padi Palawija</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/caturwulanan/uup*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'updating utp palawija']) }}">Updating
                                UTP Palawija</a>
                        </li>
                    </ul>
                </li>

                <li
                    class="menu-item has-dropdown {{ request()->routeIs('tim-produksi.triwulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" aria-expanded="false">
                        <span>Kegiatan Triwulan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('tim-produksi/triwulanan/sktr*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sktr']) }}">SKTR</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/tpi*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'tpi']) }}">TPI</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphbst*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphbst']) }}">SPHBST</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphtbf*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphtbf']) }}">SPHTBF</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/sphth*') ? 'active-link' : '' }}">
                            <a
                                href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'sphth']) }}">SPHTH</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/triwulanan/airbersih*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.triwulanan.index', ['jenisKegiatan' => 'airbersih']) }}">Air
                                Bersih</a>
                        </li>
                    </ul>
                </li>

                <li class="menu-item has-dropdown {{ request()->routeIs('tim-produksi.bulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" aria-expanded="false">
                        <span>Kegiatan Bulanan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'ksapadi' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'ksapadi']) }}">KSA
                                Padi</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'ksajagung' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'ksajagung']) }}">KSA
                                Jagung</a>
                        </li>
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
                                Palawija</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'perkebunan' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'perkebunan']) }}">Perkebunan
                                Bulanan</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('tim-produksi.bulanan.index') && request()->route('jenisKegiatan') == 'ibs' ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.bulanan.index', ['jenisKegiatan' => 'ibs']) }}">IBS
                                Bulanan</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        {{-- Tim NWA --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('nwa.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle" aria-expanded="false">
                <i class="bi bi-bar-chart-line-fill menu-icon"></i>
                <span>Tim NWA</span>
                <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->routeIs('nwa.tahunan.*') ? 'active-link' : '' }}">
                    <a href="{{ route('nwa.tahunan.index') }}">NWA Tahunan</a>
                </li>

                <li class="menu-item has-dropdown {{ request()->routeIs('nwa.triwulanan.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" aria-expanded="false">
                        <span>NWA Triwulanan</span>
                        <i class="bi bi-chevron-right dropdown-arrow-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->routeIs('nwa.triwulanan.index') && request()->route('jenis') == 'sklnp' ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'sklnp') }}">SKLNP</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('nwa.triwulanan.index') && request()->route('jenis') == 'snaper' ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'snaper') }}">Snaper</a>
                        </li>
                        <li
                            class="{{ request()->routeIs('nwa.triwulanan.index') && request()->route('jenis') == 'sktnp' ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.triwulanan.index', 'sktnp') }}">SKTNP</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        {{-- Rekapitulasi --}}
        <li class="menu-item has-dropdown {{ request()->routeIs('rekapitulasi.*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle" aria-expanded="false">
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
    </ul>
</aside>

<style>
    /* ===== PREMIUM SIDEBAR STYLES ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* ===== SIDEBAR CONTAINER ===== */
    .sidebar {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 1rem 0;
    }

    /* ===== SIDEBAR MENU ===== */
    .sidebar-menu {
        list-style: none;
        padding: 0 12px;
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
        color: rgba(30, 41, 59, 0.8);
        text-decoration: none;
        padding: 12px 16px;
        border-radius: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.925rem;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }

    .sidebar-menu>li>a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scaleY(0);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 0 3px 3px 0;
    }

    .sidebar-menu>li>a:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        color: #667eea;
        transform: translateX(4px);
    }

    .sidebar-menu>li>a:hover::before {
        transform: scaleY(1);
    }

    /* ===== MENU ICON ===== */
    .menu-icon {
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
        flex-shrink: 0;
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

    /* Arrow Icon */
    .dropdown-arrow-icon {
        margin-left: auto;
        flex-shrink: 0;
        font-size: 0.8rem;
        color: rgba(30, 41, 59, 0.5);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .submenu .dropdown-toggle .dropdown-arrow-icon {
        font-size: 0.75rem;
    }

    /* ===== SUBMENU ===== */
    .submenu {
        list-style: none;
        padding: 0;
        margin: 4px 0 0 20px;
        border-left: 2px solid rgba(102, 126, 234, 0.15);
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1),
            padding 0.4s cubic-bezier(0.4, 0, 0.2, 1),
            margin 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .submenu li {
        margin-bottom: 0;
    }

    .submenu a {
        font-size: 0.875rem;
        padding: 10px 16px;
        color: rgba(30, 41, 59, 0.7);
        font-weight: 400;
        border-radius: 8px;
        margin: 2px 0;
        gap: 10px;
    }

    .submenu a:hover {
        color: #667eea;
        background: rgba(102, 126, 234, 0.08);
        transform: translateX(4px);
    }

    .submenu a::before {
        display: none;
    }

    /* Nested Submenu Styling */
    .submenu .submenu {
        margin-left: 16px;
        border-left-color: rgba(102, 126, 234, 0.1);
    }

    .submenu .submenu a {
        font-size: 0.85rem;
        padding: 8px 14px;
        color: rgba(30, 41, 59, 0.65);
    }

    /* ===== ACTIVE STATES ===== */
    /* Active Dropdown */
    .menu-item.has-dropdown.active>.submenu {
        max-height: 2000px;
        padding: 8px 0;
        margin-top: 6px;
    }

    .menu-item.has-dropdown.active>.dropdown-toggle {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.12) 0%, rgba(118, 75, 162, 0.12) 100%);
        color: #667eea;
    }

    /* Rotasi Arrow saat Aktif */
    .menu-item.has-dropdown.active>.dropdown-toggle .dropdown-arrow-icon {
        transform: rotate(90deg);
        color: #667eea;
    }

    /* Active Link (Current Page) */
    .sidebar-menu li.active-link>a {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        font-weight: 600;
        transform: translateX(4px);
    }

    .sidebar-menu li.active-link>a:hover {
        background: linear-gradient(135deg, #5568d3 0%, #653a8a 100%);
        transform: translateX(4px);
    }

    .sidebar-menu li.active-link>a::before {
        transform: scaleY(1);
        background: #fff;
    }

    .sidebar-menu li.active-link>a .menu-icon {
        color: #fff;
    }

    /* Active Submenu Link */
    .submenu li.active-link>a {
        color: #667eea;
        font-weight: 600;
        background: rgba(102, 126, 234, 0.15);
        border-left: 3px solid #667eea;
        padding-left: 13px;
    }

    .submenu li.active-link>a:hover {
        background: rgba(102, 126, 234, 0.22);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991.98px) {
        .sidebar-menu {
            padding: 0 10px;
        }

        .sidebar-menu li a {
            font-size: 0.9rem;
            padding: 11px 14px;
        }

        .submenu a {
            font-size: 0.85rem;
            padding: 9px 14px;
        }
    }

    /* Hapus after yang tidak perlu */
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
                e.stopPropagation();
                const parentMenuItem = this.closest('.menu-item.has-dropdown');
                const parentLevel = parentMenuItem.parentElement;
                // Close other dropdowns at the same level
                const siblings = parentLevel.querySelectorAll(
                    ':scope > .menu-item.has-dropdown.active');
                siblings.forEach(sibling => {
                    if (sibling !== parentMenuItem) {
                        sibling.classList.remove('active');
                        const siblingToggle = sibling.querySelector(
                            ':scope > .dropdown-toggle');
                        if (siblingToggle) {
                            siblingToggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                });
                // Toggle current dropdown
                parentMenuItem.classList.toggle('active');

                // Update aria-expanded
                const isActive = parentMenuItem.classList.contains('active');
                this.setAttribute('aria-expanded', isActive);
            });
        });
        // Auto-open parent dropdowns for active submenu items
        const activeSubmenuItem = document.querySelector('.submenu .active-link');
        if (activeSubmenuItem) {
            let current = activeSubmenuItem.closest('.menu-item.has-dropdown');
            while (current) {
                current.classList.add('active');
                const toggle = current.querySelector(':scope > .dropdown-toggle');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'true');
                }
                current = current.parentElement.closest('.menu-item.has-dropdown');
            }
        }
    });
</script>
