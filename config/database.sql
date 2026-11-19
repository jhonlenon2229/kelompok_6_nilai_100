CREATE DATABASE IF NOT EXISTS sistem_relawan CHARACTER SET utf8mb4;
USE sistem_relawan;

CREATE TABLE relawan(id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(100) NOT NULL, email VARCHAR(100), telepon VARCHAR(20), alamat TEXT);
CREATE TABLE kegiatan(id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(150) NOT NULL, lokasi VARCHAR(150), tanggal DATE, kuota INT DEFAULT 0, deskripsi TEXT);
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, nama VARCHAR(100),
 role ENUM('admin','relawan') NOT NULL DEFAULT 'relawan', relawan_id INT NULL, FOREIGN KEY(relawan_id) REFERENCES relawan(id) ON DELETE CASCADE);
CREATE TABLE pendaftaran(id INT AUTO_INCREMENT PRIMARY KEY, relawan_id INT NOT NULL, kegiatan_id INT NOT NULL, tanggal_daftar DATE,
 FOREIGN KEY(relawan_id) REFERENCES relawan(id) ON DELETE CASCADE, FOREIGN KEY(kegiatan_id) REFERENCES kegiatan(id) ON DELETE CASCADE);
CREATE TABLE tugas(id INT AUTO_INCREMENT PRIMARY KEY, kegiatan_id INT NOT NULL, relawan_id INT NOT NULL, judul VARCHAR(150) NOT NULL, tenggat DATE,
 FOREIGN KEY(relawan_id) REFERENCES relawan(id) ON DELETE CASCADE, FOREIGN KEY(kegiatan_id) REFERENCES kegiatan(id) ON DELETE CASCADE);
CREATE TABLE kehadiran(id INT AUTO_INCREMENT PRIMARY KEY, relawan_id INT NOT NULL, kegiatan_id INT NOT NULL, tanggal DATE,
 FOREIGN KEY(relawan_id) REFERENCES relawan(id) ON DELETE CASCADE, FOREIGN KEY(kegiatan_id) REFERENCES kegiatan(id) ON DELETE CASCADE);

INSERT INTO relawan(nama,email,telepon,alamat) VALUES
('Andi Pratama','andi@mail.com','081234567801','Depok, Sleman'),
('Siti Rahma','siti@mail.com','081234567802','Condongcatur, Sleman'),
('Budi Santoso','budi@mail.com','081234567803','Gejayan, Yogyakarta'),
('Dewi Lestari','dewi@mail.com','081234567804','Godean, Sleman'),
('Rizky Maulana','rizky@mail.com','081234567805','Kotagede, Yogyakarta');

INSERT INTO kegiatan(nama,lokasi,tanggal,kuota,deskripsi) VALUES
('Bakti Sosial Panti Asuhan','Panti Asuhan Harapan','2026-12-05',20,'Pembagian sembako dan kegiatan bersama anak panti.'),
('Donor Darah Massal','Aula Kelurahan Caturtunggal','2026-11-17',30,'Aksi donor darah bersama PMI.'),
('Bersih Pantai','Pantai Parangtritis','2026-12-19',40,'Membersihkan sampah di sepanjang pantai.'),
('Penyuluhan Kesehatan','Puskesmas Depok','2026-11-08',15,'Penyuluhan pola hidup sehat untuk warga.'),
('Santunan Lansia','Panti Wreda Sejahtera','2026-11-14',12,'Santunan dan hiburan untuk lansia.');

-- Login admin: admin / password | Login relawan: relawan1 s/d relawan5 / password
INSERT INTO users(username,password,nama,role,relawan_id) VALUES
('admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator','admin',NULL),
('relawan1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Andi Pratama','relawan',1),
('relawan2','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Siti Rahma','relawan',2),
('relawan3','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Budi Santoso','relawan',3),
('relawan4','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Dewi Lestari','relawan',4),
('relawan5','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Rizky Maulana','relawan',5);

INSERT INTO pendaftaran(relawan_id,kegiatan_id,tanggal_daftar) VALUES
(1,4,'2026-11-01'),(2,4,'2026-11-02'),(3,5,'2026-11-05'),(4,1,'2026-11-12'),(5,2,'2026-11-10');

INSERT INTO tugas(kegiatan_id,relawan_id,judul,tenggat) VALUES
(4,1,'Registrasi peserta penyuluhan','2026-11-08'),
(5,3,'Dokumentasi kegiatan','2026-11-14'),
(1,4,'Menyiapkan paket sembako','2026-12-04'),
(2,5,'Mengatur antrean donor','2026-11-17'),
(3,2,'Koordinasi petugas kebersihan','2026-12-18');

INSERT INTO kehadiran(relawan_id,kegiatan_id,tanggal) VALUES
(1,4,'2026-11-08'),(2,4,'2026-11-08'),(3,5,'2026-11-14'),(4,5,'2026-11-14'),(5,2,'2026-11-17');
