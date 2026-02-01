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
    
    $query = "INSERT INTO aspirasi (user_id, kategori_id, judul, deskripsi, lokasi, tanggal_pengaduan) 
              VALUES ('$userId', '$kategoriId', '$judul', '$deskripsi', '$lokasi', '$tanggalPengaduan')";
    
    if (mysqli_query($conn, $query)) {
        alert('Aspirasi berhasil diajukan!', 'success');
        redirect('histori_aspirasi.php');
    } else {
        $error = "Gagal mengajukan aspirasi: " . mysqli_error($conn);
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
            
            <form method="POST" action="">
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
</body>
</html>