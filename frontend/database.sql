CREATE DATABASE IF NOT EXISTS sim_relawan
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sim_relawan;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS laporan;
DROP TABLE IF EXISTS tugas;
DROP TABLE IF EXISTS kegiatan;
DROP TABLE IF EXISTS relawan;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE relawan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(25) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    status ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kegiatan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    tanggal DATE NOT NULL,
    lokasi VARCHAR(150) DEFAULT NULL,
    status ENUM('Rencana', 'Berjalan', 'Selesai') NOT NULL DEFAULT 'Rencana',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tugas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    relawan_id INT UNSIGNED DEFAULT NULL,
    kegiatan_id INT UNSIGNED DEFAULT NULL,
    tenggat DATE DEFAULT NULL,
    status ENUM('Belum', 'Proses', 'Selesai') NOT NULL DEFAULT 'Belum',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tugas_relawan FOREIGN KEY (relawan_id)
        REFERENCES relawan(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_tugas_kegiatan FOREIGN KEY (kegiatan_id)
        REFERENCES kegiatan(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE laporan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    kegiatan_id INT UNSIGNED DEFAULT NULL,
    relawan_id INT UNSIGNED DEFAULT NULL,
    tanggal DATE DEFAULT NULL,
    isi TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_laporan_kegiatan FOREIGN KEY (kegiatan_id)
        REFERENCES kegiatan(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_laporan_relawan FOREIGN KEY (relawan_id)
        REFERENCES relawan(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- Akun demo: username admin, password admin123.
-- Password disimpan dalam bentuk hash bcrypt.
INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$12$.R31JF8C6i894smRQjO0ZOACKZAyEPWT3ePcSK//cXvbJlEu.RzKO', 'Admin Desa', 'Administrator'),
('koordinator', '$2y$12$.R31JF8C6i894smRQjO0ZOACKZAyEPWT3ePcSK//cXvbJlEu.RzKO', 'Koordinator Lapangan', 'Koordinator');

INSERT INTO relawan (nama, telepon, alamat, status) VALUES
('Siti Rahmawati', '0812-3456-7801', 'Dusun Krajan, RT 02', 'Aktif'),
('Budi Santoso', '0813-2211-4502', 'Dusun Sidorejo, RT 05', 'Aktif'),
('Ratna Wulandari', '0857-9000-1203', 'Dusun Karanganyar, RT 01', 'Aktif'),
('Agus Prasetyo', '0821-7788-3304', 'Dusun Krajan, RT 04', 'Nonaktif');

INSERT INTO kegiatan (nama, tanggal, lokasi, status) VALUES
('Kerja Bakti Saluran Irigasi', '2026-11-22', 'Sawah Blok Timur', 'Rencana'),
('Posyandu Balita', '2026-11-12', 'Balai Dusun Krajan', 'Selesai'),
('Penanaman 200 Pohon', '2026-11-19', 'Lereng Bukit Desa', 'Berjalan');

INSERT INTO tugas (judul, relawan_id, kegiatan_id, tenggat, status) VALUES
('Mendata peserta posyandu', 1, 2, '2026-11-11', 'Selesai'),
('Menyiapkan bibit pohon', 2, 3, '2026-11-19', 'Proses'),
('Membagi kelompok kerja bakti', 3, 1, '2026-11-21', 'Belum');

INSERT INTO laporan (judul, kegiatan_id, relawan_id, tanggal, isi) VALUES
('Laporan Posyandu November', 2, 1, '2026-11-13', '48 balita ditimbang, 6 anak mendapat tambahan makanan bergizi.');
