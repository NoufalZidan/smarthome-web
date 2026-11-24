CREATE DATABASE db_smarthome;
USE db_smarthome;

-- Tabel 1: Autentikasi Pengguna
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'guest') DEFAULT 'guest'
);

-- Tabel 2: Data Perangkat Utama
CREATE TABLE perangkat_iot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_perangkat VARCHAR(100) NOT NULL,
    mac_address VARCHAR(50) NOT NULL,
    jaringan_wifi VARCHAR(50) NOT NULL,
    kategori ENUM('LampuPintar', 'SistemAC', 'PintuPintar') NOT NULL,
    status_daya BOOLEAN DEFAULT 0,
    spesifikasi_ekstra VARCHAR(50) -- Contoh: '80%' untuk lampu, '22C' untuk AC
);

-- Tabel 3: Riwayat Aktivitas (Berelasi dengan perangkat_iot)
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_perangkat INT,
    aksi VARCHAR(100) NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_perangkat) REFERENCES perangkat_iot(id) ON DELETE CASCADE
);

-- DUMMY DATA UNTUK TESTING
-- Password untuk admin adalah: admin123
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$e.w/9YvHn/N/y9/fH.O.O.rL/nO/d/S/Y.x.x.x.x.x.x.x', 'admin');

-- Contoh perangkat
INSERT INTO perangkat_iot (nama_perangkat, mac_address, jaringan_wifi, kategori, status_daya, spesifikasi_ekstra) 
VALUES 
('Lampu Ruang Tamu', 'AA:BB:CC:DD:EE:11', 'ZidanDev_Network', 'LampuPintar', 0, '80%'),
('AC Kamar Utama', 'FF:EE:DD:CC:BB:22', 'ZidanDev_Network', 'SistemAC', 0, '24C');