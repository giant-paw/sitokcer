document.addEventListener('DOMContentLoaded', function () {
    // --- FUNGSI KLIK DROPDOWN SIDEBAR ---
    const dropdownToggles = document.querySelectorAll('.sidebar .dropdown-toggle');

    dropdownToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            const parentMenuItem = this.closest('.menu-item.has-dropdown');
            const currentlyOpen = document.querySelectorAll('.sidebar .menu-item.has-dropdown.active');

            currentlyOpen.forEach(function (openItem) {
                if (openItem !== parentMenuItem && !openItem.contains(parentMenuItem)) {
                    openItem.classList.remove('active');
                }
            });

            parentMenuItem.classList.toggle('active');
        });
    });

    // --- FUNGSI AUTO-OPEN MENU AKTIF ---
    const activeSubmenuItem = document.querySelector('.sidebar .submenu .active-link');
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