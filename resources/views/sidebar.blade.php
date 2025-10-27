{{-- resources/views/sidebar.blade.php --}}
<aside class="sidebar">

    {{-- HEADER DARI FILE KEDUA --}}
    <div class="sidebar-header">
        <a href="{{ route('home') }}" class="sidebar-brand">
            <img src="{{ asset('logo.png') }}" alt="Logo Sitokcer">
            <span>Sitokcer</span>
        </a>
        <button class="btn btn-link p-0" type="button" id="sidebar-internal-toggle">
            <i class="bi bi-list"></i>
        </button>
    </div>

    {{-- DAFTAR MENU DARI FILE PERTAMA --}}
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

                <li class="menu-item has-dropdown {{ request()->is('sosial/triwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                         <i class="bi bi-chevron-right dropdown-arrow-icon"></i> 
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('sosial/triwulanan/seruti*') ? 'active-link' : '' }}">
                            <a href="{{ route('sosial.triwulanan.index', ['jenisKegiatan' => 'seruti']) }}">Seruti</a>
                        </li>
                    </ul>
                </li>

                <li class="menu-item has-dropdown {{ request()->routeIs('sosial.semesteran.*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
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

                        <li class="{{ request()->is('tim-produksi/caturwulanan/upp*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'ubinan']) }}">Ubinan
                                Padi Palawija</a>
                        </li>
                        <li class="{{ request()->is('tim-produksi/caturwulanan/uup*') ? 'active-link' : '' }}">
                            <a href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'updating utp']) }}">Updating UTP Palawija</a>
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

        @if(Auth::user()->role == 'admin')
        <li class="{{ request()->routeIs('users.*') ? 'active-link' : '' }}">
            <a href="{{ route('users.index') }}">
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Master User</span>
            </a>
        </li>
        @endif

    
    </ul>

    {{-- FOOTER DARI FILE KEDUA --}}
    <div class="sidebar-footer">
    <div class="user-profile-dropdown">
        
        {{-- Tombol untuk membuka/menutup dropdown --}}
        <button class="user-profile-btn" id="userProfileToggle">
            <div class="user-avatar"> 
                <i class="bi bi-person-circle"></i> 
            </div>
            <div class="user-info">
                {{-- Mengambil nama pengguna yang sedang login --}}
                <span class="user-name">{{ Auth::user()->name }}</span>
                {{-- Mengambil email pengguna yang sedang login --}}
                <span class="user-email">{{ Auth::user()->email }}</span>
            </div>
            <i class="bi bi-chevron-up profile-arrow"></i>
        </button>

        {{-- Menu Dropdown --}}
        <div class="user-dropdown-menu" id="userDropdownMenu">

            
            {{-- Tombol Logout (WAJIB MENGGUNAKAN FORM POST) --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                
                {{-- 
                  Link ini akan terlihat seperti link biasa, 
                  tapi 'onclick' akan men-submit form di atasnya.
                --}}
                <a href="{{ route('logout') }}" 
                   class="dropdown-item text-danger"
                   onclick="event.preventDefault(); this.closest('form').submit();">
                   
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Log out</span>
                </a>
            </form>

        </div>
    </div>
</div>
</aside>

{{-- POPUP DIV DARI FILE KEDUA --}}
<div id="sidebar-popup" class="sidebar-popup-menu" style="display: none;">
    <div class="sidebar-popup-header">
        <span id="popup-title" class="sidebar-popup-title"></span>
        <button id="popup-close-btn" class="sidebar-popup-close">&times;</button>
    </div>
    <div id="popup-content" class="sidebar-popup-content"></div>
</div>


{{-- STYLE DARI FILE KEDUA --}}
<style>
    /* ===== RESET & BASE ===== */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    /* ===== SIDEBAR CONTAINER ===== */
    .sidebar { width: 270px; background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); color: #fff; height: 100vh; box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15); overflow-y: auto; overflow-x: hidden; display: flex; flex-direction: column; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; }
    /* ===== SIDEBAR HEADER ===== */
    .sidebar-header { padding: 12px 16px; display: flex; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); justify-content: space-between; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); min-height: 56px; flex-shrink: 0; }
    .sidebar-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    .sidebar-brand img { height: 30px; width: 30px; object-fit: contain; flex-shrink: 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    .sidebar-brand span { font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 1.1rem; color: #fff; opacity: 1; white-space: nowrap; transition: opacity 0.3s ease, transform 0.3s ease; }
    #sidebar-internal-toggle { color: rgba(255, 255, 255, 0.7); font-size: 1.5rem; line-height: 1; transition: all 0.3s ease; flex-shrink: 0; background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; }
    #sidebar-internal-toggle:hover { color: #fff; background: rgba(255, 255, 255, 0.1); border-radius: 6px; }
    /* Scrollbar Styling */
    .sidebar::-webkit-scrollbar { width: 6px; }
    .sidebar::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.1); }
    .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 3px; }
    .sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.3); }
    /* ===== SIDEBAR MENU ===== */
    .sidebar-menu { list-style: none; padding: 10px 12px; flex-grow: 1; overflow-y: auto; }
    .sidebar-menu > li { margin-bottom: 4px; }
    /* ===== MENU LINKS ===== */
    .sidebar-menu li a { display: flex; align-items: center; gap: 12px; color: rgba(255, 255, 255, 0.85); text-decoration: none; padding: 11px 14px; border-radius: 8px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); font-size: 0.925rem; font-weight: 500; position: relative; white-space: nowrap; overflow: hidden; }
    .sidebar-menu > li > a:hover { background: rgba(255, 255, 255, 0.1); color: #fff; transform: translateX(3px); }
    .sidebar-menu > li > a::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 0; background: #3498db; border-radius: 0 3px 3px 0; transition: height 0.25s ease; }
    .sidebar-menu > li > a:hover::before { height: 65%; }
    .menu-icon { font-size: 1.15rem; width: 22px; text-align: center; flex-shrink: 0; opacity: 0.9; transition: all 0.3s ease; }
    .dropdown-toggle { width: 100%; cursor: pointer; position: relative; }
    .dropdown-toggle > span { flex-grow: 1; transition: opacity 0.3s ease; pointer-events: none; /* [BARU] Cegah span menangkap klik */ }
    .dropdown-arrow-icon { margin-left: auto; flex-shrink: 0; font-size: 0.8rem; color: rgba(255, 255, 255, 0.6); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none; /* [BARU] Cegah ikon panah menangkap klik */ }
    .submenu .dropdown-toggle .dropdown-arrow-icon { font-size: 0.75rem; }
    /* ===== SUBMENU ===== */
    .submenu { list-style: none; padding: 0; margin: 0 0 0 18px; border-left: 2px solid rgba(255, 255, 255, 0.12); max-height: 0; overflow: hidden; transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease, padding 0.4s ease; opacity: 0; }
    .submenu li { margin-bottom: 0; }
    .submenu a { font-size: 0.875rem; padding: 9px 14px; color: rgba(255, 255, 255, 0.75); font-weight: 400; border-radius: 6px; margin: 3px 0; gap: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; }
    .submenu a:hover { color: #fff; background: rgba(255, 255, 255, 0.08); transform: translateX(3px); }
    .submenu a::before { display: none; }
    .submenu .submenu { margin-left: 12px; border-left-color: rgba(255, 255, 255, 0.08); }
    .submenu .submenu a { font-size: 0.85rem; padding: 8px 12px; color: rgba(255, 255, 255, 0.7); }
    /* ===== ACTIVE STATES ===== */
    .menu-item.has-dropdown.active > .submenu { max-height: 2000px; padding: 6px 0; opacity: 1; }
    .menu-item.has-dropdown.active > .dropdown-toggle { background: rgba(255, 255, 255, 0.1); color: #fff; }
    .menu-item.has-dropdown.active > .dropdown-toggle .dropdown-arrow-icon { transform: rotate(90deg); color: rgba(255, 255, 255, 0.9); }
    .sidebar-menu li.active-link > a { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: #fff; box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3); font-weight: 600; }
    .sidebar-menu li.active-link > a:hover { background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%); transform: translateX(0); }
    .sidebar-menu li.active-link > a::before { height: 0; }
    .sidebar-menu li.active-link > a .menu-icon { opacity: 1; }
    .submenu li.active-link > a { color: #3498db; font-weight: 600; background: rgba(52, 152, 219, 0.12); }
    .submenu li.active-link > a:hover { background: rgba(52, 152, 219, 0.2); }
    /* ===== SIDEBAR FOOTER / USER PROFILE ===== */
    .sidebar-footer { padding: 8px 10px; border-top: 1px solid rgba(255, 255, 255, 0.1); flex-shrink: 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    .user-profile-dropdown { position: relative; }
    .user-profile-btn { width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; }
    .user-profile-btn:hover { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.2); }
    .user-avatar { width: 32px; height: 32px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); border-radius: 50%; font-size: 1.3rem; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    .user-avatar i { color: #fff; }
    .user-info { display: flex; flex-direction: column; align-items: flex-start; flex-grow: 1; overflow: hidden; transition: all 0.3s ease; }
    .user-name { font-size: 0.85rem; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; text-align: left; }
    .user-email { font-size: 0.7rem; color: rgba(255, 255, 255, 0.6); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; text-align: left; }
    .profile-arrow { font-size: 0.85rem; color: rgba(255, 255, 255, 0.6); flex-shrink: 0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .user-profile-dropdown.active .profile-arrow { transform: rotate(180deg); color: #fff; }
    .user-dropdown-menu { position: absolute; bottom: 100%; left: 0; right: 0; margin-bottom: 8px; background: #2c3e50; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 10px; box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.3); max-height: 0; overflow: hidden; opacity: 0; transform: translateY(10px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 1000; }
    .user-profile-dropdown.active .user-dropdown-menu { max-height: 500px; opacity: 1; transform: translateY(0); padding: 8px; }
    .dropdown-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 6px; transition: all 0.2s ease; font-size: 0.875rem; position: relative; }
    .dropdown-item:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
    .dropdown-item i { font-size: 1rem; width: 18px; text-align: center; opacity: 0.8; }
    .dropdown-item span:first-of-type { flex-grow: 1; }
    .dropdown-item .shortcut { font-size: 0.7rem; color: rgba(255, 255, 255, 0.5); background: rgba(255, 255, 255, 0.05); padding: 2px 6px; border-radius: 4px; margin-left: auto; }
    .dropdown-item .ml-auto { margin-left: auto; font-size: 0.75rem; opacity: 0.6; }
    .dropdown-item.text-danger { color: #e74c3c; }
    .dropdown-item.text-danger:hover { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
    .dropdown-divider { height: 1px; background: rgba(255, 255, 255, 0.1); margin: 6px 0; }

    /* ===== COLLAPSED STATE ===== */
    html.sidebar-collapsed .sidebar { width: 60px; }
    html.sidebar-collapsed .sidebar-header { padding: 10px 6px; justify-content: center; flex-direction: column; gap: 6px; }
    html.sidebar-collapsed .sidebar-brand { flex-direction: column; gap: 4px; }
    html.sidebar-collapsed .sidebar-brand img { opacity: 0; width: 0; height: 0; position: absolute; pointer-events: none; }
    html.sidebar-collapsed .sidebar-brand span { opacity: 0; transform: scale(0.8); position: absolute; pointer-events: none; }
    html.sidebar-collapsed #sidebar-internal-toggle { margin-left: 0; font-size: 1.4rem; }
    html.sidebar-collapsed .sidebar-menu { padding: 10px 6px; }
    html.sidebar-collapsed .sidebar-menu li a { justify-content: center; padding: 10px 6px; gap: 0; }
    html.sidebar-collapsed .sidebar-menu li a span { opacity: 0; transform: scale(0.8); position: absolute; pointer-events: none; width: 0; }
    html.sidebar-collapsed .sidebar-menu li a::before { display: none; }
    html.sidebar-collapsed .dropdown-arrow-icon { opacity: 0; transform: scale(0.8); position: absolute; pointer-events: none; width: 0; }
    html.sidebar-collapsed .menu-icon { font-size: 0.9rem; }
    html.sidebar-collapsed .submenu { display: none !important; max-height: 0 !important; padding: 0 !important; margin: 0 !important; opacity: 0 !important; border: none !important; }
    html.sidebar-collapsed .menu-item.has-dropdown.active > .dropdown-toggle { background: rgba(255, 255, 255, 0.05); }
    html.sidebar-collapsed .sidebar-menu li.active-link > a { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3); }
    html.sidebar-collapsed .sidebar-menu li.active-link > a:hover { background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%); transform: translateX(0); }
    html.sidebar-collapsed .sidebar-menu > li > a:hover { transform: translateX(0); }
    html.sidebar-collapsed .sidebar-footer { padding: 6px; }
    html.sidebar-collapsed .user-profile-btn { padding: 6px; justify-content: center; }
    html.sidebar-collapsed .user-avatar { width: 32px; height: 32px; font-size: 1.1rem; }
    html.sidebar-collapsed .user-info { opacity: 0; width: 0; padding: 0; margin: 0; position: absolute; pointer-events: none; }
    html.sidebar-collapsed .profile-arrow { opacity: 0; width: 0; position: absolute; pointer-events: none; }
    html.sidebar-collapsed .user-dropdown-menu { display: none; }

    /* ===== CSS UNTUK POPUP MENU SAAT COLLAPSED ===== */
    .sidebar-popup-menu { position: fixed; left: 65px; background: #2c3e50; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3); min-width: 200px; max-width: 250px; z-index: 10000; opacity: 1; transition: opacity 0.2s ease, transform 0.2s ease; }
    .sidebar-popup-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .sidebar-popup-title { font-size: 0.9rem; font-weight: 600; color: #fff; }
    .sidebar-popup-close { background: none; border: none; color: rgba(255, 255, 255, 0.7); font-size: 1.5rem; line-height: 1; cursor: pointer; padding: 0 4px; }
    .sidebar-popup-close:hover { color: #fff; }
    .sidebar-popup-content { padding: 8px; max-height: 400px; overflow-y: auto; }
    .sidebar-popup-content ul, .sidebar-popup-content li { list-style: none !important; margin: 0 !important; padding: 0 !important; border: none !important; }
    .sidebar-popup-content li a { display: flex; align-items: center; white-space: normal; overflow: visible; padding: 9px 14px; border-radius: 6px; margin: 3px 0; gap: 10px; transform: none !important; position: relative; color: rgba(255, 255, 255, 0.75); font-weight: 400; transition: background-color 0.2s ease, color 0.2s ease; }
    .sidebar-popup-content li a:hover { color: #fff; background: rgba(255, 255, 255, 0.08); transform: none !important; }
    .sidebar-popup-content li.active-link > a { color: #3498db; font-weight: 600; background: rgba(52, 152, 219, 0.12); }
    .sidebar-popup-content li.active-link > a:hover { background: rgba(52, 152, 219, 0.2); }
    

    /* Scrollbar */
    .sidebar-popup-content::-webkit-scrollbar { width: 4px; }
    .sidebar-popup-content::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.1); }
    .sidebar-popup-content::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 2px; }

    /* RESPONSIVE & UTILITIES */
    @media (max-width: 768px) { .sidebar { width: 100%; } html.sidebar-collapsed .sidebar { width: 70px; } }
    .sidebar-menu a.dropdown-toggle::after { display: none; }
    .sidebar * { transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
</style>

{{-- SCRIPT DARI FILE KEDUA --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.documentElement; 
        const toggleButton = document.getElementById('sidebar-internal-toggle');
        const userProfileToggle = document.getElementById('userProfileToggle');
        const userProfileDropdown = document.querySelector('.user-profile-dropdown');
        
        const popup = document.getElementById('sidebar-popup');
        const popupTitle = document.getElementById('popup-title');
        const popupContent = document.getElementById('popup-content');
        const popupCloseBtn = document.getElementById('popup-close-btn');

        let isInitialLoad = true;
        let activePopupMenuItem = null;

        // Toggle Sidebar Collapse
        if (toggleButton && wrapper) {
            toggleButton.addEventListener('click', () => {
                wrapper.classList.toggle('sidebar-collapsed'); 
                const isCollapsed = wrapper.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
        }
        
        // Logika untuk Menutup Popup
        function closePopup() {
            if (popup) {
                popup.style.display = 'none';
                activePopupMenuItem = null; 
            }
        }

        if (popupCloseBtn) { popupCloseBtn.addEventListener('click', closePopup); }
        
        // Logika tutup popup saat klik di luar
        document.addEventListener('click', function(e) {
            if (popup && popup.style.display === 'block' && !popup.contains(e.target) && !e.target.closest('#sidebar-internal-toggle') && !e.target.closest('.sidebar-menu > li > .dropdown-toggle')) {
                closePopup();
            }
            // Logika tutup user dropdown
            if (userProfileDropdown && !userProfileDropdown.contains(e.target)) {
                userProfileDropdown.classList.remove('active');
            }
        });

        // Toggle User Profile Dropdown
        if (userProfileToggle && userProfileDropdown) {
             userProfileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                userProfileDropdown.classList.toggle('active');
            });
        }
        
        // Logika Dropdown Toggle UTAMA (sidebar asli)
        const mainDropdownToggles = document.querySelectorAll('.sidebar-menu > li > .dropdown-toggle, .sidebar-menu > li > .submenu > li > .dropdown-toggle'); 
        mainDropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                if (wrapper && wrapper.classList.contains('sidebar-collapsed')) {
                    // --- LOGIKA POPUP SAAT COLLAPSED ---
                    e.preventDefault(); e.stopPropagation();
                    const parentLi = this.closest('.menu-item.has-dropdown');
                    if (!parentLi) return; 
                    const submenu = parentLi.querySelector(':scope > .submenu'); 
                    const menuTitleSpan = this.querySelector(':scope > span');
                    const menuTitle = menuTitleSpan ? menuTitleSpan.textContent : 'Menu'; 
                    if (!submenu) return; 
                    if (popup.style.display === 'block' && activePopupMenuItem === parentLi) { closePopup(); return; }
                    if (popup.style.display === 'block'){ closePopup(); }
                    popupTitle.textContent = menuTitle;
                    popupContent.innerHTML = ''; 

                    
                    // Fungsi rekursif untuk "meratakan" semua link
                    function flattenAndAppendLinks(sourceUl, targetElement) {
                        if (!sourceUl) return;
                        Array.from(sourceUl.children).forEach(li => {
                            // Cek apakah <li> ini adalah dropdown
                            if (li.classList.contains('menu-item') && li.classList.contains('has-dropdown')) {
                                const nestedSubmenu = li.querySelector(':scope > .submenu');
                                if (nestedSubmenu) {
                                    flattenAndAppendLinks(nestedSubmenu, targetElement);
                                }
                            } 
                            else if (li.tagName === 'LI' && li.querySelector('a')) {
                                const clonedLi = li.cloneNode(true);                        
                                clonedLi.classList.remove('active');
                                clonedLi.querySelectorAll('.active').forEach(el => el.classList.remove('active'));
                                const arrow = clonedLi.querySelector('.dropdown-arrow-icon');
                                if (arrow) arrow.remove();
                                clonedLi.classList.remove('has-dropdown');
                                const link = clonedLi.querySelector('a');
                                if (link) link.classList.remove('dropdown-toggle');

                                targetElement.appendChild(clonedLi);
                            }
                        });
                    }
                    
                    flattenAndAppendLinks(submenu, popupContent);


                    const iconRect = this.getBoundingClientRect();
                    popup.style.top = `${iconRect.top}px`; 
                    popup.style.left = `65px`; 
                    popup.style.display = 'block';
                    activePopupMenuItem = parentLi;

                    requestAnimationFrame(() => {
                        const popupRect = popup.getBoundingClientRect();
                        const viewportHeight = window.innerHeight;
                        if (popupRect.bottom > viewportHeight - 10) {
                            const newTop = Math.max(10, viewportHeight - popupRect.height - 10);
                            popup.style.top = `${newTop}px`;
                        }
                    });
                    
                    popupContent.scrollTop = 0;
                } else {
                    // --- LOGIKA ACCORDION BIASA ---
                    e.preventDefault(); e.stopPropagation(); closePopup(); 
                    const parentMenuItem = this.closest('.menu-item.has-dropdown');
                    if (!parentMenuItem) return; 
                    const parentLevel = parentMenuItem.parentElement;
                    const siblings = parentLevel.querySelectorAll(':scope > .menu-item.has-dropdown.active');
                    siblings.forEach(sibling => { if (sibling !== parentMenuItem) { sibling.classList.remove('active'); } });
                    parentMenuItem.classList.toggle('active');
                }
            });
        });

        if (popupContent) {
            popupContent.addEventListener('click', function(e){

                const link = e.target.closest('a');
                if (link && popupContent.contains(link)) {

                    closePopup(); 
                }
            });
        }


        const activeSubmenuItem = document.querySelector('.sidebar-menu .submenu .active-link');
        if (activeSubmenuItem && wrapper && !wrapper.classList.contains('sidebar-collapsed')) {
             let current = activeSubmenuItem.closest('.menu-item.has-dropdown');
             while (current) {
                current.classList.add('active');
                current = current.parentElement.closest('.menu-item.has-dropdown');
             }
        }

        isInitialLoad = false;


        if (wrapper) {
            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    if (mutation.attributeName === 'class' && !isInitialLoad) {
                        if (!wrapper.classList.contains('sidebar-collapsed')) {
                            closePopup();
                        }
                    }
                });
            });
            observer.observe(wrapper, { attributes: true });
        }
    });
</script>