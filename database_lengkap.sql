-- =====================================================
-- Database: db_pengaduan_sekolah
-- Versi: 2.0 dengan Fitur Upload Foto
-- Tanggal: 13 Februari 2026
-- =====================================================

-- Buat database
CREATE DATABASE IF NOT EXISTS db_pengaduan_sekolah;
USE db_pengaduan_sekolah;

-- =====================================================
-- Tabel Users
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'siswa') DEFAULT 'siswa',
    kelas VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Tabel Kategori
-- =====================================================
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Tabel Aspirasi
-- FITUR BARU: Kolom foto untuk upload gambar pendukung
-- =====================================================
CREATE TABLE aspirasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kategori_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(100),
    foto VARCHAR(255) DEFAULT NULL COMMENT 'Path file foto pendukung',
    status ENUM('pending', 'proses', 'selesai', 'ditolak') DEFAULT 'pending',
    tanggal_pengaduan DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

-- =====================================================
-- Tabel Umpan Balik
-- FITUR BARU: Kolom foto untuk upload gambar progres
-- =====================================================
CREATE TABLE umpan_balik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aspirasi_id INT NOT NULL,
    admin_id INT NOT NULL,
    pesan TEXT NOT NULL,
    progres INT DEFAULT 0 COMMENT 'Persentase 0-100',
    foto VARCHAR(255) DEFAULT NULL COMMENT 'Path file foto progres perbaikan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aspirasi_id) REFERENCES aspirasi(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- Insert data kategori default
-- =====================================================
INSERT INTO kategori (nama_kategori) VALUES
('Ruang Kelas'),
('Laboratorium'),
('Perpustakaan'),
('Toilet'),
('Lapangan Olahraga'),
('Kantin'),
('Fasilitas Lainnya');

-- =====================================================
-- Insert user default
-- Username: admin | Password: admin123
-- Username: siswa1 | Password: siswa123
-- =====================================================
INSERT INTO users (nama, username, password, role, kelas) VALUES
('Administrator', 'admin', '$2a$12$DVq0F4xwmC/CAEwG7Dmdc.TpJkteR5zmCC1bV2I6vABK6rNsgHE5u', 'admin', NULL),
('Budi Santoso', 'siswa1', '$2a$12$DVq0F4xwmC/CAEwG7Dmdc.TpJkteR5zmCC1bV2I6vABK6rNsgHE5u', 'siswa', 'XII-1');

-- =====================================================
-- Index untuk Optimasi
-- =====================================================
CREATE INDEX idx_aspirasi_status ON aspirasi(status);
CREATE INDEX idx_aspirasi_foto ON aspirasi(foto);
CREATE INDEX idx_umpan_balik_foto ON umpan_balik(foto);

-- =====================================================
-- SELESAI
-- =====================================================
SELECT 'Database berhasil dibuat dengan fitur upload foto!' AS status;
SELECT 'Jangan lupa buat folder: uploads/aspirasi/ dan uploads/feedback/' AS reminder;
SELECT 'Set permission: chmod -R 777 uploads/' AS reminder2;
