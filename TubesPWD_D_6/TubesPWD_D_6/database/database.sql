CREATE DATABASE IF NOT EXISTS homestay_db; 
USE homestay_db;

DROP TABLE IF EXISTS ulasan;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS reservasi;
DROP TABLE IF EXISTS homestay;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap VARCHAR(100) NOT NULL,
  username VARCHAR(30)  NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  no_hp VARCHAR(20)  DEFAULT NULL,
  foto_profil VARCHAR(255) DEFAULT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE homestay (
  id_homestay INT AUTO_INCREMENT PRIMARY KEY,
  nama_homestay VARCHAR(100) NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  kota VARCHAR(60)  NOT NULL,
  latitude DECIMAL(10,7) NOT NULL,
  longitude DECIMAL(10,7) NOT NULL,
  deskripsi TEXT,
  fasilitas VARCHAR(255) DEFAULT NULL,
  jumlah_kamar INT NOT NULL DEFAULT 1,
  kapasitas_min INT NOT NULL DEFAULT 1,
  kapasitas_maks INT NOT NULL DEFAULT 2,
  harga_per_malam DECIMAL(12,0) NOT NULL,
  foto_utama VARCHAR(255) DEFAULT NULL
);

CREATE TABLE reservasi (
  id_reservasi INT AUTO_INCREMENT PRIMARY KEY,
  kode_reservasi VARCHAR(20) NOT NULL UNIQUE,
  id_user INT NOT NULL,
  id_homestay INT NOT NULL,
  tgl_checkin DATE NOT NULL,
  tgl_checkout DATE NOT NULL,
  jumlah_tamu INT NOT NULL,
  total_harga DECIMAL(12,0) NOT NULL,
  status ENUM('menunggu','dikonfirmasi','dibatalkan') NOT NULL DEFAULT 'menunggu',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_homestay) REFERENCES homestay(id_homestay) ON DELETE RESTRICT
);

CREATE TABLE pembayaran (
  id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
  id_reservasi INT NOT NULL UNIQUE,
  metode VARCHAR(40) NOT NULL,
  jumlah_bayar DECIMAL(12,0) NOT NULL,
  bukti_transfer VARCHAR(255) NOT NULL,
  tgl_bayar DATE NOT NULL,
  status_verifikasi ENUM('menunggu','valid','ditolak') NOT NULL DEFAULT 'menunggu',
  catatan_admin VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_reservasi) REFERENCES reservasi(id_reservasi) ON DELETE CASCADE
);

CREATE TABLE ulasan (
  id_ulasan INT AUTO_INCREMENT PRIMARY KEY,
  id_reservasi INT NOT NULL UNIQUE,
  rating DECIMAL(2,1) NOT NULL,
  komentar TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_reservasi) REFERENCES reservasi(id_reservasi) ON DELETE CASCADE
);

-- Akun admin awal: username "admin", password "admin123"
INSERT INTO users (nama_lengkap, username, email, password_hash, no_hp, role)
VALUES ('Administrator', 'admin', 'admin@homestay.test', '$2y$10$1GUfd.hTkjHT8lUqjwmKqOzyNwj5LsOwJyDRwikeH3ClseBYyvMF2', '081234567890', 'admin');

INSERT INTO homestay (nama_homestay, alamat, kota, latitude, longitude, deskripsi, fasilitas, jumlah_kamar, kapasitas_min, kapasitas_maks, harga_per_malam) VALUES
('Homestay Babarsari Asri', 'Jl. Babarsari No. 44, Caturtunggal, Depok', 'Yogyakarta', -7.7796000, 110.4150000, 'Homestay untuk keluarga, lengkap dengan dapur dan ruang tamu luas. Dekat kampus dan pusat kuliner.', 'Dapur, Wi-Fi, Parkir, AC', 3, 5, 8, 550000),
('Griya Malioboro', 'Jl. Pajeksan No. 10, Sosromenduran, Gedong Tengen', 'Yogyakarta', -7.7926000, 110.3657000, 'Penginapan nyaman berjarak jalan kaki dari Malioboro dan Stasiun Tugu.', 'Wi-Fi, AC, Sarapan', 2, 2, 4, 350000),
('Villa Kaliurang Sejuk', 'Jl. Kaliurang Km 20, Hargobinangun, Pakem', 'Sleman', -7.6000000, 110.4300000, 'Villa berhawa sejuk di lereng Merapi, cocok untuk liburan rombongan.', 'Dapur, Wi-Fi, Parkir, Teras', 4, 6, 10, 750000);

