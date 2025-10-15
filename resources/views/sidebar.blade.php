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
                <i class="bi bi-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('dashboard-distribusi') ? 'active-link' : '' }}"><a href="{{ route('dashboard.distribusi') }}">Distribusi</a></li>
                <li class="{{ request()->is('dashboard-nwa') ? 'active-link' : '' }}"><a href="{{ route('dashboard.nwa') }}">NWA</a></li>
                <li class="{{ request()->is('dashboard-produksi') ? 'active-link' : '' }}"><a href="{{ route('dashboard.produksi') }}">Produksi</a></li>
                <li class="{{ request()->is('dashboard-sosial') ? 'active-link' : '' }}"><a href="{{ route('dashboard.sosial') }}">Sosial</a></li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('sosial*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Tim Sosial</span>
                <i class="bi bi-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('sosial/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('sosial.tahunan.index') }}">Sosial Tahunan</a>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('sosial/kegiatan-triwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                        <i class="bi bi-chevron-right arrow"></i>
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
                        <i class="bi bi-chevron-right arrow"></i>
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

        <li class="menu-item has-dropdown {{ request()->is('distribusi*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-truck menu-icon"></i>
                <span>Tim Distribusi</span>
                <i class="bi bi-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('distribusi/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('distribusi.tahunan') }}">Distribusi Tahunan</a>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('distribusi/kegiatan-triwulan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                        <i class="bi bi-chevron-right arrow"></i>
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
                        <i class="bi bi-chevron-right arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('distribusi/bulanan/vhts*') ? 'active-link' : '' }}"><a href="{{ route('distribusi.vhts') }}">VHTS</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/hkd*') ? 'active-link' : '' }}"><a href="{{ route('distribusi.hkd') }}">HKD</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/shpb*') ? 'active-link' : '' }}"><a href="{{ route('distribusi.shpb') }}">SHPB</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/shp*') ? 'active-link' : '' }}"><a href="{{ route('distribusi.shp') }}">SHP</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/shpj*') ? 'active-link' : '' }}"><a href="{{ route('distribusi.shpj') }}">SHPJ</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/shpgb*') ? 'active-link' : '' }}"><a href="{{ route('distribusi.shpgb') }}">SHPBG</a></li>
                        <li class="{{ request()->is('distribusi/bulanan/hd*') ? 'active-link' : '' }}"><a href="{{ route('distribusi.hd') }}">HD</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('produksi*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-graph-up-arrow menu-icon"></i>
                <span>Tim Produksi</span>
                <i class="bi bi-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('produksi/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('produksi.tahunan') }}">Produksi Tahunan</a>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('produksi/kegiatan-caturwulan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Caturwulan</span>
                        <i class="bi bi-chevron-right arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('produksi/kegiatan-caturwulan/ubinan-padi-palawija*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.ubinanpadipalawija') }}">Ubinan Padi Palawija</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-caturwulan/update-utp-palawija*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.updateingutppalawija') }}">Update UTP Palawija</a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('produksi/kegiatan-triwulan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Triwulan</span>
                        <i class="bi bi-chevron-right arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/sktr*') ? 'active-link' : '' }}"><a href="{{ route('produksi.sktr') }}">SKTR</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/tpi*') ? 'active-link' : '' }}"><a href="{{ route('produksi.tpi') }}">TPI</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/sphbst*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.sphbst') }}">SPHBST</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/sphtbf*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.sphtbf') }}">SPHTBF</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/sphth*') ? 'active-link' : '' }}"><a href="{{ route('produksi.sphth') }}">SPHTH</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-triwulan/air-bersih*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.airbersih') }}">Air Bersih</a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('produksi/kegiatan-bulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>Kegiatan Bulanan</span>
                        <i class="bi bi-chevron-right arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/ksapadi*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.ksapadi') }}">KSA Padi</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/ksajagung*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.ksajagung') }}">KSA Jagung</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/lptb*') ? 'active-link' : '' }}"><a href="{{ route('produksi.lptb') }}">LPTB</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/sphsbs*') ? 'active-link' : '' }}"><a href="{{ route('produksi.sphsbs') }}">SPHSBS</a></li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/sppalawija*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.sppalawija') }}">SP Palawija</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/perkebunan*') ? 'active-link' : '' }}">
                            <a href="{{ route('produksi.perkebunanbulanan') }}">Perkebunan Bulanan</a>
                        </li>
                        <li class="{{ request()->is('produksi/kegiatan-bulanan/ibs*') ? 'active-link' : '' }}"><a href="{{ route('produksi.ibsbulanan') }}">IBS Bulanan</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="menu-item has-dropdown {{ request()->is('nwa*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle">
                <i class="bi bi-bar-chart-line-fill menu-icon"></i>
                <span>Tim NWA</span>
                <i class="bi bi-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li class="{{ request()->is('nwa/tahunan*') ? 'active-link' : '' }}">
                    <a href="{{ route('nwa.tahunan') }}">NWA Tahunan</a>
                </li>
                <li class="menu-item has-dropdown {{ request()->is('nwa/triwulanan*') ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle">
                        <span>NWA Triwulanan</span>
                        <i class="bi bi-chevron-right arrow"></i>
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
                <i class="bi bi-file-earmark-text-fill menu-icon"></i>
                <span>Rekapitulasi</span>
                <i class="bi bi-chevron-right arrow"></i>
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
    * { margin: 0; padding: 0; box-sizing: border-box; }
    .sidebar { width: 270px; background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); color: white; height: 100%; padding: 0; box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15); position: relative; overflow-y: auto; display: flex; flex-direction: column; }
    .sidebar::-webkit-scrollbar { width: 6px; }
    .sidebar::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.1); }
    .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 3px; }
    .sidebar-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .sidebar-logo-link { display: flex; align-items: center; text-decoration: none; }
    .sidebar-logo { height: 40px; width: 40px; object-fit: contain; }
    .sidebar-title { font-size: 1.5rem; font-weight: 700; color: white; margin: 0 0 0 0.75rem; font-family: 'Poppins', sans-serif; }
    .sidebar-menu { list-style: none; padding: 15px 12px; flex-grow: 1; }
    .sidebar-menu>li { margin-bottom: 6px; }
    .sidebar-menu li a { display: flex; align-items: center; gap: 12px; color: rgba(255, 255, 255, 0.85); text-decoration: none; padding: 12px 16px; border-radius: 8px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); font-size: 0.95rem; font-weight: 500; position: relative; }
    .sidebar-menu>li>a:hover { background: rgba(255, 255, 255, 0.1); color: #fff; transform: translateX(4px); }
    .sidebar-menu>li>a::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 0; background: #3498db; border-radius: 0 3px 3px 0; transition: height 0.3s ease; }
    .sidebar-menu>li>a:hover::before { height: 70%; }
    .menu-icon { font-size: 1.2rem; width: 24px; text-align: center; flex-shrink: 0; opacity: 0.9; }
    .menu-item.has-dropdown>a { cursor: pointer; }
    .dropdown-toggle { width: 100%; justify-content: space-between; }
    .dropdown-toggle span { flex-grow: 1; }
    .arrow { font-size: 0.8rem; margin-left: auto; transition: transform 0.3s ease; flex-shrink: 0; opacity: 0.7; }
    .submenu { list-style: none; padding: 0; margin: 0; margin-left: 20px; border-left: 2px solid rgba(255, 255, 255, 0.15); max-height: 0; overflow: hidden; transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    .submenu li { margin-bottom: 0; }
    .submenu a { font-size: 0.88rem; padding: 10px 16px; color: rgba(255, 255, 255, 0.7); font-weight: 400; border-radius: 6px; margin: 4px 0; }
    .submenu a:hover { color: #fff; background: rgba(255, 255, 255, 0.08); transform: translateX(4px); }
    .submenu a::before { display: none; }
    .menu-item.has-dropdown.active>.submenu { max-height: 500px; padding: 8px 0; }
    .menu-item.has-dropdown.active>.dropdown-toggle { background: rgba(255, 255, 255, 0.12); color: #fff; }
    .menu-item.has-dropdown.active .arrow { transform: rotate(90deg); opacity: 1; }
    .sidebar-menu li.active-link>a { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: #ffffff; box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3); }
    .sidebar-menu li.active-link>a:hover { background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%); transform: translateX(0); }
    .sidebar-menu li.active-link>a::before { height: 0; }
    .submenu li.active-link>a { color: #3498db; font-weight: 600; background: rgba(52, 152, 219, 0.15); }
    .submenu li.active-link>a:hover { background: rgba(52, 152, 219, 0.25); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.sidebar .dropdown-toggle');
        dropdownToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(event) {
                event.preventDefault();
                const parentMenuItem = this.closest('.menu-item.has-dropdown');
                const currentlyOpen = document.querySelectorAll('.menu-item.has-dropdown.active');
                currentlyOpen.forEach(function(openItem) {
                    if (openItem !== parentMenuItem && !openItem.contains(parentMenuItem)) {
                        openItem.classList.remove('active');
                    }
                });
                parentMenuItem.classList.toggle('active');
            });
        });
        const activeSubmenuItem = document.querySelector('.submenu .active-link');
        if (activeSubmenuItem) {
            let current = activeSubmenuItem;
            while (current) {
                const parentDropdown = current.closest('.menu-item.has-dropdown');
                if (parentDropdown) {
                    parentDropdown.classList.add('active');
                    current = parentDropdown.parentElement;
                } else {
                    current = null;
                }
            }
        }
    });
</script>