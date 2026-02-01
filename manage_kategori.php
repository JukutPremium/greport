<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

// Proses Tambah Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kategori'])) {
    $namaKategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    
    $query = "INSERT INTO kategori (nama_kategori) VALUES ('$namaKategori')";
    
    if (mysqli_query($conn, $query)) {
        alert('Kategori berhasil ditambahkan!', 'success');
        redirect('manage_kategori.php');
    } else {
        $error = "Gagal menambahkan kategori: " . mysqli_error($conn);
    }
}

// Proses Edit Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_kategori'])) {
    $id = intval($_POST['kategori_id']);
    $namaKategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    
    $query = "UPDATE kategori SET nama_kategori = '$namaKategori' WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        alert('Kategori berhasil diupdate!', 'success');
        redirect('manage_kategori.php');
    } else {
        $error = "Gagal mengupdate kategori: " . mysqli_error($conn);
    }
}

// Proses Hapus Kategori
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Cek apakah kategori masih digunakan
    $checkQuery = "SELECT COUNT(*) as total FROM aspirasi WHERE kategori_id = $id";
    $checkResult = mysqli_query($conn, $checkQuery);
    $check = mysqli_fetch_assoc($checkResult);
    
    if ($check['total'] > 0) {
        alert('Kategori tidak dapat dihapus karena masih digunakan pada ' . $check['total'] . ' aspirasi!', 'error');
        redirect('manage_kategori.php');
    }
    
    $query = "DELETE FROM kategori WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        alert('Kategori berhasil dihapus!', 'success');
    } else {
        alert('Gagal menghapus kategori!', 'error');
    }
    redirect('manage_kategori.php');
}

// Ambil semua kategori dengan statistik penggunaan
$queryKategori = "SELECT k.*, 
                  (SELECT COUNT(*) FROM aspirasi WHERE kategori_id = k.id) as jumlah_penggunaan
                  FROM kategori k 
                  ORDER BY k.nama_kategori ASC";
$kategoriList = mysqli_query($conn, $queryKategori);

// Ambil kategori untuk edit jika ada
$editKategori = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editResult = mysqli_query($conn, "SELECT * FROM kategori WHERE id = $editId");
    $editKategori = mysqli_fetch_assoc($editResult);
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
    
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <?php showAlert(); ?>
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Manajemen Kategori</h1>
            <p class="text-gray-600">Kelola kategori pengaduan sarana dan prasarana</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah/Edit Kategori -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <?= $editKategori ? 'Edit Kategori' : 'Tambah Kategori' ?>
                    </h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?php if ($editKategori): ?>
                            <input type="hidden" name="kategori_id" value="<?= $editKategori['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                Nama Kategori <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="nama_kategori" 
                                required
                                value="<?= $editKategori ? escapeHtml($editKategori['nama_kategori']) : '' ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Contoh: Ruang Kelas"
                            >
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <button 
                                type="submit" 
                                name="<?= $editKategori ? 'edit_kategori' : 'tambah_kategori' ?>"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                            >
                                <?= $editKategori ? 'Update Kategori' : 'Tambah Kategori' ?>
                            </button>
                            <?php if ($editKategori): ?>
                                <a href="manage_kategori.php" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium text-center transition">
                                    Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Daftar Kategori -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Daftar Kategori</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if (mysqli_num_rows($kategoriList) > 0): ?>
                            <div class="grid grid-cols-1 gap-4">
                                <?php while ($kat = mysqli_fetch_assoc($kategoriList)): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                                    <?= escapeHtml($kat['nama_kategori']) ?>
                                                </h3>
                                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        <?= $kat['jumlah_penggunaan'] ?> Aspirasi
                                                    </span>
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <?= date('d/m/Y', strtotime($kat['created_at'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex gap-2 ml-4">
                                                <a href="manage_kategori.php?edit=<?= $kat['id'] ?>" 
                                                   class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded text-sm font-medium transition">
                                                    Edit
                                                </a>
                                                <?php if ($kat['jumlah_penggunaan'] == 0): ?>
                                                    <a href="manage_kategori.php?hapus=<?= $kat['id'] ?>" 
                                                       onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                                       class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded text-sm font-medium transition">
                                                        Hapus
                                                    </a>
                                                <?php else: ?>
                                                    <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded text-sm font-medium cursor-not-allowed" 
                                                          title="Tidak dapat dihapus karena masih digunakan">
                                                        Hapus
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <p class="text-gray-500 text-lg mb-4">Belum ada kategori</p>
                                <p class="text-gray-400 text-sm">Tambahkan kategori pertama menggunakan form di samping</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Informasi Tambahan -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informasi
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-1">
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