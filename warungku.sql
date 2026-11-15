-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Nov 2026 pada 13.17
-- Versi server: 10.11.16-MariaDB
-- Versi PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `warungku`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_kas`
--

CREATE TABLE `kategori_kas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(80) NOT NULL,
  `jenis` enum('income','expense') NOT NULL,
  `dibuat_pada` timestamp NULL DEFAULT NULL,
  `diperbarui_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_kas`
--

INSERT INTO `kategori_kas` (`id`, `id_pengguna`, `nama`, `jenis`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, NULL, 'Penjualan', 'income', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(2, NULL, 'Pendapatan Lain-lain', 'income', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(3, NULL, 'Kulakan', 'expense', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(4, NULL, 'Listrik', 'expense', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(5, NULL, 'Gas', 'expense', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(6, NULL, 'Gaji', 'expense', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(7, NULL, 'Operasional', 'expense', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(8, NULL, 'Keperluan Pribadi', 'expense', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(9, NULL, 'Pengeluaran Lain-lain', 'expense', '2026-10-17 07:07:46', '2026-10-17 07:07:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `catatan_kas`
--

CREATE TABLE `catatan_kas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED NOT NULL,
  `id_kategori_kas` bigint(20) UNSIGNED NOT NULL,
  `id_mutasi_stok` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pelanggan` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis` enum('income','expense') NOT NULL,
  `nominal` decimal(14,0) NOT NULL,
  `tanggal_transaksi` datetime NOT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `dihapus_pada` timestamp NULL DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT NULL,
  `diperbarui_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `catatan_kas`
--

INSERT INTO `catatan_kas` (`id`, `id_pengguna`, `id_kategori_kas`, `id_mutasi_stok`, `id_pelanggan`, `jenis`, `nominal`, `tanggal_transaksi`, `catatan`, `dihapus_pada`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 1, 1, 1, NULL, 'income', 27000, '2026-10-17 11:20:00', 'Penjualan Telur Ayam 10 butir', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(2, 1, 4, NULL, NULL, 'expense', 50000, '2026-10-17 10:05:00', 'Token listrik', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(3, 1, 1, NULL, NULL, 'income', 25000, '2026-10-17 09:40:00', 'Jual pulsa', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(4, 1, 1, 2, NULL, 'income', 12000, '2026-10-17 08:15:00', 'Penjualan Aqua 600ml 3 botol', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(5, 1, 1, 3, NULL, 'income', 17500, '2026-10-17 07:30:00', 'Penjualan Indomie Goreng 5 bungkus', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(6, 1, 1, 4, NULL, 'income', 28000, '2026-10-16 17:00:00', 'Penjualan Indomie Goreng 8 bungkus', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(7, 1, 1, 5, NULL, 'income', 36000, '2026-10-16 15:30:00', 'Penjualan Teh Botol Sosro 6 botol', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(8, 1, 3, 6, NULL, 'expense', 192000, '2026-10-16 10:00:00', 'Kulakan Minyak Goreng 1L 12 botol', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(9, 1, 1, 7, NULL, 'income', 60000, '2026-10-16 08:00:00', 'Penjualan Beras Premium 4 kg', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(10, 1, 1, 8, NULL, 'income', 68000, '2026-10-15 11:14:00', 'Penjualan Gula Pasir 4 kg', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(11, 1, 3, NULL, NULL, 'expense', 150000, '2026-10-15 14:00:00', 'Kulakan sembako', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(12, 1, 1, 9, NULL, 'income', 20000, '2026-10-14 12:21:00', 'Penjualan Aqua 600ml 5 botol', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(13, 1, 1, 10, NULL, 'income', 12000, '2026-10-14 14:10:00', 'Penjualan Teh Botol Sosro 2 botol', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(14, 1, 1, 11, NULL, 'income', 10500, '2026-10-14 16:45:00', 'Penjualan Indomie Goreng 3 bungkus', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(15, 1, 1, 12, NULL, 'income', 16200, '2026-10-14 18:00:00', 'Penjualan Telur Ayam 6 butir', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(17, 1, 1, 14, 4, 'income', 4000, '2026-10-26 03:01:36', 'Penjualan Kopi Kapal Api', NULL, '2026-10-26 02:01:36', '2026-10-26 02:01:36'),
(18, 1, 1, 15, 6, 'income', 7000, '2026-10-27 04:02:13', 'Pelunasan Kasbon Ibu Siti (TRX-261027-0015) - Indomie Goreng', NULL, '2026-10-27 03:02:13', '2026-10-27 03:02:13'),
(19, 1, 1, 16, NULL, 'income', 24000, '2026-10-27 08:02:24', 'Penjualan Chitato Snack 65 gr', NULL, '2026-10-27 07:02:24', '2026-10-27 07:02:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(80) NOT NULL,
  `dibuat_pada` timestamp NULL DEFAULT NULL,
  `diperbarui_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `id_pengguna`, `nama`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 1, 'Sembako', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(2, 1, 'Makanan', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(3, 1, 'Minuman', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(4, 1, 'Rokok', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(5, 1, 'Kebersihan', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(6, 1, 'Bumbu Dapur', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(7, 1, 'Lain-lain', '2026-10-17 07:07:46', '2026-10-17 07:07:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED NOT NULL,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `telepon` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dihapus_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `id_pengguna`, `kode`, `nama`, `telepon`, `alamat`, `catatan`, `dibuat_pada`, `diperbarui_pada`, `dihapus_pada`) VALUES
(4, 1, 'CUST-004', 'Pak RT Bambang', '081234567899', NULL, NULL, '2026-10-26 02:01:19', '2026-10-26 02:01:19', NULL),
(6, 1, 'CUST-005', 'Ibu Siti', NULL, NULL, NULL, '2026-10-27 02:57:39', '2026-10-27 02:57:39', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_17_000001_create_categories_table', 1),
(5, '2026_10_17_000002_create_products_table', 1),
(6, '2026_10_17_000003_create_stock_movements_table', 1),
(7, '2026_10_17_000004_create_cash_categories_table', 1),
(8, '2026_10_17_000005_create_cash_entries_table', 1),
(9, '2026_10_17_000006_add_role_and_staff_fields_to_users_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `token_reset_kata_sandi`
--

CREATE TABLE `token_reset_kata_sandi` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `dibuat_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `pembeli`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `pembeli` (
`id` bigint(20) unsigned
,`user_id` bigint(20) unsigned
,`code` varchar(50)
,`name` varchar(150)
,`phone` varchar(30)
,`address` text
,`note` text
,`created_at` timestamp
,`updated_at` timestamp
,`deleted_at` timestamp
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED NOT NULL,
  `id_kategori` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `satuan` varchar(20) NOT NULL DEFAULT 'pcs',
  `gambar` varchar(255) DEFAULT NULL,
  `harga_beli` decimal(12,0) NOT NULL DEFAULT 0,
  `harga_jual` decimal(12,0) NOT NULL DEFAULT 0,
  `stok` int(11) NOT NULL DEFAULT 0,
  `stok_minimum` int(11) NOT NULL DEFAULT 5,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `dihapus_pada` timestamp NULL DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT NULL,
  `diperbarui_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `id_pengguna`, `id_kategori`, `nama`, `satuan`, `gambar`, `harga_beli`, `harga_jual`, `stok`, `stok_minimum`, `status_aktif`, `dihapus_pada`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 1, 2, 'Indomie Goreng', 'bungkus', NULL, 2500, 3500, 38, 10, 1, NULL, '2026-10-17 07:07:46', '2026-10-27 02:57:39'),
(2, 1, 1, 'Beras Premium', 'kg', NULL, 12500, 15000, 25, 5, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(3, 1, 1, 'Minyak Goreng 1L', 'botol', NULL, 15000, 18500, 4, 5, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(4, 1, 1, 'Gula Pasir', 'kg', NULL, 14000, 17000, 3, 5, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(5, 1, 3, 'Aqua 600ml', 'botol', NULL, 2800, 4000, 30, 10, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(6, 1, 3, 'Kopi Kapal Api', 'sachet', NULL, 1400, 2000, 6, 10, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(7, 1, 1, 'Telur Ayam', 'butir', NULL, 2200, 2700, 60, 20, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(8, 1, 5, 'Sabun Lifebuoy', 'buah', NULL, 3500, 4500, 2, 5, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(9, 1, 3, 'Teh Botol Sosro', 'botol', NULL, 4500, 6000, 18, 10, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(11, 1, 2, 'Chitato Snack 65 gr', 'pcs', 'prod_20261027_080132_50666e5c.jpg', 10000, 12000, 18, 5, 1, NULL, '2026-10-27 07:01:32', '2026-10-27 07:02:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sesi`
--

CREATE TABLE `sesi` (
  `id` varchar(255) NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED DEFAULT NULL,
  `alamat_ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `aktivitas_terakhir` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sesi`
--

INSERT INTO `sesi` (`id`, `id_pengguna`, `alamat_ip`, `user_agent`, `payload`, `aktivitas_terakhir`) VALUES
('3tHaNrru017IQD58cfZ94z6j5SAvWHxKDoCNfcGw', 1, '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoieHAyMTc2SENLclhWSk5nbkd3SzZneWRPc0pMd0FRV1Nyb3NDRU1iUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9kdWN0cyI7czo1OiJyb3V0ZSI7czoxNDoicHJvZHVjdHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiJlYWJjMTg5NDk1YmQ4NDQxOTUyYjc1ODNlZDc2Njg3YWI4OWQ0NWQwZDMxY2VhMzE3MjFlMjU5OTliNWZjZTkyIjt9', 1792978936),
('508MgXidiLl9tiT7g1Mxn9CxgiJQarO8bHGjcLa8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.142.0 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoialo5cTVFUU5pQlFldkJDcm80TEJyN2dNc3dCYVdxTjljUUNWQ2RaMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1792390017),
('9BgHhO4TiTmNB1cW2HYOsfrh1xEd83K1UA3gNv2G', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnI4bkVhckQ4WHFDMnhKeWVoVVNKcEVYQUlmYWZzd0tvRjE5UE5KTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1792978875),
('fFG7QFzYaleaAAXXxMTD7VfIxS6rJKEiEVbzOLrx', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWHRVQlBpZHBNTkNTaFVlNGx2R2FFWENLbVBaZ21hcDZRRVM4bnRSNCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0NDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZD9wZXJpb2Q9dG9kYXkiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1792736162),
('spirhdUUEdlLQ6xxHi6kOXtl3VtLOeDrR4QvsNl4', 1, '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUWNiN1l3R083R21VVVZBeHloTVFweTB1VkxVdzl6WW9IN1ZRbWVNeiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiZWFiYzE4OTQ5NWJkODQ0MTk1MmI3NTgzZWQ3NjY4N2FiODlkNDVkMGQzMWNlYTMxNzIxZTI1OTk5YjVmY2U5MiI7fQ==', 1792390084),
('y6vDTGF26tA5pwENKm5uokSnGCxpkaR8j7nM6jFe', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiV0lQVlZrVExRbWFBWEI0QTJBdm94ZW1sNThLZUM3SE00UDgwNXNMYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9oaXN0b3J5IjtzOjU6InJvdXRlIjtzOjEzOiJoaXN0b3J5LmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiN2MxMmY0ZDlkZGViMzUxNWNlN2RhZWE5ZWI0ZDQ5YTRjZGQ5YTFhMTU2Y2JhNmIxMGFlNWRjNWM3OGYwNTFjMiI7fQ==', 1792247069);

-- --------------------------------------------------------

--
-- Struktur dari tabel `mutasi_stok`
--

CREATE TABLE `mutasi_stok` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED NOT NULL,
  `no_faktur` varchar(50) DEFAULT NULL,
  `id_produk` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis` enum('in','out','adjust') NOT NULL,
  `alasan` enum('purchase','sale','personal','damaged','adjustment') NOT NULL,
  `status_pembayaran` varchar(20) NOT NULL DEFAULT 'paid',
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,0) NOT NULL DEFAULT 0,
  `harga_modal` decimal(12,0) NOT NULL DEFAULT 0,
  `tanggal_mutasi` datetime NOT NULL,
  `dibayar_pada` datetime DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `dihapus_pada` timestamp NULL DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT NULL,
  `diperbarui_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `mutasi_stok`
--

INSERT INTO `mutasi_stok` (`id`, `id_pengguna`, `no_faktur`, `id_produk`, `id_pelanggan`, `jenis`, `alasan`, `status_pembayaran`, `jumlah`, `harga_satuan`, `harga_modal`, `tanggal_mutasi`, `dibayar_pada`, `catatan`, `dihapus_pada`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 1, 'TRX-261017-0001', 7, NULL, 'out', 'sale', 'paid', 10, 2700, 2200, '2026-10-17 11:20:00', NULL, 'Penjualan toko', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(2, 1, 'TRX-261017-0002', 5, NULL, 'out', 'sale', 'paid', 3, 4000, 2800, '2026-10-17 08:15:00', NULL, 'Penjualan toko', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(3, 1, 'TRX-261017-0003', 1, NULL, 'out', 'sale', 'paid', 5, 3500, 2500, '2026-10-17 07:30:00', NULL, 'Penjualan toko', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(4, 1, 'TRX-261016-0004', 1, NULL, 'out', 'sale', 'paid', 8, 3500, 2500, '2026-10-16 17:00:00', NULL, 'Penjualan warung', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(5, 1, 'TRX-261016-0005', 9, NULL, 'out', 'sale', 'paid', 6, 6000, 4500, '2026-10-16 15:30:00', NULL, 'Penjualan warung', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(6, 1, 'TRX-261016-0006', 3, NULL, 'in', 'purchase', 'paid', 12, 16000, 16000, '2026-10-16 10:00:00', NULL, 'Kulakan agen', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(7, 1, 'TRX-261016-0007', 2, NULL, 'out', 'sale', 'paid', 4, 15000, 12500, '2026-10-16 08:00:00', NULL, 'Penjualan warung', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(8, 1, 'TRX-261015-0008', 4, NULL, 'out', 'sale', 'paid', 4, 17000, 14000, '2026-10-15 11:14:00', NULL, 'Penjualan warung', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(9, 1, 'TRX-261014-0009', 5, NULL, 'out', 'sale', 'paid', 5, 4000, 2800, '2026-10-14 12:21:00', NULL, 'Penjualan warung', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(10, 1, 'TRX-261014-0010', 9, NULL, 'out', 'sale', 'paid', 2, 6000, 4500, '2026-10-14 14:10:00', NULL, 'Penjualan warung', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(11, 1, 'TRX-261014-0011', 1, NULL, 'out', 'sale', 'paid', 3, 3500, 2500, '2026-10-14 16:45:00', NULL, 'Penjualan warung', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(12, 1, 'TRX-261014-0012', 7, NULL, 'out', 'sale', 'paid', 6, 2700, 2200, '2026-10-14 18:00:00', NULL, 'Penjualan warung', NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(14, 1, 'TRX-261026-0014', 6, 4, 'out', 'sale', 'paid', 2, 2000, 1400, '2026-10-26 03:01:36', NULL, 'Pembelian beras untuk syukuran', NULL, '2026-10-26 02:01:36', '2026-10-26 02:01:36'),
(15, 1, 'TRX-261027-0015', 1, 6, 'out', 'sale', 'paid', 2, 3500, 2500, '2026-10-27 03:57:39', '2026-10-27 04:02:13', 'Penjualan Kasbon Indomie Goreng', NULL, '2026-10-27 02:57:39', '2026-10-27 03:02:13'),
(16, 1, 'TRX-261027-0016', 11, NULL, 'out', 'sale', 'paid', 2, 12000, 10000, '2026-10-27 08:02:24', NULL, 'Penjualan Chitato Snack 65 gr', NULL, '2026-10-27 07:02:24', '2026-10-27 07:02:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_toko` varchar(120) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email_diverifikasi_pada` timestamp NULL DEFAULT NULL,
  `kata_sandi` varchar(255) NOT NULL,
  `peran` varchar(20) NOT NULL DEFAULT 'owner',
  `id_pemilik` bigint(20) UNSIGNED DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `token_ingat` varchar(100) DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT NULL,
  `diperbarui_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama_toko`, `nama`, `email`, `telepon`, `email_diverifikasi_pada`, `kata_sandi`, `peran`, `id_pemilik`, `status_aktif`, `token_ingat`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 'Warung Bu Sari', 'Bu Sari', 'busari@warungku.id', '081234567890', NULL, '$2y$12$JWxbeyOO8en8OSCMNb8Sn.rg7UwxuriferOO83ZXHQTsDVKRnE14S', 'owner', NULL, 1, 'VUudBdDBJLqbyLamifMtK0lgbp1EjUnV8BcTFU8HFyMkEA0H8nvoEETuw7kW', '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(2, 'Warung Bu Sari', 'Budi Penjaga', NULL, '081298765432', NULL, '$2y$12$GdqY3XoUQtS5/JRrJVepx.DW0oWuAE/i3PmjOuiaE4GCmnhGixvKO', 'staff', 1, 1, NULL, '2026-10-17 07:07:46', '2026-10-17 07:07:46'),
(3, 'Warung Bu Sari', 'Dzaky Naufal', NULL, '085703027774', NULL, '$2y$12$LLxrCxLmi40j9/cIgHz16OwU20UkOOMq/5AceO.3t8fK9P1.7vvb.', 'staff', 1, 1, 'SCWRI2fWBWOrEALMZHPYSEQyNB9nj5IZY6Obuqk95r5u2ysE6fmF0BnZmKek', '2026-10-17 07:12:15', '2026-10-17 07:12:15');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `kategori_kas`
--
ALTER TABLE `kategori_kas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_kas_id_pengguna_jenis_index` (`id_pengguna`,`jenis`);

--
-- Indeks untuk tabel `catatan_kas`
--
ALTER TABLE `catatan_kas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catatan_kas_id_kategori_kas_foreign` (`id_kategori_kas`),
  ADD KEY `catatan_kas_id_mutasi_stok_foreign` (`id_mutasi_stok`),
  ADD KEY `catatan_kas_id_pengguna_tanggal_transaksi_index` (`id_pengguna`,`tanggal_transaksi`),
  ADD KEY `catatan_kas_id_pengguna_jenis_index` (`id_pengguna`,`jenis`),
  ADD KEY `idx_ck_id_pelanggan` (`id_pelanggan`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id_pengguna_nama_index` (`id_pengguna`,`nama`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pelanggan_id_pengguna` (`id_pengguna`),
  ADD KEY `idx_pelanggan_kode` (`kode`),
  ADD KEY `idx_pelanggan_nama` (`nama`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `token_reset_kata_sandi`
--
ALTER TABLE `token_reset_kata_sandi`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id_kategori_foreign` (`id_kategori`),
  ADD KEY `produk_id_pengguna_nama_index` (`id_pengguna`,`nama`),
  ADD KEY `produk_id_pengguna_stok_index` (`id_pengguna`,`stok`);

--
-- Indeks untuk tabel `sesi`
--
ALTER TABLE `sesi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sesi_id_pengguna_index` (`id_pengguna`),
  ADD KEY `sesi_aktivitas_terakhir_index` (`aktivitas_terakhir`);

--
-- Indeks untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mutasi_stok_id_pengguna_tanggal_mutasi_index` (`id_pengguna`,`tanggal_mutasi`),
  ADD KEY `mutasi_stok_id_produk_tanggal_mutasi_index` (`id_produk`,`tanggal_mutasi`),
  ADD KEY `idx_ms_id_pelanggan` (`id_pelanggan`),
  ADD KEY `idx_ms_status_pembayaran` (`status_pembayaran`),
  ADD KEY `idx_ms_no_faktur` (`no_faktur`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengguna_email_unique` (`email`),
  ADD KEY `pengguna_id_pemilik_foreign` (`id_pemilik`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori_kas`
--
ALTER TABLE `kategori_kas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `catatan_kas`
--
ALTER TABLE `catatan_kas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- --------------------------------------------------------

--
-- Struktur untuk view `pembeli`
--
DROP TABLE IF EXISTS `pembeli`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `pembeli`  AS SELECT `pelanggan`.`id` AS `id`, `pelanggan`.`id_pengguna` AS `id_pengguna`, `pelanggan`.`kode` AS `kode`, `pelanggan`.`nama` AS `nama`, `pelanggan`.`telepon` AS `telepon`, `pelanggan`.`alamat` AS `alamat`, `pelanggan`.`catatan` AS `catatan`, `pelanggan`.`dibuat_pada` AS `dibuat_pada`, `pelanggan`.`diperbarui_pada` AS `diperbarui_pada`, `pelanggan`.`dihapus_pada` AS `dihapus_pada` FROM `pelanggan` ;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `kategori_kas`
--
ALTER TABLE `kategori_kas`
  ADD CONSTRAINT `kategori_kas_id_pengguna_foreign` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `catatan_kas`
--
ALTER TABLE `catatan_kas`
  ADD CONSTRAINT `catatan_kas_id_kategori_kas_foreign` FOREIGN KEY (`id_kategori_kas`) REFERENCES `kategori_kas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `catatan_kas_id_mutasi_stok_foreign` FOREIGN KEY (`id_mutasi_stok`) REFERENCES `mutasi_stok` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `catatan_kas_id_pengguna_foreign` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD CONSTRAINT `kategori_id_pengguna_foreign` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_id_kategori_foreign` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `produk_id_pengguna_foreign` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  ADD CONSTRAINT `mutasi_stok_id_produk_foreign` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mutasi_stok_id_pengguna_foreign` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD CONSTRAINT `pengguna_id_pemilik_foreign` FOREIGN KEY (`id_pemilik`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;