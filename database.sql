-- Database structure for Sistem Absensi dan Penggajian

CREATE DATABASE IF NOT EXISTS absensi_db;
USE absensi_db;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'karyawan') NOT NULL DEFAULT 'karyawan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: tarif
CREATE TABLE IF NOT EXISTS tarif (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarif_normal DECIMAL(10, 2) NOT NULL COMMENT 'Tarif per jam normal',
    tarif_lembur DECIMAL(10, 2) NOT NULL COMMENT 'Tarif per jam lembur',
    jam_normal INT NOT NULL COMMENT 'Jumlah jam kerja normal per hari',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: absensi
CREATE TABLE IF NOT EXISTS absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME,
    jam_keluar TIME,
    status ENUM('hadir', 'izin', 'sakit', 'alpha') NOT NULL DEFAULT 'hadir',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_absensi (user_id, tanggal)
);

-- Table: gaji
CREATE TABLE IF NOT EXISTS gaji (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    total_jam_normal DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total_jam_lembur DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total_gaji DECIMAL(15, 2) NOT NULL DEFAULT 0,
    status ENUM('draft', 'final') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_gaji (user_id, bulan, tahun)
);

-- Create settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
-- INSERT INTO users (username, password, nama_lengkap, role) VALUES
-- ('admin', '$2y$10$qwSvlDQVJ.BSRaIdBLJkUuQy0p0HhJA4B1dKCn8UbZkRVwS9RvJ8y', 'Administrator', 'admin');

-- Insert default tarif
INSERT INTO tarif (tarif_normal, tarif_lembur, jam_normal) VALUES
(50000, 75000, 8);

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('app_name', 'Sistem Absensi dan Penggajian'),
('company_name', 'PT. Example Indonesia'),
('company_address', 'Jl. Contoh No. 123, Jakarta'),
('company_phone', '021-1234567'),
('company_email', 'info@example.com'),
('work_start_time', '08:00'),
('work_end_time', '17:00'),
('allow_weekend', '0');
