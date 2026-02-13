<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

// Proses Tambah User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $kelas = $role === 'siswa' ? mysqli_real_escape_string($conn, $_POST['kelas']) : NULL;
    
    $query = "INSERT INTO users (nama, username, password, role, kelas) 
              VALUES ('$nama', '$username', '$password', '$role', " . ($kelas ? "'$kelas'" : "NULL") . ")";
    
    if (mysqli_query($conn, $query)) {
        alert('User berhasil ditambahkan!', 'success');
        redirect('manage_users.php');
    } else {
        $error = "Gagal menambahkan user: " . mysqli_error($conn);
    }
}

// Proses Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $kelas = $role === 'siswa' ? mysqli_real_escape_string($conn, $_POST['kelas']) : NULL;
    
    $query = "UPDATE users SET 
              nama = '$nama', 
              username = '$username', 
              role = '$role', 
              kelas = " . ($kelas ? "'$kelas'" : "NULL") . " 
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        alert('User berhasil diupdate!', 'success');
        redirect('manage_users.php');
    } else {
        $error = "Gagal mengupdate user: " . mysqli_error($conn);
    }
}

// Proses Reset Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $id = intval($_POST['user_id']);
    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $query = "UPDATE users SET password = '$password' WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        alert('Password berhasil direset!', 'success');
        redirect('manage_users.php');
    }
}

// Proses Hapus User
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Cek apakah user sedang login
    if ($id == $_SESSION['user_id']) {
        alert('Tidak dapat menghapus user yang sedang login!', 'error');
        redirect('manage_users.php');
    }
    
    $query = "DELETE FROM users WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        alert('User berhasil dihapus!', 'success');
    } else {
        alert('Gagal menghapus user!', 'error');
    }
    redirect('manage_users.php');
}

// Ambil semua user
$queryUsers = "SELECT * FROM users ORDER BY created_at DESC";
$usersList = mysqli_query($conn, $queryUsers);

// Ambil user untuk edit jika ada
$editUser = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editResult = mysqli_query($conn, "SELECT * FROM users WHERE id = $editId");
    $editUser = mysqli_fetch_assoc($editResult);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Aplikasi Pengaduan Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <?php showAlert(); ?>
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Manajemen User</h1>
            <p class="text-gray-600">Kelola data user sistem</p>
        </div>
        
        <!-- Form Tambah/Edit User -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <?= $editUser ? 'Edit User' : 'Tambah User Baru' ?>
            </h2>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php if ($editUser): ?>
                    <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="nama" 
                            required
                            value="<?= $editUser ? escapeHtml($editUser['nama']) : '' ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: John Doe"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="username" 
                            required
                            value="<?= $editUser ? escapeHtml($editUser['username']) : '' ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: johndoe"
                        >
                    </div>
                    
                    <?php if (!$editUser): ?>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Minimal 6 karakter"
                        >
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="role" 
                            id="roleSelect"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onchange="toggleKelasField()"
                        >
                            <option value="siswa" <?= $editUser && $editUser['role'] == 'siswa' ? 'selected' : '' ?>>Siswa</option>
                            <option value="admin" <?= $editUser && $editUser['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div id="kelasField">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="kelas" 
                            id="kelasInput"
                            value="<?= $editUser ? escapeHtml($editUser['kelas']) : '' ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: XII-1"
                        >
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button 
                        type="submit" 
                        name="<?= $editUser ? 'edit_user' : 'tambah_user' ?>"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        <?= $editUser ? 'Update User' : 'Tambah User' ?>
                    </button>
                    <?php if ($editUser): ?>
                        <a href="manage_users.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Tabel User -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Daftar User</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Terdaftar</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $no = 1;
                        while ($user = mysqli_fetch_assoc($usersList)): 
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $no++ ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= escapeHtml($user['nama']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= escapeHtml($user['username']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $user['role'] == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $user['kelas'] ? escapeHtml($user['kelas']) : '-' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex gap-2">
                                        <a href="manage_users.php?edit=<?= $user['id'] ?>" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Edit
                                        </a>
                                        <button 
                                            onclick="resetPassword(<?= $user['id'] ?>, '<?= escapeHtml($user['nama']) ?>')"
                                            class="text-yellow-600 hover:text-yellow-800 font-medium">
                                            Reset
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="manage_users.php?hapus=<?= $user['id'] ?>" 
                                               onclick="return confirm('Yakin ingin menghapus user ini?')"
                                               class="text-red-600 hover:text-red-800 font-medium">
                                                Hapus
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Reset Password -->
    <div id="resetModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Reset Password</h3>
                <form method="POST" action="">
                    <input type="hidden" name="user_id" id="resetUserId">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            User: <span id="resetUserName" class="font-normal"></span>
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="new_password" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Minimal 6 karakter"
                        >
                    </div>
                    <div class="flex gap-2">
                        <button 
                            type="submit" 
                            name="reset_password"
                            class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition"
                        >
                            Reset Password
                        </button>
                        <button 
                            type="button"
                            onclick="closeResetModal()"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-medium transition"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function toggleKelasField() {
            const role = document.getElementById('roleSelect').value;
            const kelasField = document.getElementById('kelasField');
            const kelasInput = document.getElementById('kelasInput');
            
            if (role === 'admin') {
                kelasField.style.display = 'none';
                kelasInput.removeAttribute('required');
            } else {
                kelasField.style.display = 'block';
                kelasInput.setAttribute('required', 'required');
            }
        }
        
        function resetPassword(userId, userName) {
            document.getElementById('resetUserId').value = userId;
            document.getElementById('resetUserName').textContent = userName;
            document.getElementById('resetModal').classList.remove('hidden');
        }
        
        function closeResetModal() {
            document.getElementById('resetModal').classList.add('hidden');
        }
        
        // Initialize on page load
        toggleKelasField();
    </script>
</body>
</html>