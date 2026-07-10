-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2026 at 01:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sisfopenggajian_ester`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id_absensi` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id_absensi`, `id_karyawan`, `tanggal`, `jam_masuk`, `jam_keluar`, `status`) VALUES
(1, 5, '2026-06-01', '08:02:00', '17:05:00', 'Hadir'),
(2, 5, '2026-06-02', '08:00:00', '17:00:00', 'Hadir'),
(3, 6, '2026-06-01', '08:10:00', '17:00:00', 'Hadir'),
(4, 6, '2026-06-02', NULL, NULL, 'Izin'),
(5, 7, '2026-06-01', '07:58:00', '17:02:00', 'Hadir'),
(6, 7, '2026-06-02', '08:05:00', '17:00:00', 'Hadir'),
(7, 8, '2026-06-01', NULL, NULL, 'Sakit'),
(8, 8, '2026-06-02', '08:07:00', '17:00:00', 'Hadir'),
(9, 9, '2026-06-01', '08:00:00', '17:10:00', 'Hadir'),
(10, 9, '2026-06-02', '08:03:00', '17:00:00', 'Hadir'),
(11, 10, '2026-06-01', '08:01:00', '17:00:00', 'Hadir'),
(12, 10, '2026-06-02', NULL, NULL, 'Alpha'),
(13, 11, '2026-06-03', '07:55:00', '17:05:00', 'Hadir'),
(14, 11, '2026-06-04', '08:00:00', '17:00:00', 'Hadir'),
(15, 12, '2026-06-03', '08:02:00', '17:00:00', 'Hadir'),
(16, 12, '2026-06-04', NULL, NULL, 'Izin'),
(17, 13, '2026-06-03', '08:00:00', '17:00:00', 'Hadir'),
(18, 13, '2026-06-04', '08:04:00', '17:08:00', 'Hadir'),
(19, 14, '2026-06-03', '08:00:00', '17:00:00', 'Hadir'),
(20, 14, '2026-06-04', '08:01:00', '17:00:00', 'Hadir'),
(21, 15, '2026-06-05', '07:50:00', '17:15:00', 'Hadir'),
(22, 15, '2026-06-08', '07:55:00', '17:00:00', 'Hadir'),
(23, 16, '2026-06-05', '08:00:00', '17:00:00', 'Hadir'),
(24, 16, '2026-06-08', NULL, NULL, 'Sakit'),
(25, 17, '2026-06-05', '07:52:00', '17:10:00', 'Hadir'),
(26, 17, '2026-06-08', '07:58:00', '17:05:00', 'Hadir'),
(27, 18, '2026-06-05', '08:00:00', '17:00:00', 'Hadir'),
(28, 18, '2026-06-08', '08:02:00', '17:00:00', 'Hadir'),
(29, 19, '2026-06-05', '07:45:00', '17:20:00', 'Hadir'),
(30, 19, '2026-06-08', '07:50:00', '17:15:00', 'Hadir');

-- --------------------------------------------------------

--
-- Table structure for table `akun`
--

CREATE TABLE `akun` (
  `id_akun` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','karyawan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`id_akun`, `username`, `password`, `role`) VALUES
(11, 'admin', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'admin'),
(12, 'andi', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(13, 'rina', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(14, 'doni', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(15, 'maria', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(16, 'budi', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(17, 'lestari', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(18, 'hendra', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(19, 'siska', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(20, 'rudi', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(21, 'dewi', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(22, 'tono', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(23, 'yanti', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(24, 'bakti', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(25, 'nova', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan'),
(26, 'johan', '$2b$10$Ar42.kfXmZHzVCb8b8XLwu7VaiGvBk1jzEW5Al.63nbunBHRd34Hi', 'karyawan');

-- --------------------------------------------------------

--
-- Table structure for table `gaji`
--

CREATE TABLE `gaji` (
  `id_gaji` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `bulan` tinyint(4) NOT NULL,
  `tahun` year(4) NOT NULL,
  `perolehan_gaji` int(11) NOT NULL,
  `potongan_gaji` int(11) NOT NULL,
  `total_gaji` int(11) NOT NULL,
  `tanggal_gaji` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gaji`
--

