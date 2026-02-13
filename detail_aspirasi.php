<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$aspirasiId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = $_SESSION['user_id'];

// Ambil data aspirasi
if (isAdmin()) {
    $query = "SELECT a.*, u.nama, u.kelas, k.nama_kategori 
              FROM aspirasi a 
              JOIN users u ON a.user_id = u.id 
              JOIN kategori k ON a.kategori_id = k.id 
              WHERE a.id = $aspirasiId";
} else {
    $query = "SELECT a.*, k.nama_kategori 
              FROM aspirasi a 
              JOIN kategori k ON a.kategori_id = k.id 
              WHERE a.id = $aspirasiId AND a.user_id = $userId";
}

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    alert('Aspirasi tidak ditemukan!', 'error');
    redirect('list_aspirasi.php');
}

$aspirasi = mysqli_fetch_assoc($result);

// Proses update status (Admin only)
if (isAdmin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
    $updateQuery = "UPDATE aspirasi SET status = '$newStatus' WHERE id = $aspirasiId";
    
    if (mysqli_query($conn, $updateQuery)) {
        alert('Status berhasil diupdate!', 'success');
        redirect("detail_aspirasi.php?id=$aspirasiId");
    }
}

// Proses tambah umpan balik (Admin only)
if (isAdmin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_feedback'])) {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    $progres = intval($_POST['progres']);
    
    // Proses upload foto
    $fotoPath = null;
    if (isset($_FILES['foto_feedback']) && $_FILES['foto_feedback']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($_FILES['foto_feedback']['type'], $allowedTypes)) {
            if ($_FILES['foto_feedback']['size'] <= $maxFileSize) {
                $uploadDir = 'uploads/feedback/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = pathinfo($_FILES['foto_feedback']['name'], PATHINFO_EXTENSION);
                $fileName = 'feedback_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['foto_feedback']['tmp_name'], $targetPath)) {
                    $fotoPath = $targetPath;
                }
            }
        }
    }
    
    $insertQuery = "INSERT INTO umpan_balik (aspirasi_id, admin_id, pesan, progres, foto) 
                    VALUES ($aspirasiId, $userId, '$pesan', $progres, " . 
                    ($fotoPath ? "'$fotoPath'" : "NULL") . ")";
    
    if (mysqli_query($conn, $insertQuery)) {
        alert('Umpan balik berhasil ditambahkan!', 'success');
        redirect("detail_aspirasi.php?id=$aspirasiId");
    } else {
        // Hapus foto jika insert gagal
        if ($fotoPath && file_exists($fotoPath)) {
            unlink($fotoPath);
        }
    }
}

// Ambil umpan balik
$feedbackQuery = "SELECT ub.*, u.nama as admin_nama 
                  FROM umpan_balik ub 
                  JOIN users u ON ub.admin_id = u.id 
                  WHERE ub.aspirasi_id = $aspirasiId 
                  ORDER BY ub.created_at DESC";
