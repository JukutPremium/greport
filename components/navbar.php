<nav class="bg-white shadow-lg relative z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo & Brand -->
            <a href="index.php" class="text-lg font-bold text-blue-600 flex-shrink-0">
                Pengaduan Sekolah
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg font-medium transition text-sm">Dashboard</a>
                <?php if (isSiswa()): ?>
                    <a href="form_aspirasi.php" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg font-medium transition text-sm">Buat Aspirasi</a>
                <?php endif; ?>
                <a href="list_aspirasi.php" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg font-medium transition text-sm">List Aspirasi</a>
                <?php if (isSiswa()): ?>
                    <a href="histori_aspirasi.php" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg font-medium transition text-sm">Histori Saya</a>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                    <a href="manage_users.php" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg font-medium transition text-sm">Kelola User</a>
                    <a href="manage_kategori.php" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg font-medium transition text-sm">Kelola Kategori</a>
                <?php endif; ?>
            </div>

            <!-- Desktop User Info & Logout -->
            <div class="hidden md:flex items-center space-x-3">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-800 leading-tight"><?= escapeHtml($_SESSION['nama']) ?></p>
                    <p class="text-xs text-gray-500"><?= ucfirst($_SESSION['role']) ?><?= $_SESSION['kelas'] ? ' - ' . $_SESSION['kelas'] : '' ?></p>
                </div>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex-shrink-0">Logout</a>
            </div>

            <!-- Mobile Hamburger Button -->
            <button id="hamburgerBtn" onclick="toggleMobileMenu()" class="md:hidden flex flex-col justify-center items-center w-10 h-10 rounded-lg hover:bg-gray-100 transition focus:outline-none" aria-label="Toggle menu">
                <span id="bar1" class="block w-6 h-0.5 bg-gray-700 transition-all duration-300 mb-1.5"></span>
                <span id="bar2" class="block w-6 h-0.5 bg-gray-700 transition-all duration-300 mb-1.5"></span>
                <span id="bar3" class="block w-6 h-0.5 bg-gray-700 transition-all duration-300"></span>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 bg-white shadow-lg">
        <!-- User Info Mobile -->
        <div class="px-4 py-3 bg-blue-50 border-b border-blue-100">
            <p class="text-sm font-semibold text-gray-800"><?= escapeHtml($_SESSION['nama']) ?></p>
            <p class="text-xs text-gray-500"><?= ucfirst($_SESSION['role']) ?><?= $_SESSION['kelas'] ? ' · ' . $_SESSION['kelas'] : '' ?></p>
        </div>

        <!-- Nav Links Mobile -->
        <div class="px-2 py-3 space-y-1">
            <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition text-sm">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <?php if (isSiswa()): ?>
                <a href="form_aspirasi.php" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition text-sm">
                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Aspirasi
                </a>
            <?php endif; ?>
            <a href="list_aspirasi.php" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition text-sm">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                List Aspirasi
            </a>
            <?php if (isSiswa()): ?>
                <a href="histori_aspirasi.php" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition text-sm">
                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Histori Saya
                </a>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <a href="manage_users.php" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition text-sm">
                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Kelola User
                </a>
                <a href="manage_kategori.php" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition text-sm">
                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Kelola Kategori
                </a>
            <?php endif; ?>
        </div>

        <!-- Logout Mobile -->
        <div class="px-4 py-3 border-t border-gray-200">
            <a href="logout.php" class="flex items-center justify-center w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Logout
            </a>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const bar1 = document.getElementById('bar1');
    const bar2 = document.getElementById('bar2');
    const bar3 = document.getElementById('bar3');
    const isOpen = !menu.classList.contains('hidden');
    if (isOpen) {
        menu.classList.add('hidden');
        bar1.style.transform = '';
        bar2.style.opacity = '1';
        bar3.style.transform = '';
    } else {
        menu.classList.remove('hidden');
        bar1.style.transform = 'translateY(8px) rotate(45deg)';
        bar2.style.opacity = '0';
        bar3.style.transform = 'translateY(-8px) rotate(-45deg)';
    }
}
document.addEventListener('click', function(e) {
    const nav = document.querySelector('nav');
    const menu = document.getElementById('mobileMenu');
    if (nav && !nav.contains(e.target) && !menu.classList.contains('hidden')) {
        toggleMobileMenu();
    }
});
</script>
