<nav class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-8">
                <a href="index.php" class="text-xl font-bold text-blue-600">
                    Pengaduan Sekolah
                </a>
                <div class="hidden md:flex space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                        Dashboard
                    </a>
                    <?php if (isSiswa()): ?>
                        <a href="form_aspirasi.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                            Buat Aspirasi
                        </a>
                    <?php endif; ?>
                    <a href="list_aspirasi.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                        List Aspirasi
                    </a>
                    <?php if (isSiswa()): ?>
                        <a href="histori_aspirasi.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                            Histori Saya
                        </a>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <a href="manage_users.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                            Kelola User
                        </a>
                        <a href="manage_kategori.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                            Kelola Kategori
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-medium text-gray-800"><?= escapeHtml($_SESSION['nama']) ?></p>
                    <p class="text-xs text-gray-500"><?= ucfirst($_SESSION['role']) ?><?= $_SESSION['kelas'] ? ' - ' . $_SESSION['kelas'] : '' ?></p>
                </div>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Logout
                </a>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="md:hidden pb-4">
            <div class="flex flex-col space-y-2">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                    Dashboard
                </a>
                <?php if (isSiswa()): ?>
                    <a href="form_aspirasi.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                        Buat Aspirasi
                    </a>
                <?php endif; ?>
                <a href="list_aspirasi.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                    List Aspirasi
                </a>
                <?php if (isSiswa()): ?>
                    <a href="histori_aspirasi.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                        Histori Saya
                    </a>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                    <a href="manage_users.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                        Kelola User
                    </a>
                    <a href="manage_kategori.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                        Kelola Kategori
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>