CREATE DATABASE IF NOT EXISTS uts_keamanan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uts_keamanan;
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 16, 2026 at 10:12 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uts_keamanan`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_keys`
--

DROP TABLE IF EXISTS `admin_keys`;
CREATE TABLE `admin_keys` (
  `id` int(10) UNSIGNED NOT NULL,
  `sym_hash` varchar(255) NOT NULL,
  `public_key` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_keys`
--

INSERT INTO `admin_keys` (`id`, `sym_hash`, `public_key`, `created_at`) VALUES
(1, '$2y$12$0ICvSiBxA67aGw5ImUslUemhftrKemPgbcz3fo7HWe2dP1jLf51Pi', '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0ARBMCqgucuRct1+ohbo\nitSbcg5/XsBjYwZD/y6q0xUDiTDJ+Y8AjAtE9lvt1VhAzJY8/UaQsLCPoYu1xNAL\nefM1mI9Oc/2JzF6NMJd+vb0xfUTZO3AUJtv58S7dlMtgrh6xeT9rjXmNxk3oBviw\nAv2pJgbA6pPHmNiHi9B51u1JtwB10V0e4jdQ6K/34WMMaNk8uqyBNtqu/vvdEyIF\nBawhaRoDe9JfWsxFN9Uv+/jLWHTd5TgdMJsVFpgSt/jaHTFoe+81CBVt7Q9sK/zV\ndedgAWsDIp6K7XXQxDHMtGBd1I1kpmvQpyFIialnJH+YtznjtyF52AjMBZ/7BKBb\nywIDAQAB\n-----END PUBLIC KEY-----\n', '2026-11-16 09:55:03');

-- --------------------------------------------------------

--
-- Table structure for table `records`
--

DROP TABLE IF EXISTS `records`;
CREATE TABLE `records` (
  `id` int(10) UNSIGNED NOT NULL,
  `judul_enc` text NOT NULL,
  `isi_enc` mediumtext NOT NULL,
  `signature` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `records`
--

INSERT INTO `records` (`id`, `judul_enc`, `isi_enc`, `signature`, `created_at`) VALUES
(1, 'Z+u/AXfa8deRYEmeqB6acaIPaq339eufj8i02iBKap4Ykc4ufA==', 'EJhero9vaZQZ5BzlZ1RRYOIJTSwJRyz2FkKrIGP+SwMh5Oam3oSTLHv52IBJcKEfaGydSREX5t2A7JAs0hm7rg5B6EDw42ul/UNwBw0JFnTptgk=', 'Np5QyFxr2euRp9dR96MnUaJZF/nvPOTmMj1njCoQki4mKlw7LX/6NYYK/LWLLGRwbGj9XfXSQ/OKLGaiSuVRhvCeIHFEufrJkcHRERH/zChHWYRDMFbnnCdom0159WdN39tGq+yuVvfB+5S+eNWv+9yfc7Rv13cfjbGGJRmc5FlLIzWAZ8EBfSDQYG5iEK/p7xVnBymoGSKpuSzfNiylClpEe59UvpAY4Yv4f89j3B3LTomDdub5aj1BHt4qHu6ctfkOk8GMxx4Ic2Ris4weTKSB24LnARusoHkrCRLLnuyA5a2RAHhnoQn6bOSBBTUD2dMVjzox3lqhgSt7um0/Zg==', '2026-11-16 09:58:09'),
(2, 'NJCJA1MVjE3WX0+5wcelSDFakb5KJJPTfZDpAWTeED54zw==', 'SzJdtQRBgn9s5VYsXNAZ9ugKXj/oF6sHZiFy9HwxqLKPvqhLwupz6f+ufBE1M41QynmQBCyvRwchmg==', 'zylRQJ6zuNiFmEZEgeqTJCapXY2wSb+LGFJ4j53MaliPvHgLkAn7L5GfjUCIqaLHUvrzW/c+Qv7/36KPnVRJ/jflNtVtHHntbs371LXCsupnTqI+KBJNJ8uAngK0okvDTkOP+6honUioxhPmxJR/rBwQC+LExFwtzCyE+NmXikoZ8127syJ8OY1jEREVNQRooPCB2ii7MfKw1wswiDA6HDja4lMsGCRRDvpYgaZB/t9XnwkUksesydubGfk//cuC8dOprn9/Bjvm1O/xN71bDlHr3wZTinAK4e7Q1j10NKO8YKQ4cs6ZJ6Nz8Nb8QYoZ9oFL7BtxW9wXKTRPJnDyDg==', '2026-11-16 10:00:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_keys`
--
ALTER TABLE `admin_keys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `records`
--
ALTER TABLE `records`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_keys`
--
ALTER TABLE `admin_keys`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `records`
--
ALTER TABLE `records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
