-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 13, 2026 at 10:36 AM
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
-- Database: `bachat_gat_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `profile_image` varchar(255) DEFAULT 'default-avatar.png',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password`, `role`, `profile_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@bachatgat.com', '9876543210', '$2y$10$JgNrzMFkLHVic6GvvcVcWuNPZ0qYdFmfizT8HjeK.Ptj7t/9aICSW', 'admin', '69b3c7a8c2951_1773389736.jpg', 'active', '2026-03-04 09:17:40', '2026-03-13 08:21:01'),
(5, 'Shivam Patil', 'shivampatil4972@gmail.com', '7709792167', '$2y$10$UWqKiZ858WYfsvpiHpUxIenS7regWv/ORb.elKLzfM8lK8MM7U6ta', 'member', '69b3c6c3b2fd7_1773389507.jpg', 'active', '2026-03-04 09:25:44', '2026-03-13 08:12:27'),
(6, 'Sanika Patil', 'sanikap324@gmail.com', '8237997781', '$2y$10$iUncVCBK3HFb7xAjgfqlUeb8YFTm2U0AWGtkmoByyt7WoL85cwspG', 'member', '69b3c6ad632ac_1773389485.jpg', 'active', '2026-03-04 09:26:40', '2026-03-13 08:11:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
