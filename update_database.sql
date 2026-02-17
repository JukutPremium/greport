-- SQL Script untuk menambahkan fitur upload foto
-- Jalankan script ini di database Anda

-- Tambahkan kolom foto pada tabel aspirasi
ALTER TABLE `aspirasi` 
ADD COLUMN `foto` VARCHAR(255) NULL AFTER `lokasi`;

-- Tambahkan kolom foto pada tabel umpan_balik
ALTER TABLE `umpan_balik` 
ADD COLUMN `foto` VARCHAR(255) NULL AFTER `progres`;

-- Buat folder uploads jika belum ada (manual di server)
-- chmod 777 uploads/ (agar dapat menulis file)
