-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2026 at 09:37 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spk_fuzzy_sugeno`
--

-- --------------------------------------------------------

--
-- Table structure for table `aturan_fuzzy`
--

CREATE TABLE `aturan_fuzzy` (
  `id_aturan` int(11) NOT NULL,
  `nama_aturan` varchar(200) NOT NULL,
  `output_nilai` float NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aturan_fuzzy`
--

INSERT INTO `aturan_fuzzy` (`id_aturan`, `nama_aturan`, `output_nilai`, `keterangan`) VALUES
(22, 'Rule Wawancara - Tinggi', 100, 'Sangat Layak'),
(23, 'Rule Utama Wawancara - Tinggi', 80, 'Layak'),
(24, 'Rule Utama Wawancara - Tinggi', 60, 'Cukup Layak'),
(25, 'Rule Utama Wawancara - Tinggi', 80, 'Layak'),
(26, 'Rule Utama Wawancara - Tinggi', 80, 'Layak'),
(27, 'Rule Utama Wawancara - Tinggi', 60, 'Cukup Layak'),
(28, 'Rule Utama Wawancara - Tinggi', 60, 'Cukup Layak'),
(29, 'Rule Utama Wawancara - Tinggi', 60, 'Cukup Layak'),
(30, 'Rule Utama Wawancara - Tinggi', 40, 'Tidak Layak'),
(31, 'Rule Utama Wawancara - Sedang', 80, 'Layak'),
(32, 'Rule Utama Wawancara - Sedang', 80, 'Layak'),
(33, 'Rule Utama Wawancara - Sedang', 60, 'Cukup Layak'),
(34, 'Rule Utama Wawancara - Sedang', 80, 'Layak'),
(35, 'Rule Utama Wawancara - Sedang', 60, 'Cukup Layak'),
(36, 'Rule Utama Wawancara - Sedang', 60, 'Cukup Layak'),
(37, 'Rule Utama Wawancara - Sedang', 60, 'Cukup Layak'),
(38, 'Rule Utama Wawancara - Sedang', 60, 'Cukup Layak'),
(39, 'Rule Utama Wawancara - Sedang', 40, 'Tidak Layak'),
(40, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak'),
(41, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak'),
(42, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak'),
(43, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak'),
(44, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak'),
(45, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak'),
(46, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak'),
(47, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak'),
(48, 'Rule Utama Wawancara - Rendah', 40, 'Tidak Layak');

-- --------------------------------------------------------

--
-- Table structure for table `detail_aturan_fuzzy`
--

CREATE TABLE `detail_aturan_fuzzy` (
  `id_detail` int(11) NOT NULL,
  `id_aturan` int(11) NOT NULL,
  `id_himpunan` int(11) NOT NULL,
  `operator` enum('AND','OR') DEFAULT 'AND'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_aturan_fuzzy`
--

INSERT INTO `detail_aturan_fuzzy` (`id_detail`, `id_aturan`, `id_himpunan`, `operator`) VALUES
(60, 22, 10, 'AND'),
(61, 22, 19, 'AND'),
(62, 22, 22, 'AND'),
(63, 23, 10, 'AND'),
(64, 23, 18, 'AND'),
(65, 23, 22, 'AND'),
(66, 24, 10, 'AND'),
(67, 24, 17, 'AND'),
(68, 24, 22, 'AND'),
(69, 25, 9, 'AND'),
(70, 25, 19, 'AND'),
(71, 25, 22, 'AND'),
(72, 26, 9, 'AND'),
(73, 26, 18, 'AND'),
(74, 26, 22, 'AND'),
(75, 27, 9, 'AND'),
(76, 27, 17, 'AND'),
(77, 27, 22, 'AND'),
(78, 28, 8, 'AND'),
(79, 28, 19, 'AND'),
(80, 28, 22, 'AND'),
(81, 29, 8, 'AND'),
(82, 29, 18, 'AND'),
(83, 29, 22, 'AND'),
(84, 30, 8, 'AND'),
(85, 30, 17, 'AND'),
(86, 30, 22, 'AND'),
(87, 31, 10, 'AND'),
(88, 31, 19, 'AND'),
(89, 31, 21, 'AND'),
(90, 32, 10, 'AND'),
(91, 32, 18, 'AND'),
(92, 32, 21, 'AND'),
(93, 33, 10, 'AND'),
(94, 33, 17, 'AND'),
(95, 33, 21, 'AND'),
(96, 34, 9, 'AND'),
(97, 34, 19, 'AND'),
(98, 34, 21, 'AND'),
(99, 35, 9, 'AND'),
(100, 35, 18, 'AND'),
(101, 35, 21, 'AND'),
(102, 36, 9, 'AND'),
(103, 36, 17, 'AND'),
(104, 36, 21, 'AND'),
(105, 37, 8, 'AND'),
(106, 37, 19, 'AND'),
(107, 37, 21, 'AND'),
(108, 38, 8, 'AND'),
(109, 38, 18, 'AND'),
(110, 38, 21, 'AND'),
(111, 39, 8, 'AND'),
(112, 39, 17, 'AND'),
(113, 39, 21, 'AND'),
(114, 40, 10, 'AND'),
(115, 40, 19, 'AND'),
(116, 40, 20, 'AND'),
(117, 41, 10, 'AND'),
(118, 41, 18, 'AND'),
(119, 41, 20, 'AND'),
(120, 42, 10, 'AND'),
(121, 42, 17, 'AND'),
(122, 42, 20, 'AND'),
(123, 43, 9, 'AND'),
(124, 43, 19, 'AND'),
(125, 43, 20, 'AND'),
(126, 44, 9, 'AND'),
(127, 44, 18, 'AND'),
(128, 44, 20, 'AND'),
(129, 45, 9, 'AND'),
(130, 45, 17, 'AND'),
(131, 45, 20, 'AND'),
(132, 46, 8, 'AND'),
(133, 46, 19, 'AND'),
(134, 46, 20, 'AND'),
(135, 47, 8, 'AND'),
(136, 47, 18, 'AND'),
(137, 47, 20, 'AND'),
(138, 48, 8, 'AND'),
(139, 48, 17, 'AND'),
(140, 48, 20, 'AND');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_perhitungan`
--

CREATE TABLE `hasil_perhitungan` (
  `id_hasil` bigint(100) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `nilai_defuzzifikasi` float NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `tanggal_hitung` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hasil_perhitungan`
--

INSERT INTO `hasil_perhitungan` (`id_hasil`, `id_siswa`, `nilai_defuzzifikasi`, `kategori`, `tanggal_hitung`, `id_user`) VALUES
(3401, 16, 46, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3402, 17, 42.4447, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3403, 18, 63.4555, 'Layak', '2026-08-02 07:11:01', 6),
(3404, 19, 43.2658, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3405, 20, 74.5455, 'Layak', '2026-08-02 07:11:01', 6),
(3406, 21, 54.0664, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3407, 22, 78.3195, 'Sangat Layak', '2026-08-02 07:11:01', 6),
(3408, 23, 55.6345, 'Layak', '2026-08-02 07:11:01', 6),
(3409, 24, 62.885, 'Layak', '2026-08-02 07:11:01', 6),
(3410, 25, 42.2521, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3411, 26, 52.0447, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3412, 27, 46.3456, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3413, 28, 72.88, 'Layak', '2026-08-02 07:11:01', 6),
(3414, 29, 60, 'Layak', '2026-08-02 07:11:01', 6),
(3415, 30, 44.9031, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3416, 31, 53.8002, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3417, 32, 88.07, 'Sangat Layak', '2026-08-02 07:11:01', 6),
(3418, 33, 60, 'Layak', '2026-08-02 07:11:01', 6),
(3419, 34, 62.18, 'Layak', '2026-08-02 07:11:01', 6),
(3420, 35, 60, 'Layak', '2026-08-02 07:11:01', 6),
(3421, 36, 60.4312, 'Layak', '2026-08-02 07:11:01', 6),
(3422, 37, 62.7586, 'Layak', '2026-08-02 07:11:01', 6),
(3423, 38, 72.6578, 'Layak', '2026-08-02 07:11:01', 6),
(3424, 39, 51.1111, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3425, 40, 60, 'Layak', '2026-08-02 07:11:01', 6),
(3426, 41, 50, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3427, 42, 41.1452, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3428, 43, 66.2, 'Layak', '2026-08-02 07:11:01', 6),
(3430, 45, 40, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3431, 46, 61.8122, 'Layak', '2026-08-02 07:11:01', 6),
(3432, 47, 45.9077, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3433, 48, 46, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3434, 49, 62.1053, 'Layak', '2026-08-02 07:11:01', 6),
(3435, 50, 71.6102, 'Layak', '2026-08-02 07:11:01', 6),
(3436, 51, 60.6067, 'Layak', '2026-08-02 07:11:01', 6),
(3437, 52, 72.0057, 'Layak', '2026-08-02 07:11:01', 6),
(3438, 53, 62.2222, 'Layak', '2026-08-02 07:11:01', 6),
(3439, 54, 46.7918, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3440, 55, 65.56, 'Layak', '2026-08-02 07:11:01', 6),
(3441, 56, 71.1, 'Layak', '2026-08-02 07:11:01', 6),
(3442, 57, 65.9329, 'Layak', '2026-08-02 07:11:01', 6),
(3443, 58, 71.1111, 'Layak', '2026-08-02 07:11:01', 6),
(3444, 59, 63.1579, 'Layak', '2026-08-02 07:11:01', 6),
(3445, 60, 66, 'Layak', '2026-08-02 07:11:01', 6),
(3446, 61, 60.7862, 'Layak', '2026-08-02 07:11:01', 6),
(3447, 62, 66.9122, 'Layak', '2026-08-02 07:11:01', 6),
(3448, 63, 71.46, 'Layak', '2026-08-02 07:11:01', 6),
(3449, 64, 50.7983, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3450, 65, 40, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3451, 66, 57.8643, 'Layak', '2026-08-02 07:11:01', 6),
(3452, 67, 40, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3453, 68, 66.0636, 'Layak', '2026-08-02 07:11:01', 6),
(3454, 69, 60, 'Layak', '2026-08-02 07:11:01', 6),
(3455, 70, 51.9654, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3456, 71, 61.1135, 'Layak', '2026-08-02 07:11:01', 6),
(3457, 72, 44.3775, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3458, 73, 48.7732, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3459, 74, 53.2331, 'Cukup Layak', '2026-08-02 07:11:01', 6),
(3460, 75, 60, 'Layak', '2026-08-02 07:11:01', 6),
(3461, 76, 72.6422, 'Layak', '2026-08-02 07:11:01', 6),
(3462, 77, 62.12, 'Layak', '2026-08-02 07:11:01', 6),
(3463, 44, 40, 'Cukup Layak', '2026-08-02 07:13:27', 6);

-- --------------------------------------------------------

--
-- Table structure for table `himpunan_fuzzy`
--

CREATE TABLE `himpunan_fuzzy` (
  `id_himpunan` int(11) NOT NULL,
  `id_kriteria` int(11) NOT NULL,
  `nama_himpunan` varchar(100) NOT NULL,
  `tipe_fungsi` enum('trimf','trapmf') NOT NULL,
  `nilai_a` float NOT NULL,
  `nilai_b` float NOT NULL,
  `nilai_c` float NOT NULL,
  `nilai_d` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `himpunan_fuzzy`
--

INSERT INTO `himpunan_fuzzy` (`id_himpunan`, `id_kriteria`, `nama_himpunan`, `tipe_fungsi`, `nilai_a`, `nilai_b`, `nilai_c`, `nilai_d`) VALUES
(8, 2, 'Tinggi Badan (Rendah)', 'trimf', 160, 165, 170, NULL),
(9, 2, 'Tinggi Badan (Sedang)', 'trimf', 165, 172.5, 180, NULL),
(10, 2, 'Tinggi Badan (Tinggi)', 'trimf', 175, 180, 185, NULL),
(17, 3, 'Pengetahuan (Rendah)', 'trimf', 70, 75, 80, NULL),
(18, 3, 'Pengetahuan (Sedang)', 'trimf', 75, 85, 95, NULL),
(19, 3, 'Pengetahuan (Tinggi)', 'trimf', 90, 95, 100, NULL),
(20, 5, 'Wawancara (Rendah)', 'trimf', 60, 70, 80, NULL),
(21, 5, 'Wawancara (Sedang)', 'trimf', 70, 80, 90, NULL),
(22, 5, 'Wawancara (Tinggi)', 'trimf', 80, 90, 100, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kriteria`
--

CREATE TABLE `kriteria` (
  `id_kriteria` int(11) NOT NULL,
  `nama_kriteria` varchar(200) NOT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `nilai_min` float DEFAULT 0,
  `nilai_max` float DEFAULT 100,
  `keterangan` text DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kriteria`
--

INSERT INTO `kriteria` (`id_kriteria`, `nama_kriteria`, `satuan`, `nilai_min`, `nilai_max`, `keterangan`, `urutan`, `created_at`, `updated_at`) VALUES
(2, 'Tinggi Badan', 'Cm', 160, 185, 'Tinggi Badan Kandidat', 0, '2026-02-21 15:24:01', '2026-06-06 09:32:13'),
(3, 'Pengetahuan Paskibra', 'nilai', 70, 100, 'Nilai Pengetahuan Kandidat', 2, '2026-02-21 15:48:49', '2026-06-06 09:33:10'),
(5, 'Wawancara', 'Nilai', 60, 100, 'Nilai Wawancara Kandidat', 1, '2026-05-02 14:28:55', '2026-06-06 09:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `penilaian`
--

CREATE TABLE `penilaian` (
  `id_penilaian` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `id_kriteria` int(11) NOT NULL,
  `nilai_input` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penilaian`
--

INSERT INTO `penilaian` (`id_penilaian`, `id_siswa`, `id_kriteria`, `nilai_input`, `created_at`) VALUES
(83, 59, 2, 167, '2026-06-17 08:42:34'),
(84, 59, 5, 82, '2026-06-17 08:42:34'),
(85, 59, 3, 86, '2026-06-17 08:42:34'),
(98, 22, 2, 173, '2026-06-17 08:45:12'),
(99, 22, 5, 83, '2026-06-17 08:45:12'),
(100, 22, 3, 93.99, '2026-06-17 08:45:12'),
(107, 53, 2, 173, '2026-06-17 08:47:40'),
(108, 53, 5, 81, '2026-06-17 08:47:40'),
(109, 53, 3, 87, '2026-06-17 08:47:40'),
(128, 60, 2, 173, '2026-06-17 08:54:14'),
(129, 60, 5, 83, '2026-06-17 08:54:14'),
(130, 60, 3, 82, '2026-06-17 08:54:14'),
(152, 37, 2, 166, '2026-06-17 09:20:11'),
(153, 37, 5, 82, '2026-06-17 09:20:11'),
(154, 37, 3, 80, '2026-06-17 09:20:11'),
(164, 33, 2, 172, '2026-06-17 09:22:36'),
(165, 33, 5, 80, '2026-06-17 09:22:36'),
(166, 33, 3, 85, '2026-06-17 09:22:36'),
(167, 75, 2, 170, '2026-06-17 09:23:08'),
(168, 75, 5, 80, '2026-06-17 09:23:08'),
(169, 75, 3, 89, '2026-06-17 09:23:08'),
(185, 48, 2, 175, '2026-06-17 09:40:13'),
(186, 48, 5, 71, '2026-06-17 09:40:13'),
(187, 48, 3, 93, '2026-06-17 09:40:13'),
(191, 20, 2, 170, '2026-06-17 09:41:16'),
(192, 20, 5, 87, '2026-06-17 09:41:16'),
(193, 20, 3, 91, '2026-06-17 09:41:16'),
(200, 58, 2, 171, '2026-06-17 09:42:57'),
(201, 58, 5, 86, '2026-06-17 09:42:57'),
(202, 58, 3, 90, '2026-06-17 09:42:57'),
(209, 16, 2, 172, '2026-06-17 09:45:55'),
(210, 16, 5, 73, '2026-06-17 09:45:55'),
(211, 16, 3, 86, '2026-06-17 09:45:55'),
(212, 39, 2, 175, '2026-06-17 09:46:27'),
(213, 39, 5, 76, '2026-06-17 09:46:27'),
(214, 39, 3, 90, '2026-06-17 09:46:27'),
(218, 36, 2, 172.61, '2026-06-17 09:47:33'),
(219, 36, 5, 80.21, '2026-06-17 09:47:33'),
(220, 36, 3, 85.47, '2026-06-17 09:47:33'),
(221, 77, 2, 173, '2026-06-17 09:47:49'),
(222, 77, 5, 81.06, '2026-06-17 09:47:49'),
(223, 77, 3, 85.16, '2026-06-17 09:47:49'),
(233, 23, 2, 165.19, '2026-06-17 09:48:57'),
(234, 23, 5, 77.96, '2026-06-17 09:48:57'),
(235, 23, 3, 83.54, '2026-06-17 09:48:57'),
(236, 34, 2, 172, '2026-06-17 09:49:09'),
(237, 34, 5, 81.09, '2026-06-17 09:49:09'),
(238, 34, 3, 85.95, '2026-06-17 09:49:09'),
(242, 54, 2, 166.63, '2026-06-17 09:49:45'),
(243, 54, 5, 72.41, '2026-06-17 09:49:45'),
(244, 54, 3, 82.81, '2026-06-17 09:49:45'),
(248, 73, 2, 167.44, '2026-06-17 09:50:12'),
(249, 73, 5, 73.29, '2026-06-17 09:50:12'),
(250, 73, 3, 82.34, '2026-06-17 09:50:12'),
(251, 42, 2, 165.35, '2026-06-17 09:50:22'),
(252, 42, 5, 70.19, '2026-06-17 09:50:22'),
(253, 42, 3, 80.79, '2026-06-17 09:50:22'),
(254, 41, 2, 168.23, '2026-06-17 09:50:32'),
(255, 41, 5, 74.36, '2026-06-17 09:50:32'),
(256, 41, 3, 83.4, '2026-06-17 09:50:32'),
(260, 74, 2, 165.24, '2026-06-17 09:51:44'),
(261, 74, 5, 76.72, '2026-06-17 09:51:44'),
(262, 74, 3, 82.61, '2026-06-17 09:51:44'),
(278, 18, 2, 166.98, '2026-06-17 09:55:36'),
(279, 18, 5, 84.61, '2026-06-17 09:55:36'),
(280, 18, 3, 89.17, '2026-06-17 09:55:36'),
(284, 45, 2, 161.96, '2026-06-17 09:56:21'),
(285, 45, 5, 66.12, '2026-06-17 09:56:21'),
(286, 45, 3, 75.07, '2026-06-17 09:56:21'),
(299, 67, 2, 164.39, '2026-06-17 09:59:33'),
(300, 67, 5, 68.36, '2026-06-17 09:59:33'),
(301, 67, 3, 77.69, '2026-06-17 09:59:33'),
(302, 72, 2, 165.78, '2026-06-17 09:59:57'),
(303, 72, 5, 71.14, '2026-06-17 09:59:57'),
(304, 72, 3, 88.26, '2026-06-17 09:59:57'),
(305, 65, 2, 160.2, '2026-06-17 10:00:25'),
(306, 65, 5, 63.78, '2026-06-17 10:00:25'),
(307, 65, 3, 75.47, '2026-06-17 10:00:25'),
(308, 43, 2, 173.76, '2026-06-17 10:00:47'),
(309, 43, 5, 83.1, '2026-06-17 10:00:47'),
(310, 43, 3, 84.87, '2026-06-17 10:00:47'),
(320, 68, 2, 173.18, '2026-06-17 10:02:48'),
(321, 68, 5, 82.48, '2026-06-17 10:02:48'),
(322, 68, 3, 80.7, '2026-06-17 10:02:48'),
(326, 27, 2, 177.67, '2026-06-17 10:08:37'),
(327, 27, 5, 71.05, '2026-06-17 10:08:37'),
(328, 27, 3, 75.72, '2026-06-17 10:08:37'),
(341, 25, 2, 166.09, '2026-06-17 10:11:12'),
(342, 25, 5, 76.51, '2026-06-17 10:11:12'),
(343, 25, 3, 74.81, '2026-06-17 10:11:12'),
(347, 30, 2, 164.97, '2026-06-17 10:12:05'),
(348, 30, 5, 73.83, '2026-06-17 10:12:05'),
(349, 30, 3, 78.29, '2026-06-17 10:12:05'),
(350, 44, 2, 165.39, '2026-06-17 10:12:32'),
(351, 44, 5, 68.92, '2026-06-17 10:12:32'),
(352, 44, 3, 78.86, '2026-06-17 10:12:32'),
(353, 64, 2, 171.98, '2026-06-17 10:12:56'),
(354, 64, 5, 76.22, '2026-06-17 10:12:56'),
(355, 64, 3, 77.56, '2026-06-17 10:12:56'),
(356, 47, 2, 169.1, '2026-06-17 10:13:21'),
(357, 47, 5, 71.23, '2026-06-17 10:13:21'),
(358, 47, 3, 90.07, '2026-06-17 10:13:21'),
(359, 26, 2, 165.24, '2026-06-17 10:13:41'),
(360, 26, 5, 78.08, '2026-06-17 10:13:41'),
(361, 26, 3, 79.75, '2026-06-17 10:13:41'),
(362, 21, 2, 165.3, '2026-06-17 10:14:11'),
(363, 21, 5, 78.7, '2026-06-17 10:14:11'),
(364, 21, 3, 90.58, '2026-06-17 10:14:11'),
(365, 57, 2, 175.77, '2026-06-17 10:14:39'),
(366, 57, 5, 82.92, '2026-06-17 10:14:39'),
(367, 57, 3, 77.58, '2026-06-17 10:14:39'),
(368, 17, 2, 174.75, '2026-06-17 10:15:06'),
(369, 17, 5, 70.58, '2026-06-17 10:15:06'),
(370, 17, 3, 76.67, '2026-06-17 10:15:06'),
(374, 55, 2, 172.81, '2026-06-17 10:15:48'),
(375, 55, 5, 82.78, '2026-06-17 10:15:48'),
(376, 55, 3, 84.03, '2026-06-17 10:15:48'),
(383, 66, 2, 166.17, '2026-06-17 10:16:53'),
(384, 66, 5, 85.17, '2026-06-17 10:16:53'),
(385, 66, 3, 79.13, '2026-06-17 10:16:53'),
(386, 31, 2, 166.35, '2026-06-17 10:17:47'),
(387, 31, 5, 85.49, '2026-06-17 10:17:47'),
(388, 31, 3, 77.89, '2026-06-17 10:17:47'),
(389, 28, 2, 173.53, '2026-06-17 10:18:52'),
(390, 28, 5, 86.44, '2026-06-17 10:18:52'),
(391, 28, 3, 84.07, '2026-06-17 10:18:52'),
(395, 71, 2, 165.39, '2026-06-17 10:19:47'),
(396, 71, 5, 87.27, '2026-06-17 10:19:47'),
(397, 71, 3, 80.57, '2026-06-17 10:19:47'),
(401, 51, 2, 165.21, '2026-06-17 10:20:48'),
(402, 51, 5, 82.19, '2026-06-17 10:20:48'),
(403, 51, 3, 81.48, '2026-06-17 10:20:48'),
(404, 76, 2, 173.83, '2026-06-17 10:21:35'),
(405, 76, 5, 87.09, '2026-06-17 10:21:35'),
(406, 76, 3, 90, '2026-06-17 10:21:35'),
(413, 56, 2, 173.42, '2026-06-17 10:22:57'),
(414, 56, 5, 85.55, '2026-06-17 10:22:57'),
(415, 56, 3, 87.53, '2026-06-17 10:22:57'),
(416, 32, 2, 177.23, '2026-06-17 10:23:31'),
(417, 32, 5, 94.18, '2026-06-17 10:23:31'),
(418, 32, 3, 93.55, '2026-06-17 10:23:31'),
(419, 38, 2, 175.51, '2026-06-17 10:25:34'),
(420, 38, 5, 85.58, '2026-06-17 10:25:34'),
(421, 38, 3, 82.52, '2026-06-17 10:25:34'),
(422, 63, 2, 174.96, '2026-06-17 10:25:55'),
(423, 63, 5, 85.73, '2026-06-17 10:25:55'),
(424, 63, 3, 80.99, '2026-06-17 10:25:55'),
(425, 62, 2, 168.66, '2026-06-17 10:26:17'),
(426, 62, 5, 86.12, '2026-06-17 10:26:17'),
(427, 62, 3, 80.83, '2026-06-17 10:26:17'),
(428, 46, 2, 165.83, '2026-06-17 10:26:41'),
(429, 46, 5, 85.55, '2026-06-17 10:26:41'),
(430, 46, 3, 88.55, '2026-06-17 10:26:41'),
(431, 24, 2, 166.44, '2026-06-17 10:27:19'),
(432, 24, 5, 87.65, '2026-06-17 10:27:19'),
(433, 24, 3, 87.75, '2026-06-17 10:27:19'),
(434, 35, 2, 163.82, '2026-06-17 10:27:55'),
(435, 35, 5, 89.54, '2026-06-17 10:27:55'),
(436, 35, 3, 95, '2026-06-17 10:27:55'),
(446, 61, 2, 165.32, '2026-06-17 10:29:59'),
(447, 61, 5, 86.38, '2026-06-17 10:29:59'),
(448, 61, 3, 87.26, '2026-06-17 10:29:59'),
(449, 52, 2, 168.83, '2026-06-17 10:30:28'),
(450, 52, 5, 85.94, '2026-06-17 10:30:28'),
(451, 52, 3, 94.39, '2026-06-17 10:30:28'),
(461, 69, 2, 163.6, '2026-06-17 10:32:01'),
(462, 69, 5, 85.94, '2026-06-17 10:32:01'),
(463, 69, 3, 95.32, '2026-06-17 10:32:01'),
(467, 29, 2, 164.87, '2026-06-17 10:32:57'),
(468, 29, 5, 82.57, '2026-06-17 10:32:57'),
(469, 29, 3, 91.94, '2026-06-17 10:32:57'),
(470, 50, 2, 175.45, '2026-06-17 10:33:52'),
(471, 50, 5, 85.05, '2026-06-17 10:33:52'),
(472, 50, 3, 87.04, '2026-06-17 10:33:52'),
(479, 40, 2, 164.91, '2026-06-17 10:34:56'),
(480, 40, 5, 86, '2026-06-17 10:34:56'),
(481, 40, 3, 90.21, '2026-06-17 10:34:56'),
(482, 19, 2, 165.3, '2026-06-17 10:35:21'),
(483, 19, 5, 71.05, '2026-06-17 10:35:21'),
(484, 19, 3, 87.97, '2026-06-17 10:35:21'),
(485, 70, 2, 166.02, '2026-06-17 10:35:45'),
(486, 70, 5, 76.25, '2026-06-17 10:35:45'),
(487, 70, 3, 86.99, '2026-06-17 10:35:45'),
(500, 49, 2, 160, '2026-08-07 03:28:44'),
(501, 49, 5, 69.99, '2026-08-07 03:28:44'),
(502, 49, 3, 70, '2026-08-07 03:28:44');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id_role` int(11) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id_role`, `role`) VALUES
(1, 'Admin'),
(2, 'Siswa'),
(3, 'Penyeleksi');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `asal_sekolah` varchar(255) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tinggi_badan` int(11) DEFAULT NULL,
  `berat_badan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `name`, `email`, `asal_sekolah`, `jenis_kelamin`, `tinggi_badan`, `berat_badan`) VALUES
(16, 'Revan Jovanis', 'revan@gmail.com', 'SMAS Yadika 10', 'L', 170, 70),
(17, 'Ihsan Saputra', 'ihsan@gmail.com', 'SMA Bina Bhakti', 'L', 172, 72),
(18, 'Adinda Putri', 'adinda@gmail.com', 'SMAN 27 Kabupaten Tangerang', 'P', 165, 65),
(19, 'Siti Muslimah', 'siti@gmail.com', 'SMAN 1 Kabupaten Tangerang', 'P', 166, 65),
(20, 'Odilia Cinta Cristiani Simbolon', 'Odilia@gmail.com', 'SMAN 15 Kabupaten Tangerang', 'P', 168, 65),
(21, 'Ganesha Priscilla Boru Barutu', 'ganesha@gmail.com', 'SMAN 15 Kabupaten Tangerang', 'P', 165, 78),
(22, 'Azriel Gheisan', 'azriel@gmail.com', 'SMAN 18 Kabupaten Tangerang', 'L', 174, 70),
(23, 'Sasya Tiara Ramadhani', 'sasya@gmail.com', 'SMAN 19 Kabupaten Tangerang', 'P', 165, 54),
(24, 'Putri Aurell Silaban', 'putri@gmail.com', 'SMAN 19 Kabupaten Tangerang', 'P', 165, 70),
(25, 'Dina Nur Priatna', 'dina@gmail.com', 'SMAN 19 Kabupaten Tangerang', 'P', 168, 60),
(26, 'Gaitsa Nur Hanifah', 'gaitsa@gmail.com', 'SMAN 24 Kabupaten Tangerang', 'P', 162, 60),
(27, 'Dico Alriyus Naftael Sihite', 'dico@gmail.com', 'SMAN 24 Kabupaten Tangerang', 'L', 180, 85),
(28, 'Lintang Aryo Wicaksono', 'lintang@gmail.com', 'SMAN 24 Kabupaten Tangerang', 'L', 168, 65),
(29, 'Safira Amalia', 'safira@gmail.com', 'SMAN 24 Kabupaten Tangerang', 'P', 168, 69),
(30, 'Dinda Mutiara', 'dinda@gmail.com', 'SMAN 24 Kabupaten Tangerang', 'P', 165, 66),
(31, 'Khofifah Dwi Hapsari', 'khofifah@gmail.com', 'SMAN 24 Kabupaten Tangerang', 'P', 166, 68),
(32, 'Muhammad Fazar Alamsyah', 'fazar@gmail.com', 'SMAN 25 Kabupaten Tangerang', 'L', 170, 68),
(33, 'Mika Aulia Hutasoit', 'mika@gmail.com', 'SMAN 27 Kabupaten Tangerang', 'P', 170, 77),
(34, 'Satria Dika Pratama', 'satria@gmail.com', 'SMAN 27 Kabupaten Tangerang', 'L', 164, 55),
(35, 'Ramadhani Annida Suwandi', 'ramadhani@gmail.com', 'SMAN 27 Kabupaten Tangerang', 'P', 169, 59),
(36, 'Rio Aprilio Zidan', 'rio@gmail.com', 'SMAN 27 Kabupaten Tangerang', 'L', 180, 70),
(37, 'Keysha Adeeva Taufiq', 'Keysha@gmail.com', 'SMAN 3 Kabupaten Tangerang', 'P', 170, 70),
(38, 'Muhammad Ilham Bustomi', 'ilham@gmail.com', 'SMAN 30 Kabupaten Tangerang', 'L', 177, 79),
(39, 'Rifky', 'rifky@gmail.com', 'SMAN 30 Kabupaten Tangerang', 'L', 169, 70),
(40, 'Selvi Aulia Cahyani', 'selvi@gmail.com', 'SMAN 9 Kabupaten Tangerang', 'P', 165, 69),
(41, 'Tri Deswita Shaeshar', 'tri@gmail.com', 'SMAN 9 Kabupaten Tangerang', 'P', 165, 66),
(42, 'Tarumi', 'tarumi@gmail.com', 'SMAS Bina Bhakti', 'P', 168, 65),
(43, 'Bayhaqy', 'bayhaqy@gmail.com', 'SMAS Bina Bhakti', 'L', 170, 66),
(44, 'Fadilah', 'fadilah@gmail.com', 'SMAS Bina Bhakti', 'P', 170, 65),
(45, 'Ana Authapun Nisa', 'ana@gmail.com', 'SMAS Mandiri Balaraja', 'P', 166, 69),
(46, 'Okta Dwi Cahyo', 'okta@gmail.com', 'SMAS Yadika 10', 'P', 169, 69),
(47, 'Fenny Rose Perangin-angin', 'fenny@gmail.com', 'SMAS Yadika 10', 'P', 168, 69),
(48, 'Muhammad Iskandar Surya', 'iskandar@gmail.com', 'SMAS Yadika 10', 'L', 173, 73),
(49, 'Adinda Meisya Salsabila', 'meisya@gmail.com', 'SMAS Yadika 10', 'P', 166, 60),
(50, 'Sandro Rido Aripin', 'sandro@gmail.com', 'SMAS Yadika 10', 'L', 167, 65),
(51, 'Malahayati Tantria', 'malahayati@gmail.com', 'SMAS Yadika 10', 'P', 160, 55),
(52, 'Rina Anggraeni', 'rina@gmail.com', 'SMAS Yadika 10', 'P', 169, 62),
(53, 'Bima Brama Pura', 'bima@gmail.com', 'SMAS Yadika 10', 'L', 165, 69),
(54, 'Shofy Salsabila', 'shofy@gmail.com', 'SMK Al - Hikmah Curug', 'P', 170, 70),
(55, 'Ikrom Bagas Saputra', 'ikrom@gmail.com', 'SMK Al - Hikmah Curug', 'L', 168, 60),
(56, 'Muhammad Faiz Sungkar', 'faiz@gmail.com', 'SMK Angkasa 1', 'L', 169, 65),
(57, 'Hafiz Pradita Haryanto', 'hafiz@gmail.com', 'SMK Angkasa 2', 'L', 175, 65),
(58, 'Rahmat', 'rahmat@gmail.com', 'SMK Buna Insani Cijeruk', 'L', 176, 65),
(59, 'Alya Syfah Damayanti', 'alya@gmail.com', 'SMK Bina Mandiri Teluk Naga', 'P', 162, 60),
(60, 'Fakhri Akbar Ludiyanto', 'fakhri@gmail.com', 'SMK Kusuma Bangsa', 'L', 165, 70),
(61, 'Rehana Risqi Novian', 'rehana@gmail.com', 'SMKN 4 Kabupaten Tangerang', 'P', 165, 68),
(62, 'Nurani Rahmawati', 'nurani@gmail.com', 'SMKN 6 Kabupaten Tangerang', 'P', 169, 65),
(63, 'Muhammad Ilham Ramadhani', 'milham@gmail.com', 'SMKN 1 Kabupaten Tangerang', 'L', 170, 75),
(64, 'Fahry Ferdiyansyah', 'fahry@gmail.com', 'SMKN 1 Kabupaten Tangerang', 'L', 168, 70),
(65, 'Azzura Aprilianti', 'azzura@gmail.com', 'SMKN 8 Kabupaten Tangerang', 'P', 169, 65),
(66, 'Indah Nurcahya Ningsih', 'indah@gmail.com', 'SMKN 8 Kabupaten Tangerang', 'P', 168, 66),
(67, 'Annisa Lararati', 'anissa@gmail.com', 'SMKN 8 Kabupaten Tangerang', 'P', 169, 65),
(68, 'Bryan Reguel Yeremia Sitanggang', 'bryan@gmail.com', 'SMKN 8 Kabupaten Tangerang', 'L', 175, 80),
(69, 'Sabita Chairunisa', 'sabita@gmail.com', 'SMKS Bintang Nusantara School', 'P', 169, 63),
(70, 'Zahratus Syta', 'zahratus@gmail.com', 'SMKS Bintang Nusantara School', 'P', 166, 70),
(71, 'Amanda Apriliani', 'amanda@gmail.com', 'SMKS Wipama Cikupa', 'P', 166, 63),
(72, 'Aurel Amelia Septiana', 'aurel@gmail.com', 'SMKS Wipama Cikupa', 'P', 168, 64),
(73, 'Siti Nur Aidah', 'nur@gmail.com', 'SMKS Wipama Cikupa', 'P', 164, 61),
(74, 'Adinda Nurul Najmi', 'nurul@gmail.com', 'SMKS Wipama Cikupa', 'P', 162, 62),
(75, 'Muhammad Adiansyah', 'adiansyah@gmail.com', 'SMKS Wipama Cikupa', 'L', 168, 69),
(76, 'Muhammad Faiz Fauzi', 'fauzi@gmail.com', 'SMKS Wipama Cikupa', 'L', 169, 64),
(77, 'Robi Ardiansah', 'robi@gmail.com', 'SMKS Wipama Cikupa', 'L', 170, 70);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(6, 'Admin', 'admin@gmail.com', '$2y$10$I5IXhZ2MY.hLSuH/7Xjgl.QuPC0VTFsXKzWxzdgXQT5KH98Wmhaa6', 1, '2026-05-02 14:22:48', '2026-06-18 13:15:12'),
(9, 'Revan Jovanis', 'revan@gmail.com', '$2a$12$3vAtPMfP9MV4.eddIEhk/u.Ahlnlr.BllhQR1VYcoaeoqAhiC6DWG', 2, '2026-06-18 08:26:10', NULL),
(10, 'Ihsan Saputra', 'ihsan@gmail.com', '$2y$10$xtCq5XwuP8ZprrNluT7er.MIvxgrvoxnT1e.VOn8opn5PQGN8NeY.', 2, '2026-06-18 08:26:42', NULL),
(11, 'Adinda Putri', 'adinda@gmail.com', '$2y$10$FOlpQuCiT2pQjhkbLlal2.EBVA7B55xEURpvAH8vgFD3P9tvEy.Sy', 2, '2026-06-18 08:27:21', NULL),
(12, 'Siti Muslimah', 'siti@gmail.com', '$2y$10$xCqiAxIR/CIfSAe88rUge.iinkFwwWjhKFs7/qRnI5kU32V9Rq5k.', 2, '2026-06-18 08:27:51', NULL),
(13, 'Odilia Cinta Cristiani Simbolon', 'Odilia@gmail.com', '$2y$10$0RdzbHuvERXo99BxEOCY2.YaNQw6TqF8YRT.k3ZAjNxKLO0aLbf/.', 2, '2026-06-18 08:28:19', NULL),
(14, 'Ganesha Priscilla Boru Barutu', 'ganesha@gmail.com', '$2y$10$oMRjRMSdV14cheGbTqyORuP3dGjRWNOIp22loWljup6GLIZfVkttW', 2, '2026-06-18 08:28:53', NULL),
(15, 'Azriel Gheisan', 'azriel@gmail.com', '$2y$10$Pd1FdjgE/AnM2O82LyMfVOBfmQ6fO7.nD/9jPeeleSU5.YnmtG8de', 2, '2026-06-18 08:34:18', NULL),
(16, 'Sasya Tiara Ramadhani', 'sasya@gmail.com', '$2y$10$QQ.tzEZDVjBlR.7Yyfn4R.VTSL.lHJ4N1iEZTf6HgWjUHgdM05.1i', 2, '2026-06-18 08:35:43', NULL),
(17, 'Putri Aurell Silaban', 'putri@gmail.com', '$2y$10$Yj4MlPUZV3TEF9YIwMcZKOy.Q0T6tWHEoJpDu/OREBWDRBTTvDQ2m', 2, '2026-06-18 08:36:28', NULL),
(18, 'Dina Nur Priatna', 'dina@gmail.com', '$2y$10$aLaYT9jGS9EEFD74EZlKxe9bDfHYNrJWdLar.znWQO.pQKZIpJkae', 2, '2026-06-18 08:36:58', NULL),
(19, 'Gaitsa Nur Hanifah', 'gaitsa@gmail.com', '$2y$10$SK4IXnLSYaDLmyb7FkLp8e2z7feD9PLnO/ttFF0BjAYmHyHN0ecRC', 2, '2026-06-18 08:37:41', NULL),
(20, 'Dico Alriyus Naftael Sihite', 'dico@gmail.com', '$2y$10$vEIUo5CkaZNN5zZCfy49eOwpSMsMKzm2qYAzL1Ok0CSTXV3yWfCPq', 2, '2026-06-18 08:38:10', NULL),
(21, 'Lintang Aryo Wicaksono', 'lintang@gmail.com', '$2y$10$Dc2CdhCEH16QAOeoyrYCeOpgCa1Lw6Hl4QgqfnqGv7rYiHxuK0ZHq', 2, '2026-06-18 08:38:41', NULL),
(22, 'Safira Amalia', 'safira@gmail.com', '$2y$10$eWLKmwowdsW6y.meUYJm5ehMwMsnUmLVq2v2aKkzXn0WmGf.YmzGy', 2, '2026-06-18 08:39:27', NULL),
(23, 'Dinda Mutiara', 'dinda@gmail.com', '$2y$10$sBsEXULQadn0cspKkmJluOJzXZxuy2.zRGx0bBLekjYTxd9Ef2IRO', 2, '2026-06-18 08:40:00', NULL),
(24, 'Khofifah Dwi Hapsari', 'khofifah@gmail.com', '$2y$10$.dWwKrLf6hiOMcIR4/lX2eMN2GNT95K2IKY8QImiw4fSC3pDaWcLO', 2, '2026-06-18 08:40:35', NULL),
(25, 'Muhammad Fazar Alamsyah', 'fazar@gmail.com', '$2y$10$rjGrQXSvB1H.WSny9qX3SOhEK3OyeAIebloSxaB./w56TSrF5yfIO', 2, '2026-06-18 08:41:12', NULL),
(26, 'Mika Aulia Hutasoit', 'mika@gmail.com', '$2y$10$0Hex5M2gUoC3egRjJ.EjAeBMSh.d9Lx5.cw3ArJPCzhzuLGfJWjP.', 2, '2026-06-18 08:41:40', NULL),
(27, 'Satria Dika Pratama', 'satria@gmail.com', '$2y$10$thUqmHIGqxhTY3Q51OPlXe.4PLtyGpk489VNurxb8AL0KuoqDGRXS', 2, '2026-06-18 08:42:13', NULL),
(28, 'Ramadhani Annida Suwandi', 'ramadhani@gmail.com', '$2y$10$pA/CVZqootDLIIs7BHaYQ.4iMiwcjKSoVd/LvKfebzc7yec58e2Lq', 2, '2026-06-18 08:42:40', NULL),
(29, 'Rio Aprilio Zidan', 'rio@gmail.com', '$2y$10$98q0DQ6VMSd4vcGh2Wj.RejUY5KmCtXUWTisoE/dzTvDhdImTrNxa', 2, '2026-06-18 08:43:07', NULL),
(30, 'Keysha Adeeva Taufiq', 'Keysha@gmail.com', '$2y$10$TF4mu8ybBriIoeJO.b22eujiWhZYf3w5ROkWZj9s2QR0LAlogi8PC', 2, '2026-06-18 08:43:37', NULL),
(31, 'Muhammad Ilham Bustomi', 'ilham@gmail.com', '$2y$10$s2tsJCDVrDf4UnR/V1t02ObVo15Debb.4KZLuz9xmHgLKLFNAwmGa', 2, '2026-06-18 08:44:27', NULL),
(32, 'Rifky', 'rifky@gmail.com', '$2y$10$ptXrgvtpYP60OThVfC4rrud7bi.w5TkS8EsvRfdzdjhr8r6vF5EGi', 2, '2026-06-18 08:44:49', NULL),
(33, 'Selvi Aulia Cahyani', 'selvi@gmail.com', '$2y$10$KUsDDFbmDXfxESzCaQ0O3.mxwL4jw61hcGF8GSQIIldhsHQ3siopK', 2, '2026-06-18 08:45:21', NULL),
(34, 'Tri Deswita Shaeshar', 'tri@gmail.com', '$2y$10$W1zMfuChn2nenHNsUscc6eTLxwvG27SMh.t/Cu89cBaQ0FVMnyRCG', 2, '2026-06-18 08:45:45', NULL),
(35, 'Tarumi', 'tarumi@gmail.com', '$2y$10$ZPmmt7PRT3ho4tl62Je94uPqukfaoJFHGScOKS1XERxbMwBOZkQRu', 2, '2026-06-18 08:46:10', NULL),
(36, 'Bayhaqy', 'bayhaqy@gmail.com', '$2y$10$zeTVU9xABU3C679oLW8ObeHiHehzu/DsElzOTJN1qBsCoI5bsKCju', 2, '2026-06-18 08:46:41', NULL),
(37, 'Fadilah', 'fadilah@gmail.com', '$2y$10$NEgmYe9Hfk1gNDpGoSOEsujJES32HEf8JiVTA7UNW0AlctAo9ZT..', 2, '2026-06-18 08:47:19', NULL),
(38, 'Ana Authapun Nisa', 'ana@gmail.com', '$2y$10$7k48IeWXOodGNMluMD548u09ulFSUuG/MMyJbaU30.trmIVq0n9i2', 2, '2026-06-18 08:48:05', NULL),
(39, 'Okta Dwi Cahyo', 'okta@gmail.com', '$2y$10$aTuEkQPuMyjd/utjs6be4.0KqAMulo1JIDQKIkLFuTsRVtn7q22Q2', 2, '2026-06-18 08:48:35', NULL),
(40, 'Fenny Rose Perangin-angin', 'fenny@gmail.com', '$2y$10$akMtPbo/PKK98gc1RcyYcOgPj2teZzW46EuTH7ZtqlcAeadtffv8e', 2, '2026-06-18 08:49:02', NULL),
(41, 'Muhammad Iskandar Surya', 'iskandar@gmail.com', '$2y$10$qJAl8Se91dwAJIKIcu8GruKzpf04cV1thd0wU./wPi6b2g7VyYOSW', 2, '2026-06-18 08:49:40', NULL),
(42, 'Adinda Meisya Salsabila', 'meisya@gmail.com', '$2y$10$DhT60DM5t7PKFKGRrNXLROt639qalDdhwpwcuqY7PTjin1eoD/7Bq', 2, '2026-06-18 08:50:19', NULL),
(43, 'Sandro Rido Aripin', 'sandro@gmail.com', '$2y$10$Wv./E6YjMexTyUCxZLjj/eGJOd9LPuYxLquAPmD0ThTX5iFQzt.Qu', 2, '2026-06-18 08:50:58', NULL),
(44, 'Malahayati Tantria', 'malahayati@gmail.com', '$2y$10$5R1VjfxBneWOn0LaisWxhOwc.uaC98wcC8pMsNLqeMNgb0H3TfmdK', 2, '2026-06-18 08:51:29', NULL),
(45, 'Rina Anggraeni', 'rina@gmail.com', '$2y$10$.7cclJ2Te7EDrGsIj817j.uV8AK1cb4Bld1H.sGCXVoX4Pe6iOVSG', 2, '2026-06-18 08:51:57', NULL),
(46, 'Bima Brama Pura', 'bima@gmail.com', '$2y$10$Wqn4cw524eA7GP7HkqWYxOev0SH8/KGADBVcfe.WbIratYFCAC0Qe', 2, '2026-06-18 08:52:26', NULL),
(47, 'Shofy Salsabila', 'shofy@gmail.com', '$2y$10$soAORQ2oyS1yMDBZ1JvmGuLXqnMVpsOZz6VeGoJBcD5c.lPZ8sDvi', 2, '2026-06-18 08:52:59', NULL),
(48, 'Ikrom Bagas Saputra', 'ikrom@gmail.com', '$2y$10$VlW49whMszbhvbbuWjAHHui1HnsHGZ5mUvVrG7NZ/IVn6Ll2n5pcS', 2, '2026-06-18 08:53:25', NULL),
(49, 'Muhammad Faiz Sungkar', 'faiz@gmail.com', '$2y$10$qyd.iDTIemiVflWHVQlcwuyeIJjkF/96Z31YjuTo8tSbeD/bbgI.q', 2, '2026-06-18 08:53:54', NULL),
(50, 'Hafiz Pradita Haryanto', 'hafiz@gmail.com', '$2y$10$81vhgSVU/FYNarruYHd/Su4Eij9Y//pfL4.nXOjunmNXrbzv9kL/i', 2, '2026-06-18 08:54:55', NULL),
(51, 'Rahmat', 'rahmat@gmail.com', '$2y$10$ZydiAljnekfYCzcq6UoEAO8ZO5MGiYvGl7BxVpMQqGQHW7Qr527.a', 2, '2026-06-18 08:55:23', NULL),
(52, 'Alya Syfah Damayanti', 'alya@gmail.com', '$2y$10$IBL1FcZ04STuHcGafCxkMO4LOJcb9XBOoDRk3FfHuM29RbJ7Y4y4e', 2, '2026-06-18 08:55:57', NULL),
(53, 'Fakhri Akbar Ludiyanto', 'fakhri@gmail.com', '$2y$10$kBm37KYgLC7Yl0op8rU6CeOFzHZQ15plgrxNDgvoXcfZVk3nxoydS', 2, '2026-06-18 08:56:52', NULL),
(54, 'Rehana Risqi Novian', 'rehana@gmail.com', '$2y$10$sejLJVSQODNU65GgffbJjO4IHHN2/e0gsXjosiLKi9noiDqzlE8e6', 2, '2026-06-18 08:57:20', NULL),
(55, 'Nurani Rahmawati', 'nurani@gmail.com', '$2y$10$MJoCw9RU9xfIMdxgpwZCVOm.3AA5b9B7LlbD7QKetE6RkHsQis/YS', 2, '2026-06-18 08:57:48', NULL),
(56, 'Muhammad Ilham Ramadhani', 'milham@gmail.com', '$2y$10$ouJK/9ZMH9M.oFhNMRwb2O3YqmSR6DgqxdxKq7FCbpKz4tjry6hci', 2, '2026-06-18 08:59:17', NULL),
(57, 'Fahry Ferdiyansyah', 'fahry@gmail.com', '$2y$10$YUVlUTLdQPMLVz25XCxOQ.hqs.W9h9ZMxJHcegDu2bvjn4FrebjLC', 2, '2026-06-18 08:59:52', NULL),
(58, 'Azzura Aprilianti', 'azzura@gmail.com', '$2y$10$Or4TGWwrSc4Sw.uU/22PEeGWtTYKme8g4/F.q.he0o5Mm7LKG9r5q', 2, '2026-06-18 09:00:19', NULL),
(59, 'Indah Nurcahya Ningsih', 'indah@gmail.com', '$2y$10$aj7GU.uCnMW2Rxf40rZ9qOOLW5yIAai.6uzj43pqexDh6oNS4KmF6', 2, '2026-06-18 09:00:44', NULL),
(60, 'Annisa Lararati', 'anissa@gmail.com', '$2y$10$nqYqDqntMQfBWtv7rX3dAuo9/m9GGJnTWDolIdOD64wNd02gD0VFC', 2, '2026-06-18 09:01:17', NULL),
(61, 'Bryan Reguel Yeremia Sitanggang', 'bryan@gmail.com', '$2y$10$gL7FIV5byCwCjBFIYKkCkOUL8neFc9Iy9cJ/G5UhZo/dESFuTNzxe', 2, '2026-06-18 09:01:59', NULL),
(62, 'Sabita Chairunisa', 'sabita@gmail.com', '$2y$10$ZA0DAG5Fw2nZdZovlIw0tuNwKgwcP/WEXuSB/b.h1Od50KoAUaivq', 2, '2026-06-18 09:02:28', NULL),
(63, 'Zahratus Syta', 'zahratus@gmail.com', '$2y$10$2rl45TbxZZpMANw9Oz5x/.enm.t4EqN.5Kmryi0Vsqmp5FLYiUFE.', 2, '2026-06-18 09:02:57', NULL),
(64, 'Amanda Apriliani', 'amanda@gmail.com', '$2y$10$hgHu.eBl.HyJt3ru7/z6muky2iB2a5VYCtuGpmbA9AR1rr7OCOmzC', 2, '2026-06-18 09:03:23', NULL),
(65, 'Aurel Amelia Septiana', 'aurel@gmail.com', '$2y$10$XQ.Bp5rSNhqwdhlRhrR7hehHzlAlok.Yz2ZvJNgpn3UotRB6eA7KS', 2, '2026-06-18 09:06:17', NULL),
(66, 'Siti Nur Aidah', 'nur@gmail.com', '$2y$10$W0wMeMxwXxSldvycPXyqxeNndlvvkUWOnDAGKy0yptXls/wwNiZ92', 2, '2026-06-18 09:06:42', NULL),
(67, 'Adinda Nurul Najmi', 'nurul@gmail.com', '$2y$10$22NldEO0Tvn0oa3JYZN86Oc3mmGUUzcFerzWiFl0H8Nz0trjrJkUO', 2, '2026-06-18 09:08:18', NULL),
(68, 'Muhammad Adiansyah', 'adiansyah@gmail.com', '$2y$10$55pm1xZydSid.Kb/t.cHzuT9ylzmgUJ5hudXQBWByehylbpVuo/1e', 2, '2026-06-18 09:08:50', NULL),
(69, 'Muhammad Faiz Fauzi', 'fauzi@gmail.com', '$2y$10$3PkTqkW3OfQXjq/Hh0ZtQeKE6XVd7eDQH6bop6HOWJtDITPqxuJgC', 2, '2026-06-18 09:09:59', NULL),
(70, 'Robi Ardiansah', 'robi@gmail.com', '$2y$10$h479HElOj91ycdPSFN.0a.aVbXtUYvPabagY77nwBOGxuBGICHJUu', 2, '2026-06-18 09:10:25', NULL),
(71, 'Alfino Nicolas Prasetya', 'nicolasprasetya18@gmail.com', '$2y$10$zFdSezUgEfVCwcVjFQKlLueIJyHL7SxUYxPJbh0J7YSL27OOL.SQG', 3, '2026-08-15 06:14:32', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aturan_fuzzy`
--
ALTER TABLE `aturan_fuzzy`
  ADD PRIMARY KEY (`id_aturan`);

--
-- Indexes for table `detail_aturan_fuzzy`
--
ALTER TABLE `detail_aturan_fuzzy`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_aturan` (`id_aturan`),
  ADD KEY `id_himpunan` (`id_himpunan`);

--
-- Indexes for table `hasil_perhitungan`
--
ALTER TABLE `hasil_perhitungan`
  ADD PRIMARY KEY (`id_hasil`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indexes for table `himpunan_fuzzy`
--
ALTER TABLE `himpunan_fuzzy`
  ADD PRIMARY KEY (`id_himpunan`),
  ADD KEY `id_kriteria` (`id_kriteria`);

--
-- Indexes for table `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id_kriteria`);

--
-- Indexes for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id_penilaian`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_kriteria` (`id_kriteria`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aturan_fuzzy`
--
ALTER TABLE `aturan_fuzzy`
  MODIFY `id_aturan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `detail_aturan_fuzzy`
--
ALTER TABLE `detail_aturan_fuzzy`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `hasil_perhitungan`
--
ALTER TABLE `hasil_perhitungan`
  MODIFY `id_hasil` bigint(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3464;

--
-- AUTO_INCREMENT for table `himpunan_fuzzy`
--
ALTER TABLE `himpunan_fuzzy`
  MODIFY `id_himpunan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id_kriteria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id_penilaian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=503;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_aturan_fuzzy`
--
ALTER TABLE `detail_aturan_fuzzy`
  ADD CONSTRAINT `detail_aturan_fuzzy_ibfk_1` FOREIGN KEY (`id_aturan`) REFERENCES `aturan_fuzzy` (`id_aturan`),
  ADD CONSTRAINT `detail_aturan_fuzzy_ibfk_2` FOREIGN KEY (`id_himpunan`) REFERENCES `himpunan_fuzzy` (`id_himpunan`);

--
-- Constraints for table `hasil_perhitungan`
--
ALTER TABLE `hasil_perhitungan`
  ADD CONSTRAINT `hasil_perhitungan_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Constraints for table `himpunan_fuzzy`
--
ALTER TABLE `himpunan_fuzzy`
  ADD CONSTRAINT `himpunan_fuzzy_ibfk_1` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`);

--
-- Constraints for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`),
  ADD CONSTRAINT `penilaian_ibfk_2` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role`) REFERENCES `role` (`id_role`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
