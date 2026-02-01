<?php
require_once 'config.php';

if (!isLoggedIn() || !isSiswa()) {
    redirect('index.php');
}

$userId = $_SESSION['user_id'];

// Ambil semua aspirasi user
$query = "SELECT a.*, k.nama_kategori,
          (SELECT COUNT(*) FROM umpan_balik WHERE aspirasi_id = a.id) as jumlah_feedback,
          (SELECT MAX(progres) FROM umpan_balik WHERE aspirasi_id = a.id) as progres_terkini
          FROM aspirasi a 
          JOIN kategori k ON a.kategori_id = k.id 
          WHERE a.user_id = $userId 
          ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $query);

// Hitung statistik
$totalAspirasi = mysqli_num_rows($result);
$queryStats = "SELECT 
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
               SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
               SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
               SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
               FROM aspirasi WHERE user_id = $userId";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $queryStats));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Aspirasi - Aplikasi Pengaduan Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <?php showAlert(); ?>
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Histori Aspirasi Saya</h1>
            <p class="text-gray-600">Semua aspirasi yang pernah Anda ajukan</p>
        </div>
        
        <!-- Statistik Ringkas -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-gray-500 text-sm mb-1">Total</p>
                <p class="text-3xl font-bold text-gray-800"><?= $totalAspirasi ?></p>
            </div>
            <div class="bg-yellow-50 rounded-lg shadow p-4 text-center">
                <p class="text-yellow-700 text-sm mb-1">Pending</p>
                <p class="text-3xl font-bold text-yellow-600"><?= $stats['pending'] ?></p>
            </div>
            <div class="bg-blue-50 rounded-lg shadow p-4 text-center">
                <p class="text-blue-700 text-sm mb-1">Proses</p>
                <p class="text-3xl font-bold text-blue-600"><?= $stats['proses'] ?></p>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-4 text-center">
                <p class="text-green-700 text-sm mb-1">Selesai</p>
                <p class="text-3xl font-bold text-green-600"><?= $stats['selesai'] ?></p>
            </div>
            <div class="bg-red-50 rounded-lg shadow p-4 text-center">
                <p class="text-red-700 text-sm mb-1">Ditolak</p>
                <p class="text-3xl font-bold text-red-600"><?= $stats['ditolak'] ?></p>
            </div>
        </div>
        
        <!-- Tombol Buat Aspirasi Baru -->
        <div class="mb-6">
            <a href="form_aspirasi.php" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Aspirasi Baru
            </a>
        </div>
        
        <!-- Timeline Aspirasi -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Timeline Aspirasi</h2>
            </div>
            
            <div class="p-6">
                <?php if ($totalAspirasi > 0): ?>
                    <div class="space-y-6">
                        <?php 
                        mysqli_data_seek($result, 0);
                        while ($asp = mysqli_fetch_assoc($result)): 
                        ?>
                            <div class="relative border-l-4 <?= $asp['status'] == 'selesai' ? 'border-green-500' : ($asp['status'] == 'proses' ? 'border-blue-500' : ($asp['status'] == 'ditolak' ? 'border-red-500' : 'border-yellow-500')) ?> pl-6 pb-6">
                                <div class="absolute w-4 h-4 rounded-full -left-2.5 top-1.5 <?= $asp['status'] == 'selesai' ? 'bg-green-500' : ($asp['status'] == 'proses' ? 'bg-blue-500' : ($asp['status'] == 'ditolak' ? 'bg-red-500' : 'bg-yellow-500')) ?>"></div>
                                
                                <div class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800 mb-1"><?= escapeHtml($asp['judul']) ?></h3>
                                            <div class="flex gap-3 text-sm text-gray-600">
                                                <span>📅 <?= date('d F Y', strtotime($asp['tanggal_pengaduan'])) ?></span>
                                                <span>📂 <?= escapeHtml($asp['nama_kategori']) ?></span>
                                                <span>📍 <?= escapeHtml($asp['lokasi']) ?></span>
                                            </div>
                                        </div>
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
                                    
                                    <p class="text-gray-700 mb-4"><?= escapeHtml(substr($asp['deskripsi'], 0, 150)) ?>...</p>
                                    
                                    <div class="flex justify-between items-center">
                                        <div class="flex gap-4 text-sm">
                                            <span class="text-gray-600">
                                                💬 <?= $asp['jumlah_feedback'] ?> Umpan Balik
                                            </span>
                                            <?php if ($asp['progres_terkini'] !== null): ?>
                                                <span class="text-blue-600 font-semibold">
                                                    📊 Progres: <?= $asp['progres_terkini'] ?>%
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <a href="detail_aspirasi.php?id=<?= $asp['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                                            Lihat Detail →
                                        </a>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <?php if ($asp['progres_terkini'] !== null && $asp['progres_terkini'] > 0): ?>
                                        <div class="mt-4">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: <?= $asp['progres_terkini'] ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-16">
                        <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg mb-4">Anda belum pernah mengajukan aspirasi</p>
                        <a href="form_aspirasi.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                            Buat Aspirasi Pertama
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>