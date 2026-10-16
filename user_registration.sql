-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2026 at 10:09 AM
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
-- Database: `user_registration`
--

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `selector` char(24) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`selector`, `user_id`, `token_hash`, `expires_at`) VALUES
('8b94ff018c6324fac1cdacba', 7, '2dc6bcf02a536d2f327cb7302cfa8926f854242613683afffb5ef65b3bdba818', '2026-11-15 09:06:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `enrollment` varchar(30) DEFAULT NULL,
  `mobile` varchar(10) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `study_year` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `enrollment`, `mobile`, `department`, `study_year`, `gender`, `role`) VALUES
(2, 'Patel Krisha Ashokkumar', '25cs068@charusat.edu.in', '$2y$10$UALl4KWrs3RYgsN/YylniuCBhCEru9oq7W2zAuHSzUPnLnhR2aTv2', '2026-10-13 14:37:20', '25CS068', '9656565955', 'Computer Science / IT', '2nd Year', 'Female', 'admin'),
(3, 'PATEL KRISHA ASHOKKUMAR', '25cs063@charusat.edu.in', '$2y$10$T7EYLNBMvxprswYHcAJU2e1ahQ0lKDFTzg.RzecDnx6Ca61nC9m4i', '2026-10-13 19:31:47', '25CS063', '9656565955', 'Computer Science / IT', '1st Year', 'Female', 'student'),
(4, 'Patel Heni Pinalkumar', '25cs061@charusat.edu.in', '$2y$10$N9CcXR0NmXkxKS99g0/dMe7XuOQXmkG4hh4rVIWRbr2d/VePZ4Mba', '2026-10-14 01:59:41', '25CS061', '9656565955', 'Computer Science / IT', '2nd Year', 'Female', 'student'),
(5, 'Patel Jiya Umeshbahi', '25dcs089@charusat.edu.in', '$2y$10$o1ALu2Vc8gxYiC1N388kueQMykc6NvJk30rtfqwV1UzFGRxP.KPaW', '2026-10-14 14:20:07', '25DCS089', '9155865563', 'Electrical Engineering', '3rd Year', 'Female', 'student'),
(6, 'Patel Honey PareshBhai', '25dcs090@charusat.edu.in', '$2y$10$AZExmroAiZkkGQqpvzuZ.eACkFZakw5KAmXTR7zLzbRblL4RH6t6y', '2026-10-14 19:59:58', '25DCS090', '9155865563', 'Computer Science / IT', '1st Year', 'Female', 'student'),
(7, 'Patel Aditi Ashokkumar', '25cs001@charusat.edu.in', '$2y$10$fAUl7gO5pY8Xl7/KVx3INu2NYMXT0ppAKYit8CVUSFmymSatboNDG', '2026-10-15 11:49:20', '25CS001', '9656565955', 'Computer Science / IT', '2nd Year', 'Female', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`selector`),
  ADD KEY `idx_remember_user` (`user_id`),
  ADD KEY `idx_remember_expiry` (`expires_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `enrollment` (`enrollment`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