INSERT INTO `gaji` (`id_gaji`, `id_karyawan`, `bulan`, `tahun`, `perolehan_gaji`, `potongan_gaji`, `total_gaji`, `tanggal_gaji`) VALUES
(3, 5, 6, '2026', 3350000, 0, 3350000, '2026-06-30'),
(4, 6, 6, '2026', 3200000, 20000, 3180000, '2026-06-30'),
(5, 7, 6, '2026', 3400000, 0, 3400000, '2026-06-30'),
(6, 8, 6, '2026', 3200000, 300000, 2900000, '2026-06-30'),
(7, 9, 6, '2026', 3300000, 100000, 3200000, '2026-06-30'),
(8, 10, 6, '2026', 3200000, 30000, 3170000, '2026-06-30'),
(9, 11, 6, '2026', 4700000, 0, 4700000, '2026-06-30'),
(10, 12, 6, '2026', 4650000, 250000, 4400000, '2026-06-30'),
(11, 13, 6, '2026', 4750000, 150000, 4600000, '2026-06-30'),
(12, 14, 6, '2026', 4400000, 400000, 4000000, '2026-06-30'),
(13, 15, 6, '2026', 7000000, 0, 7000000, '2026-06-30'),
(14, 16, 6, '2026', 7100000, 200000, 6900000, '2026-06-30'),
(15, 17, 6, '2026', 10300000, 0, 10300000, '2026-06-30'),
(16, 18, 6, '2026', 9500000, 500000, 9000000, '2026-06-30'),
(17, 19, 6, '2026', 16000000, 300000, 15700000, '2026-06-30');

-- --------------------------------------------------------

--
-- Table structure for table `jabatan`
--

