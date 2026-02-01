-- Database: db_pengaduan_sekolah

CREATE DATABASE IF NOT EXISTS db_pengaduan_sekolah;
USE db_pengaduan_sekolah;

-- Tabel Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'siswa') DEFAULT 'siswa',
    kelas VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Aspirasi
CREATE TABLE aspirasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kategori_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(100),
    status ENUM('pending', 'proses', 'selesai', 'ditolak') DEFAULT 'pending',
    tanggal_pengaduan DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

-- Tabel Umpan Balik
CREATE TABLE umpan_balik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aspirasi_id INT NOT NULL,
    admin_id INT NOT NULL,
    pesan TEXT NOT NULL,
    progres INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aspirasi_id) REFERENCES aspirasi(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert data kategori default
INSERT INTO kategori (nama_kategori) VALUES
('Ruang Kelas'),
('Laboratorium'),
('Perpustakaan'),
('Toilet'),
('Lapangan Olahraga'),
('Kantin'),
('Fasilitas Lainnya');

-- Insert user default (password: admin123 dan siswa123)
INSERT INTO users (nama, username, password, role, kelas) VALUES
('Administrator', 'admin', '$2a$12$DVq0F4xwmC/CAEwG7Dmdc.TpJkteR5zmCC1bV2I6vABK6rNsgHE5u', 'admin', NULL),
('Budi Santoso', 'siswa1', '$2a$12$DVq0F4xwmC/CAEwG7Dmdc.TpJkteR5zmCC1bV2I6vABK6rNsgHE5u', 'siswa', 'XII-1');

-- Password hash untuk 'admin123' dan 'siswa123'
-- Gunakan password_hash('admin123', PASSWORD_DEFAULT) di PHP untuk generate