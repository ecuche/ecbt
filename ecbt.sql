-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2025 at 08:45 PM
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
-- Database: `ecbt`
--

-- --------------------------------------------------------

--
-- Table structure for table `paper`
--

CREATE TABLE `paper` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL,
  `status` int(1) DEFAULT 0,
  `description` text NOT NULL,
  `time` int(3) NOT NULL,
  `poll` int(11) NOT NULL,
  `pass_mark` int(11) NOT NULL DEFAULT 50,
  `instruction` text NOT NULL,
  `csv` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paper`
--

INSERT INTO `paper` (`id`, `user_id`, `name`, `code`, `status`, `description`, `time`, `poll`, `pass_mark`, `instruction`, `csv`, `created_on`, `updated_on`, `deleted_on`) VALUES
(1, 1, 'English language', '8D66B9', 0, 'this paper is a test simulation', 60, 50, 50, 'please read very carefully before you answer', 'emekafelix_8D66B9', '2025-01-06 22:05:52', '2025-01-16 11:15:19', NULL),
(2, 1, 'physics', 'F7OB62', 1, 'MCS 888: critical infrastructure', 2147483647, 3, 50, 'answer all questions', 'msam_F7OB62', '2025-01-07 12:31:19', '2025-01-14 15:12:43', NULL),
(3, 2, 'maths 101', '437DGV', 1, 'maths 101 for NDA tama 1 22/25 session', 90, 60, 50, 'answer all questions', 'maths101_437DGV', '2025-01-07 14:30:02', '2025-01-16 15:55:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset`
--

INSERT INTO `password_reset` (`id`, `user_id`, `hash`, `expiry`, `created_on`, `updated_on`, `deleted_on`) VALUES
(3, 18, '69ba2f06246bd93404d67dd6dc7220b63ae0e6254d38bc398b417040a009ba45', '2024-12-31 21:20:41', '2024-12-31 19:17:23', '2024-12-31 19:20:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `remembered_logins`
--

CREATE TABLE `remembered_logins` (
  `token_hash` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `result`
--

CREATE TABLE `result` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `paper_id` int(11) NOT NULL,
  `poll` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `percent` decimal(5,2) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `grade` varchar(1) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `csv` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `result`
--

INSERT INTO `result` (`id`, `user_id`, `paper_id`, `poll`, `score`, `percent`, `start_time`, `grade`, `remark`, `csv`, `created_on`, `updated_on`, `deleted_on`) VALUES
(1, 18, 1, 20, 11, 55.00, '2025-01-23 10:33:02', 'A', 'very good', 'wegy53uwhr', '2025-01-12 10:33:55', '2025-01-16 13:54:20', NULL),
(6, 18, 2, 3, 2, 66.67, '2025-01-16 13:48:24', 'B', 'Very Good', 'F7OB62_18_2PV7AR', '2025-01-16 13:50:32', NULL, NULL),
(7, 15, 2, 3, 2, 66.67, '2025-01-16 13:48:24', 'B', 'Very Good', 'F7OB62_18_2PV7AR', '2025-01-16 13:50:32', NULL, NULL),
(8, 22, 3, 20, 11, 55.00, '2025-01-23 10:33:02', 'A', 'very good', 'wegy53uwhr', '2025-01-12 10:33:55', '2025-01-16 16:01:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(255) NOT NULL DEFAULT 'student',
  `reg_no` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`, `active`, `role`, `reg_no`, `created_on`, `updated_on`, `deleted_on`) VALUES
(1, 'uche emmanuel', 'ecuche@efcc.gov.ng', '$2y$10$3dyjtogXBZmyl4IUQ2K1tO42kxVWLiO6zAFlZK9XlRF2IkSYPFFhu', 1, 'instructor', '', '2024-12-10 14:48:04', '2025-01-13 11:48:52', NULL),
(14, 'nnamdi felix', 'nfelix@efcc.gov.ng', '$2y$10$wUbNFCS8EXZQ6R9mYITyGe8CCREh.L31RVzG4nqhcVcYQoTJ0eZ/2', 0, 'student', '', '2024-12-20 11:06:28', NULL, NULL),
(15, 'uche emmanuel', 'cousinavi30@gmail.com', '$2y$10$YX2j0i/OJoLidYQer/u.dOUyx2NC0DjFUiSAsn3St1vvPwk4H1uHG', 1, 'instructor', '', '2024-12-20 15:25:28', '2025-01-13 12:20:08', NULL),
(16, 'femeka felix', 'femeka@gmail.com', '$2y$10$j13tcv6fNSmy.Nq02q1eDejj35zE4G6LhF5XVeC714chJ4incUdau', 1, 'student', '', '2024-12-20 16:02:46', '2024-12-31 12:45:36', NULL),
(17, 'mac donald', 'macd@gmail.com', '$2y$10$3DpDKnDtCV4EdXzFqW7JIOr.yfPDBPKUbh2c2SSfcgM6AOCyoVJ5W', 0, 'student', '', '2024-12-20 16:16:05', NULL, NULL),
(18, 'sam moses', 'msam@gmail.com', '$2y$10$MlS55xmkxIa44gvcQSWJ2OF5hb3mrbSTjG0SMMbBi1vyZ1MA2u5jO', 1, 'student', '', '2024-12-20 16:23:09', '2025-01-04 22:34:03', NULL),
(19, 'thaddeous ogege', 'togege@efcc.gov.ng', '$2y$10$la3s0HMag/OXTVQ9m3jz2.422a6cHTWSjqwsSaDdPu0hTGpuBfUKK', 0, 'student', '', '2024-12-20 16:33:47', NULL, NULL),
(22, 'cynthia obiudu', 'cynthia@gmail.com', '$2y$10$wQdo685BOSnCC9rvBhqW/euNwm.pMXhYt7n1CssMEjPEF488cdQyi', 1, 'instructor', '', '2024-12-29 21:36:23', '2025-01-13 12:20:14', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `paper`
--
ALTER TABLE `paper`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `remembered_logins`
--
ALTER TABLE `remembered_logins`
  ADD PRIMARY KEY (`token_hash`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `paper_id` (`paper_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `paper`
--
ALTER TABLE `paper`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `result`
--
ALTER TABLE `result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `remembered_logins`
--
ALTER TABLE `remembered_logins`
  ADD CONSTRAINT `remembered_logins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `result`
--
ALTER TABLE `result`
  ADD CONSTRAINT `paper_id` FOREIGN KEY (`paper_id`) REFERENCES `paper` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