CREATE TABLE `jabatan` (
  `id_jabatan` int(11) NOT NULL,
  `nama_jabatan` varchar(100) NOT NULL,
  `gaji_pokok` int(11) NOT NULL,
  `tunjangan_jabatan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jabatan`
--

INSERT INTO `jabatan` (`id_jabatan`, `nama_jabatan`, `gaji_pokok`, `tunjangan_jabatan`) VALUES
(103, 'Staff', 2900000, 300000),
(104, 'Team Leader', 3800000, 600000),
(105, 'Site Manager', 5500000, 1000000),
(106, 'Manager', 8000000, 1500000),
(107, 'General Manager', 12000000, 2500000);

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id_karyawan` int(11) NOT NULL,
  `id_akun` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `id_akun`, `id_jabatan`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `no_hp`, `tanggal_masuk`) VALUES
(5, 12, 103, 'Andi Saragih', 'L', '1998-03-12', 'Jl. Sutomo No. 12, Siantar Barat, Pematangsiantar', '081234500001', '2022-01-10'),
(6, 13, 103, 'Rina Purba', 'P', '1999-07-25', 'Jl. Merdeka No. 45, Siantar Timur, Pematangsiantar', '081234500002', '2022-03-15'),
(7, 14, 103, 'Doni Sinaga', 'L', '1997-11-02', 'Jl. Asahan No. 8, Siantar Utara, Pematangsiantar', '081234500003', '2021-09-01'),
(8, 15, 103, 'Maria Damanik', 'P', '2000-01-18', 'Jl. Kartini No. 21, Siantar Selatan, Pematangsiantar', '081234500004', '2023-02-20'),
(9, 16, 103, 'Budi Sitorus', 'L', '1996-05-09', 'Jl. Pane No. 3, Siantar Marihat, Pematangsiantar', '081234500005', '2020-11-05'),
(10, 17, 103, 'Lestari Silalahi', 'P', '1999-09-30', 'Jl. Diponegoro No. 17, Siantar Martoba, Pematangsiantar', '081234500006', '2022-06-13'),
(11, 18, 104, 'Hendra Simanjuntak', 'L', '1993-02-14', 'Jl. Thamrin No. 9, Siantar Barat, Pematangsiantar', '081234500007', '2019-04-01'),
(12, 19, 104, 'Siska Nainggolan', 'P', '1994-08-22', 'Jl. Cokroaminoto No. 5, Siantar Timur, Pematangsiantar', '081234500008', '2019-07-22'),
(13, 20, 104, 'Rudi Situmorang', 'L', '1992-12-05', 'Jl. Rakoetta Sembiring No. 11, Siantar Utara, Pematangsiantar', '081234500009', '2020-01-20'),
(14, 21, 104, 'Dewi Tampubolon', 'P', '1995-04-17', 'Jl. Sisingamangaraja No. 33, Siantar Selatan, Pematangsiantar', '081234500010', '2020-08-10'),
(15, 22, 105, 'Tono Manurung', 'L', '1988-06-21', 'Jl. Medan No. 27, Siantar Marimbun, Pematangsiantar', '081234500011', '2017-03-05'),
(16, 23, 105, 'Yanti Hutabarat', 'P', '1989-10-11', 'Jl. Pdt J. Wismar Saragih No. 14, Siantar Sitalasari, Pematangsiantar', '081234500012', '2017-09-18'),
(17, 24, 106, 'Bakti Simatupang', 'L', '1985-01-09', 'Jl. Bah Bolon No. 6, Siantar Barat, Pematangsiantar', '081234500013', '2015-05-11'),
(18, 25, 106, 'Nova Napitupulu', 'P', '1986-03-27', 'Jl. Damai No. 19, Siantar Timur, Pematangsiantar', '081234500014', '2016-02-08'),
(19, 26, 107, 'Johan Panjaitan', 'L', '1980-07-04', 'Jl. Sangnawaluh No. 2, Siantar Selatan, Pematangsiantar', '081234500015', '2012-01-15');

-- --------------------------------------------------------

--
-- Table structure for table `perolehan_gaji`
--

CREATE TABLE `perolehan_gaji` (
  `id_perolehan` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `nama_perolehan` varchar(20) NOT NULL,
  `total_perolehan` int(11) NOT NULL,
  `tanggal_perolehan` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perolehan_gaji`
--

INSERT INTO `perolehan_gaji` (`id_perolehan`, `id_karyawan`, `nama_perolehan`, `total_perolehan`, `tanggal_perolehan`) VALUES
(5, 5, 'Bonus Lembur', 150000, '2026-06-25'),
(6, 7, 'Bonus Kinerja', 200000, '2026-06-25'),
(7, 9, 'Uang Transport', 100000, '2026-06-25'),
(8, 11, 'Bonus Kinerja', 300000, '2026-06-25'),
(9, 12, 'Bonus Lembur', 250000, '2026-06-25'),
(10, 13, 'Insentif', 350000, '2026-06-25'),
(11, 15, 'Bonus Kinerja', 500000, '2026-06-25'),
(12, 16, 'THR', 600000, '2026-06-25'),
(13, 17, 'Bonus Kinerja', 800000, '2026-06-25'),
(14, 19, 'Insentif', 1500000, '2026-06-25');

-- --------------------------------------------------------

--
-- Table structure for table `potongan_gaji`
--

CREATE TABLE `potongan_gaji` (
  `id_potongan` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `nama_potongan` varchar(20) NOT NULL,
  `total_potongan` int(11) NOT NULL,
  `tanggal_potongan` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `potongan_gaji`
--

INSERT INTO `potongan_gaji` (`id_potongan`, `id_karyawan`, `nama_potongan`, `total_potongan`, `tanggal_potongan`) VALUES
(3, 6, 'Denda Terlambat', 20000, '2026-06-27'),
(4, 8, 'Kasbon', 300000, '2026-06-27'),
(5, 9, 'Alpha', 100000, '2026-06-27'),
(6, 10, 'Denda Terlambat', 30000, '2026-06-27'),
(7, 12, 'Kasbon', 250000, '2026-06-27'),
(8, 13, 'BPJS', 150000, '2026-06-27'),
(9, 14, 'Kasbon', 400000, '2026-06-27'),
(10, 16, 'BPJS', 200000, '2026-06-27'),
(11, 18, 'Kasbon', 500000, '2026-06-27'),
(12, 19, 'BPJS', 300000, '2026-06-27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indexes for table `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id_akun`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `gaji`
--
ALTER TABLE `gaji`
  ADD PRIMARY KEY (`id_gaji`),
  ADD UNIQUE KEY `uq_karyawan_periode` (`id_karyawan`,`bulan`,`tahun`);

--
-- Indexes for table `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id_jabatan`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD KEY `id_akun` (`id_akun`),
  ADD KEY `id_jabatan` (`id_jabatan`);

--
-- Indexes for table `perolehan_gaji`
--
ALTER TABLE `perolehan_gaji`
  ADD PRIMARY KEY (`id_perolehan`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indexes for table `potongan_gaji`
--
ALTER TABLE `potongan_gaji`
  ADD PRIMARY KEY (`id_potongan`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `akun`
--
ALTER TABLE `akun`
  MODIFY `id_akun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `gaji`
--
ALTER TABLE `gaji`
  MODIFY `id_gaji` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `jabatan`
--
ALTER TABLE `jabatan`
  MODIFY `id_jabatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `perolehan_gaji`
--
ALTER TABLE `perolehan_gaji`
  MODIFY `id_perolehan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `potongan_gaji`
--
ALTER TABLE `potongan_gaji`
  MODIFY `id_potongan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gaji`
--
ALTER TABLE `gaji`
  ADD CONSTRAINT `gaji_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `karyawan_ibfk_2` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `perolehan_gaji`
--
ALTER TABLE `perolehan_gaji`
  ADD CONSTRAINT `perolehan_gaji_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `potongan_gaji`
--
ALTER TABLE `potongan_gaji`
  ADD CONSTRAINT `potongan_gaji_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
