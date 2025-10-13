<aside class="sidebar">
    <div class="sidebar-header">
        <h3>Menu Navigasi</h3>
    </div>
    <ul class="sidebar-menu">
        <li><a href="/">Dashboard</a></li>
        <li><a href="/produk">Produk</a></li>
        <li><a href="/laporan">Laporan</a></li>
        <li><a href="/pengaturan">Pengaturan</a></li>
    </ul>
</aside>

<style>
    .sidebar {
        width: 250px;
        background-color: #2c3e50;
        color: white;
        height: 100vh;
        padding: 20px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        flex-shrink: 0; /* Mencegah sidebar menyusut */
    }
    .sidebar-header h3 {
        margin-top: 0;
        text-align: center;
    }
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .sidebar-menu li a {
        display: block;
        color: white;
        text-decoration: none;
        padding: 12px 15px;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    .sidebar-menu li a:hover {
        background-color: #34495e;
    }
</style>
