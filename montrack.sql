CREATE DATABASE IF NOT EXISTS montrack
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE montrack;

CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nama     VARCHAR(100) NOT NULL,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE kategori (
    id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE transaksi (
    id_transaksi    INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    kategori_id     INT NOT NULL,
    jenis_transaksi ENUM('pemasukan','pengeluaran') NOT NULL,
    nominal         DECIMAL(15,2) NOT NULL,
    tanggal         DATE NOT NULL,
    keterangan      VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (user_id)     REFERENCES users(id)             ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id_kategori) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO kategori (nama_kategori) VALUES
('Makanan'),('Parkir'),('Transportasi'),('Pakaian'),
('Transfer'),('Pendidikan'),('Hiburan'),('Lainnya');
