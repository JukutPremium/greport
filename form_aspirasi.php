<?php
require_once 'config.php';

if (!isLoggedIn() || !isSiswa()) {
    redirect('index.php');
}

// Ambil data kategori
$queryKategori = "SELECT * FROM kategori ORDER BY nama_kategori";
$kategoriList = mysqli_query($conn, $queryKategori);

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $kategoriId = mysqli_real_escape_string($conn, $_POST['kategori_id']);
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $tanggalPengaduan = date('Y-m-d');
    
    // Proses upload foto
    $fotoPath = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($_FILES['foto']['type'], $allowedTypes)) {
            if ($_FILES['foto']['size'] <= $maxFileSize) {
                $uploadDir = 'uploads/aspirasi/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $fileName = 'aspirasi_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetPath)) {
                    $fotoPath = $targetPath;
                } else {
                    $error = "Gagal mengupload foto.";
                }
            } else {
                $error = "Ukuran foto terlalu besar. Maksimal 5MB.";
            }
        } else {
            $error = "Format foto tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
    }
    
    if (!isset($error)) {
        $query = "INSERT INTO aspirasi (user_id, kategori_id, judul, deskripsi, lokasi, foto, tanggal_pengaduan) 
                  VALUES ('$userId', '$kategoriId', '$judul', '$deskripsi', '$lokasi', " . 
                  ($fotoPath ? "'$fotoPath'" : "NULL") . ", '$tanggalPengaduan')";
        
        if (mysqli_query($conn, $query)) {
            alert('Aspirasi berhasil diajukan!', 'success');
            redirect('histori_aspirasi.php');
        } else {
            $error = "Gagal mengajukan aspirasi: " . mysqli_error($conn);
            // Hapus foto jika insert gagal
            if ($fotoPath && file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Aspirasi - Aplikasi Pengaduan Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <?php showAlert(); ?>
        
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Form Aspirasi Siswa</h1>
                <p class="text-gray-600">Sampaikan pengaduan atau masukan terkait sarana dan prasarana sekolah</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="kategori_id">
                        Kategori Pengaduan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="kategori_id" 
                        id="kategori_id" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Pilih Kategori</option>
                        <?php while ($kat = mysqli_fetch_assoc($kategoriList)): ?>
                            <option value="<?= $kat['id'] ?>"><?= escapeHtml($kat['nama_kategori']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="judul">
                        Judul Pengaduan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="judul" 
                        id="judul" 
                        required
                        maxlength="200"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: Kerusakan Kursi di Ruang Kelas XII-1"
                    >
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="lokasi">
                        Lokasi <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="lokasi" 
                        id="lokasi" 
                        required
                        maxlength="100"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: Ruang Kelas XII-1"
                    >
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="deskripsi">
                        Deskripsi Detail <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="deskripsi" 
                        id="deskripsi" 
                        required
                        rows="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Jelaskan secara detail permasalahan yang ditemukan..."
                    ></textarea>
                    <p class="text-gray-500 text-xs mt-1">Berikan informasi selengkap-lengkapnya agar memudahkan penanganan</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="foto">
                        Upload Foto Pendukung (Opsional)
                    </label>
                    <input 
                        type="file" 
                        name="foto" 
                        id="foto" 
                        accept="image/jpeg,image/jpg,image/png,image/gif"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        onchange="previewImage(event)"
                    >
                    <p class="text-gray-500 text-xs mt-1">Format: JPG, PNG, GIF. Maksimal 5MB</p>
                    
                    <!-- Preview Foto -->
                    <div id="imagePreview" class="mt-4 hidden">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Preview:</p>
                        <img id="preview" src="" alt="Preview" class="max-w-md rounded-lg border border-gray-300 shadow-sm">
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-900 mb-2">Informasi Pengadu</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Nama:</span>
                            <span class="font-medium text-gray-800 ml-2"><?= escapeHtml($_SESSION['nama']) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Kelas:</span>
                            <span class="font-medium text-gray-800 ml-2"><?= escapeHtml($_SESSION['kelas']) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Tanggal:</span>
                            <span class="font-medium text-gray-800 ml-2"><?= date('d F Y') ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <button 
                        type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200"
                    >
                        Kirim Aspirasi
                    </button>
                    <a 
                        href="histori_aspirasi.php"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg text-center transition duration-200"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('imagePreview');
            
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