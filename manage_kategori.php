<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kategori'])) {
    $namaKategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    if (mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$namaKategori')")) {
        alert('Kategori berhasil ditambahkan!', 'success');
        redirect('manage_kategori.php');
    } else {
        $error = "Gagal menambahkan kategori: " . mysqli_error($conn);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_kategori'])) {
    $id = intval($_POST['kategori_id']);
    $namaKategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    if (mysqli_query($conn, "UPDATE kategori SET nama_kategori = '$namaKategori' WHERE id = $id")) {
        alert('Kategori berhasil diupdate!', 'success');
        redirect('manage_kategori.php');
    } else {
        $error = "Gagal mengupdate kategori: " . mysqli_error($conn);
    }
}

if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi WHERE kategori_id = $id"));
    if ($check['total'] > 0) {
        alert('Kategori tidak dapat dihapus karena masih digunakan pada ' . $check['total'] . ' aspirasi!', 'error');
    } else {
        if (mysqli_query($conn, "DELETE FROM kategori WHERE id = $id")) {
            alert('Kategori berhasil dihapus!', 'success');
        } else {
            alert('Gagal menghapus kategori!', 'error');
        }
    }
    redirect('manage_kategori.php');
}

$kategoriList = mysqli_query($conn, "SELECT k.*, (SELECT COUNT(*) FROM aspirasi WHERE kategori_id = k.id) as jumlah_penggunaan FROM kategori k ORDER BY k.nama_kategori ASC");

$editKategori = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editKategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kategori WHERE id = $editId"));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Aplikasi Pengaduan Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-6 md:py-8 max-w-5xl">
        <?php showAlert(); ?>
        
        <div class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-1">Manajemen Kategori</h1>
            <p class="text-gray-600 text-sm md:text-base">Kelola kategori pengaduan sarana dan prasarana</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 md:gap-6">
            <!-- Form Tambah/Edit -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-5 md:p-6 lg:sticky lg:top-4">
                    <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">
                        <?= $editKategori ? 'Edit Kategori' : 'Tambah Kategori' ?>
                    </h2>
                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <?php if ($editKategori): ?>
                            <input type="hidden" name="kategori_id" value="<?= $editKategori['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Kategori <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_kategori" required
                                value="<?= $editKategori ? escapeHtml($editKategori['nama_kategori']) : '' ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                placeholder="Contoh: Ruang Kelas">
                        </div>
                        <div class="flex flex-col gap-2">
                            <button type="submit" name="<?= $editKategori ? 'edit_kategori' : 'tambah_kategori' ?>"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition text-sm">
                                <?= $editKategori ? 'Update Kategori' : 'Tambah Kategori' ?>
                            </button>
                            <?php if ($editKategori): ?>
                                <a href="manage_kategori.php" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2.5 rounded-lg font-medium text-center transition text-sm">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Daftar Kategori -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="px-5 md:px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg md:text-xl font-semibold text-gray-800">Daftar Kategori</h2>
                    </div>
                    <div class="p-5 md:p-6">
                        <?php if (mysqli_num_rows($kategoriList) > 0): ?>
                            <div class="space-y-3 md:space-y-4">
                                <?php while ($kat = mysqli_fetch_assoc($kategoriList)): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                        <div class="flex justify-between items-start gap-3">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-base font-semibold text-gray-800 mb-1"><?= escapeHtml($kat['nama_kategori']) ?></h3>
                                                <div class="flex flex-wrap gap-3 text-xs text-gray-600">
                                                    <span>📄 <?= $kat['jumlah_penggunaan'] ?> Aspirasi</span>
                                                    <span>📅 <?= date('d/m/Y', strtotime($kat['created_at'])) ?></span>
                                                </div>
                                            </div>
                                            <div class="flex gap-2 flex-shrink-0">
                                                <a href="manage_kategori.php?edit=<?= $kat['id'] ?>" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded text-xs font-medium transition">Edit</a>
                                                <?php if ($kat['jumlah_penggunaan'] == 0): ?>
                                                    <a href="manage_kategori.php?hapus=<?= $kat['id'] ?>" onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                                       class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1.5 rounded text-xs font-medium transition">Hapus</a>
                                                <?php else: ?>
                                                    <span class="bg-gray-100 text-gray-400 px-3 py-1.5 rounded text-xs font-medium cursor-not-allowed" title="Masih digunakan">Hapus</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-10 md:py-12">
                                <svg class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <p class="text-gray-500 text-base md:text-lg mb-2">Belum ada kategori</p>
                                <p class="text-gray-400 text-sm">Tambahkan kategori menggunakan form di atas</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-5 md:mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <h3 class="font-semibold text-blue-900 mb-2 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Informasi
                    </h3>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>• Kategori digunakan untuk mengelompokkan jenis pengaduan</li>
                        <li>• Kategori yang masih digunakan tidak dapat dihapus</li>
                        <li>• Pastikan nama kategori jelas dan mudah dipahami</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