$feedbackResult = mysqli_query($conn, $feedbackQuery);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aspirasi - Aplikasi Pengaduan Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <?php showAlert(); ?>
        
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="list_aspirasi.php" class="text-blue-600 hover:text-blue-800 text-sm">← Kembali ke Daftar</a>
        </div>
        
        <!-- Detail Aspirasi -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= escapeHtml($aspirasi['judul']) ?></h1>
                    <div class="flex gap-4 text-sm text-gray-600">
                        <span>📅 <?= date('d F Y', strtotime($aspirasi['tanggal_pengaduan'])) ?></span>
                        <span>📂 <?= escapeHtml($aspirasi['nama_kategori']) ?></span>
                        <span>📍 <?= escapeHtml($aspirasi['lokasi']) ?></span>
                    </div>
                </div>
                
                <?php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                    'proses' => 'bg-blue-100 text-blue-800 border-blue-300',
                    'selesai' => 'bg-green-100 text-green-800 border-green-300',
                    'ditolak' => 'bg-red-100 text-red-800 border-red-300'
                ];
                ?>
                <span class="px-4 py-2 text-sm font-semibold rounded-full border-2 <?= $statusColors[$aspirasi['status']] ?>">
                    <?= ucfirst($aspirasi['status']) ?>
                </span>
            </div>
            
            <?php if (isAdmin()): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2">Informasi Pengadu</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Nama:</span>
                        <span class="font-medium text-gray-800 ml-2"><?= escapeHtml($aspirasi['nama']) ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Kelas:</span>
                        <span class="font-medium text-gray-800 ml-2"><?= escapeHtml($aspirasi['kelas']) ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="mb-6">
                <h3 class="font-semibold text-gray-800 mb-2">Deskripsi Pengaduan</h3>
                <p class="text-gray-700 leading-relaxed"><?= nl2br(escapeHtml($aspirasi['deskripsi'])) ?></p>
            </div>
            
            <?php if (!empty($aspirasi['foto']) && file_exists($aspirasi['foto'])): ?>
            <div class="mb-6">
                <h3 class="font-semibold text-gray-800 mb-2">Foto Pendukung</h3>
                <a href="<?= $aspirasi['foto'] ?>" target="_blank" class="inline-block">
                    <img src="<?= $aspirasi['foto'] ?>" alt="Foto Aspirasi" class="max-w-md rounded-lg border border-gray-300 shadow-md hover:shadow-lg transition cursor-pointer">
                </a>
                <p class="text-sm text-gray-500 mt-2">Klik untuk memperbesar</p>
            </div>
            <?php endif; ?>
            
            <!-- Update Status (Admin Only) -->
            <?php if (isAdmin()): ?>
            <div class="border-t pt-6">
                <h3 class="font-semibold text-gray-800 mb-4">Update Status Penyelesaian</h3>
                <form method="POST" action="" class="flex gap-4">
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pending" <?= $aspirasi['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="proses" <?= $aspirasi['status'] == 'proses' ? 'selected' : '' ?>>Dalam Proses</option>
                        <option value="selesai" <?= $aspirasi['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="ditolak" <?= $aspirasi['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                    <button type="submit" name="update_status" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                        Update Status
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Form Umpan Balik (Admin Only) -->
        <?php if (isAdmin()): ?>
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Tambah Umpan Balik & Progres</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        Pesan Umpan Balik
                    </label>
                    <textarea 
                        name="pesan" 
                        required
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Berikan informasi terkini tentang penanganan aspirasi ini..."
                    ></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        Progres Perbaikan (%)
                    </label>
                    <input 
                        type="range" 
                        name="progres" 
                        id="progresRange"
                        min="0" 
                        max="100" 
                        value="0"
                        class="w-full"
                        oninput="document.getElementById('progresValue').textContent = this.value + '%'"
                    >
                    <div class="text-center mt-2">
                        <span id="progresValue" class="text-2xl font-bold text-blue-600">0%</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="foto_feedback">
                        Upload Foto Progres (Opsional)
                    </label>
                    <input 
                        type="file" 
                        name="foto_feedback" 
                        id="foto_feedback" 
                        accept="image/jpeg,image/jpg,image/png,image/gif"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                        onchange="previewFeedbackImage(event)"
                    >
                    <p class="text-gray-500 text-xs mt-1">Format: JPG, PNG, GIF. Maksimal 5MB</p>
                    
                    <!-- Preview Foto -->
                    <div id="feedbackImagePreview" class="mt-4 hidden">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Preview:</p>
                        <img id="feedbackPreview" src="" alt="Preview" class="max-w-md rounded-lg border border-gray-300 shadow-sm">
                    </div>
                </div>
                
                <button type="submit" name="tambah_feedback" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                    Kirim Umpan Balik
                </button>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Daftar Umpan Balik -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Histori Umpan Balik & Progres</h2>
            
            <?php if (mysqli_num_rows($feedbackResult) > 0): ?>
                <div class="space-y-6">
                    <?php while ($fb = mysqli_fetch_assoc($feedbackResult)): ?>
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="font-semibold text-gray-800">Admin: <?= escapeHtml($fb['admin_nama']) ?></p>
                                    <p class="text-sm text-gray-500"><?= date('d F Y H:i', strtotime($fb['created_at'])) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600 mb-1">Progres Perbaikan</p>
                                    <p class="text-2xl font-bold text-blue-600"><?= $fb['progres'] ?>%</p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: <?= $fb['progres'] ?>%"></div>
                                </div>
                            </div>
                            
                            <p class="text-gray-700 leading-relaxed mb-3"><?= nl2br(escapeHtml($fb['pesan'])) ?></p>
                            
                            <?php if (!empty($fb['foto']) && file_exists($fb['foto'])): ?>
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Foto Progres:</p>
                                <a href="<?= $fb['foto'] ?>" target="_blank" class="inline-block">
                                    <img src="<?= $fb['foto'] ?>" alt="Foto Progres" class="max-w-sm rounded-lg border border-gray-300 shadow-sm hover:shadow-md transition cursor-pointer">
                                </a>
                                <p class="text-xs text-gray-500 mt-1">Klik untuk memperbesar</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p>Belum ada umpan balik untuk aspirasi ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function previewFeedbackImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('feedbackPreview');
            const previewContainer = document.getElementById('feedbackImagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
            }
        }
    </script>
</body>
</html>