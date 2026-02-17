<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$filterTanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$filterBulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filterSiswa = isset($_GET['siswa']) ? $_GET['siswa'] : '';
$filterKategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';

if (isAdmin()) {
    $query = "SELECT a.*, u.nama, u.kelas, k.nama_kategori FROM aspirasi a JOIN users u ON a.user_id = u.id JOIN kategori k ON a.kategori_id = k.id WHERE 1=1";
} else {
    $query = "SELECT a.*, k.nama_kategori FROM aspirasi a JOIN kategori k ON a.kategori_id = k.id WHERE a.user_id = $userId";
}

if ($filterTanggal) $query .= " AND DATE(a.tanggal_pengaduan) = '" . mysqli_real_escape_string($conn, $filterTanggal) . "'";
if ($filterBulan) $query .= " AND DATE_FORMAT(a.tanggal_pengaduan, '%Y-%m') = '" . mysqli_real_escape_string($conn, $filterBulan) . "'";
if ($filterSiswa && isAdmin()) $query .= " AND a.user_id = " . intval($filterSiswa);
if ($filterKategori) $query .= " AND a.kategori_id = " . intval($filterKategori);
if ($filterStatus) $query .= " AND a.status = '" . mysqli_real_escape_string($conn, $filterStatus) . "'";
$query .= " ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $query);
$kategoriList = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
if (isAdmin()) {
    $siswaList = mysqli_query($conn, "SELECT id, nama, kelas FROM users WHERE role = 'siswa' ORDER BY nama");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Aspirasi - Aplikasi Pengaduan Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-6 md:py-8">
        <?php showAlert(); ?>
        
        <div class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-1">Daftar Aspirasi</h1>
            <p class="text-gray-600 text-sm md:text-base"><?= isAdmin() ? 'Semua aspirasi dari siswa' : 'Daftar aspirasi yang Anda ajukan' ?></p>
        </div>
        
        <!-- Filter -->
        <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
            <h2 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Filter & Pencarian</h2>
            <form method="GET" action="">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= escapeHtml($filterTanggal) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Bulan</label>
                        <input type="month" name="bulan" value="<?= escapeHtml($filterBulan) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <?php if (isAdmin()): ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Siswa</label>
                        <select name="siswa" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Siswa</option>
                            <?php while ($siswa = mysqli_fetch_assoc($siswaList)): ?>
                                <option value="<?= $siswa['id'] ?>" <?= $filterSiswa == $siswa['id'] ? 'selected' : '' ?>>
                                    <?= escapeHtml($siswa['nama']) ?> (<?= escapeHtml($siswa['kelas']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kategori</option>
                            <?php while ($kat = mysqli_fetch_assoc($kategoriList)): ?>
                                <option value="<?= $kat['id'] ?>" <?= $filterKategori == $kat['id'] ? 'selected' : '' ?>><?= escapeHtml($kat['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $filterStatus == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="proses" <?= $filterStatus == 'proses' ? 'selected' : '' ?>>Proses</option>
                            <option value="selesai" <?= $filterStatus == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="ditolak" <?= $filterStatus == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Terapkan Filter</button>
                    <a href="list_aspirasi.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-5 py-2 rounded-lg text-sm font-medium transition">Reset</a>
                </div>
            </form>
        </div>
        
        <!-- List Aspirasi - Desktop Table / Mobile Cards -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg md:text-xl font-semibold text-gray-800">Hasil: <?= mysqli_num_rows($result) ?> Aspirasi</h2>
            </div>
            
            <!-- Desktop Table (hidden on mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Judul</th>
                            <?php if (isAdmin()): ?>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pengadu</th>
                            <?php endif; ?>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($asp = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 text-sm text-gray-900"><?= $no++ ?></td>
                                    <td class="px-4 py-4 text-sm text-gray-900 whitespace-nowrap"><?= date('d/m/Y', strtotime($asp['tanggal_pengaduan'])) ?></td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        <div class="font-medium"><?= escapeHtml($asp['judul']) ?></div>
                                        <div class="text-gray-500 text-xs"><?= escapeHtml($asp['lokasi']) ?></div>
                                    </td>
                                    <?php if (isAdmin()): ?>
                                    <td class="px-4 py-4 text-sm text-gray-900 whitespace-nowrap">
                                        <div><?= escapeHtml($asp['nama']) ?></div>
                                        <div class="text-gray-500 text-xs"><?= escapeHtml($asp['kelas']) ?></div>
                                    </td>
                                    <?php endif; ?>
                                    <td class="px-4 py-4 text-sm text-gray-900 whitespace-nowrap"><?= escapeHtml($asp['nama_kategori']) ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <?php $statusColors = ['pending'=>'bg-yellow-100 text-yellow-800','proses'=>'bg-blue-100 text-blue-800','selesai'=>'bg-green-100 text-green-800','ditolak'=>'bg-red-100 text-red-800']; ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusColors[$asp['status']] ?>"><?= ucfirst($asp['status']) ?></span>
                                    </td>
                                    <td class="px-4 py-4 text-sm whitespace-nowrap">
                                        <a href="detail_aspirasi.php?id=<?= $asp['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium">Detail →</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="<?= isAdmin() ? '7' : '6' ?>" class="px-6 py-8 text-center text-gray-500">Tidak ada data aspirasi</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards (hidden on desktop) -->
            <div class="md:hidden p-4 space-y-3">
                <?php 
                mysqli_data_seek($result, 0);
                $hasData = false;
                while ($asp = mysqli_fetch_assoc($result)):
                    $hasData = true;
                    $statusColors = ['pending'=>'bg-yellow-100 text-yellow-800','proses'=>'bg-blue-100 text-blue-800','selesai'=>'bg-green-100 text-green-800','ditolak'=>'bg-red-100 text-red-800'];
                ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-2 gap-2">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-800 text-sm truncate"><?= escapeHtml($asp['judul']) ?></h3>
                                <p class="text-xs text-gray-500"><?= escapeHtml($asp['lokasi']) ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full flex-shrink-0 <?= $statusColors[$asp['status']] ?>"><?= ucfirst($asp['status']) ?></span>
                        </div>
                        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 mb-3">
                            <span><?= date('d/m/Y', strtotime($asp['tanggal_pengaduan'])) ?></span>
                            <span><?= escapeHtml($asp['nama_kategori']) ?></span>
                            <?php if (isAdmin()): ?>
                                <span><?= escapeHtml($asp['nama']) ?> (<?= escapeHtml($asp['kelas']) ?>)</span>
                            <?php endif; ?>
                        </div>
                        <a href="detail_aspirasi.php?id=<?= $asp['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Lihat Detail →</a>
                    </div>
                <?php endwhile; ?>
                <?php if (!$hasData): ?>
                    <p class="text-center text-gray-500 py-8">Tidak ada data aspirasi</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
