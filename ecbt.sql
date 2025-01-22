-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2025 at 10:58 AM
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
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '\'{"view_result":1,"view_answers":1}\'',
  `created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paper`
--

INSERT INTO `paper` (`id`, `user_id`, `name`, `code`, `status`, `description`, `time`, `poll`, `pass_mark`, `instruction`, `settings`, `created_on`, `updated_on`, `deleted_on`) VALUES
(1, 1, 'English Language', '8D66B9', 1, 'this paper is a test simulation', 999, 10, 50, 'please read very carefully before you answer', '{\"view_result\":1,\"view_answers\":0}', '2025-01-06 22:05:52', '2025-01-19 20:40:27', NULL),
(2, 1, 'Physics', 'F7OB62', 1, 'MCS 888: critical infrastructure', 5, 3, 50, 'answer all questions', '{\"view_result\":1,\"view_answers\":1}', '2025-01-07 12:31:19', '2025-01-19 15:45:02', NULL),
(3, 14, 'Maths 101', '437DGV', 0, 'maths 101 for NDA tama 1 22/25 session', 5, 5, 50, 'answer all questions', '{\"view_result\":1,\"view_answers\":1}', '2025-01-07 14:30:02', '2025-01-19 00:02:11', NULL),
(4, 14, 'Chemistry 101 By Uche Emmanuel', 'E3UCVU', 0, 'chemistry question for ss2 class in cac 2009 set', 10, 10, 50, 'answer all questions', '{\"view_result\":1,\"view_answers\":0}', '2025-01-18 23:14:05', NULL, NULL);

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
  `end_time` timestamp NULL DEFAULT NULL,
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

INSERT INTO `result` (`id`, `user_id`, `paper_id`, `poll`, `score`, `percent`, `start_time`, `end_time`, `grade`, `remark`, `csv`, `created_on`, `updated_on`, `deleted_on`) VALUES
(37, 1, 1, 10, 4, 40.00, '2025-01-21 13:20:43', '2025-01-22 10:10:28', 'D', 'Pass', '8D66B9_1_13PVG9', '2025-01-21 13:20:43', '2025-01-22 10:12:31', NULL);

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
(1, 'uche emmanuel', 'ecuche@efcc.gov.ng', '$2y$10$3dyjtogXBZmyl4IUQ2K1tO42kxVWLiO6zAFlZK9XlRF2IkSYPFFhu', 1, 'instructor', '102651', '2024-12-10 14:48:04', '2025-01-19 00:06:34', NULL),
(14, 'nnamdi felix', 'nfelix@efcc.gov.ng', '$2y$10$3dyjtogXBZmyl4IUQ2K1tO42kxVWLiO6zAFlZK9XlRF2IkSYPFFhu', 1, 'instructor', '102650', '2024-12-20 11:06:28', '2025-01-18 23:49:53', NULL),
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
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paper`
--
ALTER TABLE `paper`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `result`
--
ALTER TABLE `result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

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
