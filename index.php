<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Ambil data statistik
$userId = $_SESSION['user_id'];

if (isAdmin()) {
    $queryTotal = "SELECT COUNT(*) as total FROM aspirasi";
    $queryPending = "SELECT COUNT(*) as total FROM aspirasi WHERE status = 'pending'";
    $queryProses = "SELECT COUNT(*) as total FROM aspirasi WHERE status = 'proses'";
    $querySelesai = "SELECT COUNT(*) as total FROM aspirasi WHERE status = 'selesai'";
} else {
    $queryTotal = "SELECT COUNT(*) as total FROM aspirasi WHERE user_id = $userId";
    $queryPending = "SELECT COUNT(*) as total FROM aspirasi WHERE user_id = $userId AND status = 'pending'";
    $queryProses = "SELECT COUNT(*) as total FROM aspirasi WHERE user_id = $userId AND status = 'proses'";
    $querySelesai = "SELECT COUNT(*) as total FROM aspirasi WHERE user_id = $userId AND status = 'selesai'";
}

$totalAspirasi = mysqli_fetch_assoc(mysqli_query($conn, $queryTotal))['total'];
$totalPending = mysqli_fetch_assoc(mysqli_query($conn, $queryPending))['total'];
$totalProses = mysqli_fetch_assoc(mysqli_query($conn, $queryProses))['total'];
$totalSelesai = mysqli_fetch_assoc(mysqli_query($conn, $querySelesai))['total'];

// Ambil data aspirasi terbaru
if (isAdmin()) {
    $queryAspirasi = "SELECT a.*, u.nama, u.kelas, k.nama_kategori 
                      FROM aspirasi a 
                      JOIN users u ON a.user_id = u.id 
                      JOIN kategori k ON a.kategori_id = k.id 
                      ORDER BY a.created_at DESC LIMIT 5";
} else {
    $queryAspirasi = "SELECT a.*, k.nama_kategori 
                      FROM aspirasi a 
                      JOIN kategori k ON a.kategori_id = k.id 
                      WHERE a.user_id = $userId 
                      ORDER BY a.created_at DESC LIMIT 5";
}

$aspirasiTerbaru = mysqli_query($conn, $queryAspirasi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Pengaduan Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <?php showAlert(); ?>
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h1>
            <p class="text-gray-600">Selamat datang, <?= escapeHtml($_SESSION['nama']) ?> (<?= ucfirst($_SESSION['role']) ?>)</p>
        </div>
        
        <!-- Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Total Aspirasi</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $totalAspirasi ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Pending</p>
                        <p class="text-3xl font-bold text-yellow-600"><?= $totalPending ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Dalam Proses</p>
                        <p class="text-3xl font-bold text-blue-600"><?= $totalProses ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Selesai</p>
                        <p class="text-3xl font-bold text-green-600"><?= $totalSelesai ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Aspirasi Terbaru -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Aspirasi Terbaru</h2>
                <a href="list_aspirasi.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Lihat Semua →
                </a>
            </div>
            <div class="p-6">
                <?php if (mysqli_num_rows($aspirasiTerbaru) > 0): ?>
                    <div class="space-y-4">
                        <?php while ($asp = mysqli_fetch_assoc($aspirasiTerbaru)): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-gray-800"><?= escapeHtml($asp['judul']) ?></h3>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'proses' => 'bg-blue-100 text-blue-800',
                                        'selesai' => 'bg-green-100 text-green-800',
                                        'ditolak' => 'bg-red-100 text-red-800'
                                    ];
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $statusColors[$asp['status']] ?>">
                                        <?= ucfirst($asp['status']) ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm mb-2"><?= escapeHtml(substr($asp['deskripsi'], 0, 100)) ?>...</p>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>
                                        <span class="font-medium"><?= escapeHtml($asp['nama_kategori']) ?></span>
                                        <?php if (isAdmin()): ?>
                                            | <?= escapeHtml($asp['nama']) ?> (<?= escapeHtml($asp['kelas']) ?>)
                                        <?php endif; ?>
                                    </span>
                                    <span><?= date('d/m/Y', strtotime($asp['tanggal_pengaduan'])) ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">Belum ada aspirasi</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>