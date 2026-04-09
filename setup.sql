-- ================================================
-- Setup Database: taskmanager
-- Jalankan file ini sekali untuk inisialisasi DB
-- ================================================

CREATE DATABASE IF NOT EXISTS taskmanager
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE taskmanager;

CREATE TABLE IF NOT EXISTS tasks (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  judul       VARCHAR(255)  NOT NULL,
  deskripsi   TEXT,
  prioritas   ENUM('rendah','sedang','tinggi') NOT NULL DEFAULT 'sedang',
  status      ENUM('belum','proses','selesai')  NOT NULL DEFAULT 'belum',
  deadline    DATE,
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  diupdate_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Contoh data awal
INSERT INTO tasks (judul, deskripsi, prioritas, status, deadline) VALUES
  ('Buat laporan bulanan',   'Laporan keuangan Q1 2026',      'tinggi',  'proses',  '2026-04-15'),
  ('Meeting tim desain',     'Review mockup aplikasi mobile', 'sedang',  'belum',   '2026-04-12'),
  ('Update dokumentasi API', 'Tambah endpoint baru ke docs',  'rendah',  'selesai', '2026-04-10');
