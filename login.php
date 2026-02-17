<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['kelas'] = $user['kelas'];
            alert('Login berhasil! Selamat datang ' . $user['nama'], 'success');
            redirect('index.php');
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Pengaduan Sarana Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 w-full max-w-md">
        <div class="text-center mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-1">Aplikasi Pengaduan</h1>
            <p class="text-gray-600 text-sm md:text-base">Sarana & Prasarana Sekolah</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="username">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    id="username" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base"
                    placeholder="Masukkan username"
                >
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base"
                    placeholder="Masukkan password"
                >
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 text-sm md:text-base">
                Login
            </button>
        </form>
    </div>
</body>
</html>
