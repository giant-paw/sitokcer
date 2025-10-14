<aside class="sidebar">
    {{-- Bagian header logo sudah dipindahkan ke app.blade.php --}}

    <ul class="sidebar-menu">
        <li class="{{ request()->is('/') ? 'active-link' : '' }}">
            <a href="{{ route('home') }}">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Home</span>
            </a>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('dashboard*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboards</span>
                <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
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
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Tim Sosial</span>
                <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('sosial/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('sosial.tahunan.index') }}">Sosial Tahunan</a>
                </li>
                <li
                    class="menu-item has-dropdown {{ request()->is('sosial/kegiatan-triwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                        <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('sosial/kegiatan-triwulanan/seruti*') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.seruti') }}">Seruti</a>
                        </li>
                    </ul>
                </li>
                <li
                    class="menu-item has-dropdown {{ request()->is('sosial/kegiatan-semesteran*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Semesteran</span>
                        <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->is('sosial/kegiatan-semesteran/sakernas*') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.sakernas') }}">Sakernas</a>
                        </li>
                        <li class="{{ request()->is('sosial/kegiatan-semesteran/susenas*') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.susenas') }}">Susenas</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('distribusi*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 20 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                <span>Tim Distribusi</span>
                <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('distribusi/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('distribusi.tahunan') }}">Distribusi Tahunan</a>
                </li>
                <li
                    class="menu-item has-dropdown {{ request()->is('distribusi/kegiatan-triwulan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                        <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('distribusi/kegiatan-triwulan/spunp*') ? 'active-link' : '' }}">
                            <a href="{{ route('distribusi.spunp') }}">SPUNP</a>
                        </li>
                        <li class="{{ request()->is('distribusi/kegiatan-triwulan/shkk*') ? 'active-link' : '' }}">
                            <a href="{{ route('distribusi.shkk') }}">SHKK</a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('distribusi/bulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Distribusi Bulanan</span>
                        <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('distribusi/bulanan/vhts*') ? 'active-link' : '' }}"><a
                                href="{{ route('distribusi.vhts') }}">VHTS</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/hkd*') ? 'active-link' : '' }}"><a
                                href="{{ route('distribusi.hkd') }}">HKD</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/shpb*') ? 'active-link' : '' }}"><a
                                href="{{ route('distribusi.shpb') }}">SHPB</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/shp*') ? 'active-link' : '' }}"><a
                                href="{{ route('distribusi.shp') }}">SHP</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/shpj*') ? 'active-link' : '' }}"><a
                                href="{{ route('distribusi.shpj') }}">SHPJ</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/shpgb*') ? 'active-link' : '' }}"><a
                                href="{{ route('distribusi.shpgb') }}">SHPBG</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/hd*') ? 'active-link' : '' }}"><a
                                href="{{ route('distribusi.hd') }}">HD</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('produksi*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
                <span>Tim Produksi</span>
                <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('produksi/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('produksi.tahunan') }}">Produksi Tahunan</a>
                </li>
                <li
                    class="menu-item has-dropdown {{ request()->is('produksi/kegiatan-caturwulan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Caturwulan</span>
                        <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <ul class="submenu">
                        <li
                            class="{{ request()->is('produksi/kegiatan-caturwulan/ubinan-padi-palawija*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.ubinanpadipalawija') }}">Ubinan Padi Palawija</a>
                        </li>
                        <li
                            class="{{ request()->is('produksi/kegiatan-caturwulan/update-utp-palawija*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.updateutppalawija') }}">Update UTP Palawija</a>
                        </li>
                    </ul>
                </li>
                <li
                    class="menu-item has-dropdown {{ request()->is('produksi/kegiatan-triwulan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                        <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/sktr*') ? 'active-link' : '' }}"><a
                                href="{{ route('produksi.sktr') }}">SKTR</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/tpi*') ? 'active-link' : '' }}"><a
                                href="{{ route('produksi.tpi') }}">TPI</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/sphbst*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.sphbst') }}">SPHBST</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/sphtbf*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.sphtbf') }}">SPHTBF</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/sphth*') ? 'active-link' : '' }}"><a
                                href="{{ route('produksi.sphth') }}">SPHTH</a></li>
                        <li
                            class="{{ request()->is('produksi/kegiatan-triwulan/air-bersih*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.airbersih') }}">Air Bersih</a>
                        </li>
                    </ul>
                </li>
                <li
                    class="menu-item has-dropdown {{ request()->is('produksi/kegiatan-bulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Bulanan</span>
                        <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/ksapadi*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.ksapadi') }}">KSA Padi</a>
                        </li>
                        <li
                            class="{{ request()->is('produksi/kegiatan-bulanan/ksajagung*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.ksajagung') }}">KSA Jagung</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/lptb*') ? 'active-link' : '' }}"><a
                                href="{{ route('produksi.lptb') }}">LPTB</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/sphsbs*') ? 'active-link' : '' }}"><a
                                href="{{ route('produksi.sphsbs') }}">SPHSBS</a></li>
                        <li
                            class="{{ request()->is('produksi/kegiatan-bulanan/sppalawija*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.sppalawija') }}">SP Palawija</a>
                        </li>
                        <li
                            class="{{ request()->is('produksi/kegiatan-bulanan/perkebunan*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.perkebunanbulanan') }}">Perkebunan Bulanan</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/ibs*') ? 'active-link' : '' }}"><a
                                href="{{ route('produksi.ibsbulanan') }}">IBS Bulanan</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('nwa*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M12 20V10"></path>
                    <path d="M18 20V4"></path>
                    <path d="M6 20V16"></path>
                </svg>
                <span>Tim NWA</span>
                <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('nwa/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('nwa.tahunan') }}">NWA Tahunan</a>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('nwa/triwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>NWA Triwulanan</span>
                        <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('nwa/triwulanan/sklnp*') ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.sklnp') }}">SKLNP</a>
                        </li>
                        <li class="{{ request()->is('nwa/triwulanan/snaper*') ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.snaper') }}">Snaper</a>
                        </li>
                        <li class="{{ request()->is('nwa/triwulanan/sktnp*') ? 'active-link' : '' }}">
                            <a href="{{ route('nwa.sktnp') }}">SKTNP</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('rekapitulasi*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>Rekapitulasi</span>
                <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
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

        <li class="{{ request()->is('master-petugas*') ? 'active-link' : '' }}">
            <a href="{{ route('master.petugas') }}">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Master Petugas</span>
            </a>
        </li>

        <li class="{{ request()->is('master-kegiatan*') ? 'active-link' : '' }}">
            <a href="{{ route('master.kegiatan') }}">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                    <polyline points="13 2 13 9 20 9"></polyline>
                </svg>
                <span>Master Kegiatan</span>
            </a>
        </li>

        <li class="{{ request()->is('user*') ? 'active-link' : '' }}">
            <a href="{{ route('user') }}">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
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

    /* Menghapus style body karena sudah diatur di app.blade.php */

    .sidebar {
        width: 270px;
        background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        color: white;
        height: 100%; /* <-- INI PERUBAHAN PENTING */
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

    /* Menghapus .sidebar-header dan turunannya karena sudah tidak ada */

    .sidebar-menu {
        list-style: none;
        padding: 15px 12px;
        flex-grow: 1; /* Memastikan menu mengisi ruang yang tersedia */
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
        max-height: 500px; /* Nilai yang cukup besar untuk menampung semua submenu */
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

    /* Menghapus bagian responsive karena diatur oleh AlpineJS dan Tailwind di app.blade.php */
    /* Menghapus gaya logo karena sudah pindah */
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- BAGIAN 1: FUNGSI KLIK YANG DIPERBAIKI ---
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        dropdownToggles.forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                const parentMenuItem = this.closest('.menu-item.has-dropdown');

                // Cari semua dropdown yang sedang terbuka
                const currentlyOpen = document.querySelectorAll('.menu-item.has-dropdown.active');

                currentlyOpen.forEach(function (openItem) {
                    // Tutup item yang terbuka HANYA JIKA item itu BUKAN induk dari item yang diklik
                    if (openItem !== parentMenuItem && !openItem.contains(parentMenuItem)) {
                        openItem.classList.remove('active');
                    }
                });

                // Buka atau tutup item yang diklik
                parentMenuItem.classList.toggle('active');
            });
        });

        // --- BAGIAN 2: FUNGSI AUTO-OPEN YANG DIPERBAIKI ---
        const activeSubmenuItem = document.querySelector('.submenu .active-link');
        if (activeSubmenuItem) {
            let current = activeSubmenuItem;
            // Telusuri ke atas dari item yang aktif
            while (current) {
                // Temukan induk dropdown terdekat
                const parentDropdown = current.closest('.menu-item.has-dropdown');
                if (parentDropdown) {
                    // Aktifkan (buka) induk tersebut
                    parentDropdown.classList.add('active');
                    // Pindah ke elemen di atasnya untuk melanjutkan pencarian induk berikutnya
                    current = parentDropdown.parentElement;
                } else {
                    // Jika tidak ada lagi induk dropdown, hentikan pencarian
                    current = null;
                }
            }
        }
    });
</script>