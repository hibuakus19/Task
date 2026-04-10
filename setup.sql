CREATE DATABASE IF NOT EXISTS taskmanager
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE taskmanager;

CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(100) NOT NULL,
  email       VARCHAR(191) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tasks (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT NOT NULL,
  judul         VARCHAR(255) NOT NULL,
  deskripsi     TEXT,
  prioritas     ENUM('rendah','sedang','tinggi') NOT NULL DEFAULT 'sedang',
  status        ENUM('belum','proses','selesai')  NOT NULL DEFAULT 'belum',
  deadline      DATE,
  dibuat_pada   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  diupdate_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Akun demo: admin@taskflow.id / admin123
INSERT IGNORE INTO users (nama, email, password) VALUES
  ('Admin', 'admin@taskflow.id',
   '$2y$12$GKWlA1h6Qf0oH1JWBGrFquM9.GpUSrPyJ6.YSWFB.FyO6PcfN0u3G');

INSERT IGNORE INTO tasks (user_id, judul, deskripsi, prioritas, status, deadline) VALUES
  (1, 'Buat laporan bulanan',   'Laporan keuangan Q1 2026',      'tinggi', 'proses',  '2026-04-15'),
  (1, 'Meeting tim desain',     'Review mockup aplikasi mobile', 'sedang', 'belum',   '2026-04-20'),
  (1, 'Update dokumentasi API', 'Tambah endpoint baru ke docs',  'rendah', 'selesai', '2026-04-10');
