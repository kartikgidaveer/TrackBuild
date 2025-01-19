-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2025 at 10:11 AM
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
-- Database: `trackbuild_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`details`)),
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `timestamp`) VALUES
(12, 11, 'Create Project', '{\"project_name\":\"Project 1\"}', '2024-12-27 20:45:12'),
(13, 11, 'Create Project', '{\"project_name\":\"Kartik\"}', '2024-12-27 20:58:06'),
(15, 11, 'Create Project', '{\"project_name\":\"New\"}', '2025-01-08 15:38:16'),
(16, 11, 'Create Project', '{\"project_name\":\"Project 1\"}', '2025-01-09 11:42:02'),
(17, 11, 'Create Project', '{\"project_name\":\"Project 1\"}', '2025-01-09 15:30:21'),
(18, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-09 16:38:40'),
(19, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-11 14:19:35'),
(20, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-11 14:23:23'),
(21, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-11 14:26:32'),
(22, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-11 14:29:13'),
(23, 11, 'Create Project', '{\"project_name\":\"Project 1\"}', '2025-01-11 14:31:56'),
(24, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-11 14:34:32'),
(25, 11, 'Create Project', '{\"project_name\":\"1\"}', '2025-01-11 14:43:32'),
(26, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-11 14:45:48'),
(27, 11, 'Create Project', '{\"project_name\":\"Project 1\"}', '2025-01-11 15:34:23'),
(28, 14, 'Create Project', '{\"project_name\":\"Kartik\"}', '2025-01-11 15:52:04'),
(29, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-13 12:58:56'),
(30, 11, 'Create Project', '{\"project_name\":\"Project 1\"}', '2025-01-13 12:59:51'),
(31, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-13 13:08:02'),
(32, 14, 'Create Project', '{\"project_name\":\"New\"}', '2025-01-13 15:06:37'),
(33, 11, 'Create Project', '{\"project_name\":\"Forum Mall\"}', '2025-01-19 00:39:29');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `expense_name` text DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `project_id`, `expense_name`, `category`, `amount`, `description`, `date`) VALUES
(62, 35, 'Labour', 'Labor', 11000.00, NULL, '2025-01-15'),
(63, 31, 'truck', 'Materials', 111000.00, NULL, '2025-01-15'),
(72, 36, 'Steel', 'Materials', 2000.00, NULL, '2025-01-18'),
(73, 36, 'Steel', 'Materials', 190000.00, NULL, '2025-01-18'),
(74, 36, 'Steel', 'Labor', 2000.00, NULL, '2025-01-18');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `project_id`, `message`, `is_read`, `created_at`) VALUES
(5, 10, 35, 'Budget is nearing the limit for project: New', 1, '2025-01-15 09:31:31'),
(6, 15, 31, 'Budget is nearing the limit for project: Kartik', 1, '2025-01-15 11:07:51'),
(11, 15, 31, 'Budget is nearing the limit for project: Kartik', 1, '2025-01-15 11:32:05'),
(31, 15, 31, 'Budget is nearing the limit for project: Kartik', 1, '2025-01-17 06:15:32'),
(32, 15, 31, 'Budget is nearing the limit for project: Kartik', 1, '2025-01-17 06:16:43'),
(48, 17, 36, 'Budget is nearing the limit for project: Forum Mall', 1, '2025-01-18 20:30:20'),
(49, 17, 36, 'Budget is nearing the limit for project: Forum Mall', 1, '2025-01-18 20:30:41'),
(50, 17, 36, 'Budget is nearing the limit for project: Forum Mall', 1, '2025-01-18 20:33:06'),
(51, 17, 36, 'Budget is nearing the limit for project: Forum Mall', 1, '2025-01-18 20:33:10'),
(52, 17, 36, 'Budget is nearing the limit for project: Forum Mall', 1, '2025-01-18 20:33:23'),
(53, 17, 36, 'Budget is nearing the limit for project: Forum Mall', 1, '2025-01-18 20:33:23');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `land_area` float NOT NULL,
  `budget` float DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `address` text DEFAULT NULL,
  `document` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `engineer_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `project_name`, `description`, `land_area`, `budget`, `duration`, `address`, `document`, `created_at`, `engineer_id`, `client_id`) VALUES
(31, 14, 'Kartik', '11', 111, 111111, 11, '11', '../../uploads/', '2025-01-11 10:22:04', 14, 15),
(35, 14, 'New', '1', 1, 11111, 1, 'Vjp', '../../uploads/', '2025-01-13 09:36:37', 14, 10),
(36, 11, 'Forum Mall', 'New One', 100, 200000, 3, 'Vjp', '../../uploads/', '2025-01-18 19:09:29', 11, 17);

-- --------------------------------------------------------

--
-- Table structure for table `project_access`
--

CREATE TABLE `project_access` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `role_name` enum('engineer','client') DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `role_name`) VALUES
(10, 'client', 'client@gmail.com', '$2y$10$ThmqsCLIw7mWLnsBpaMe8.E4oTMtxw8raRh/Gp0w9XkbsDDbsZCfK', '2024-12-27 11:32:20', 'client'),
(11, 'engineer', 'hahaha@gmail.com', '$2y$10$hnw41brD2wWrnT9DcnZ2T..SFIW7ErRO8OgjNJiGloXVi9xpeVKpu', '2024-12-27 14:47:22', 'engineer'),
(14, 'Kartik', 'gidaveerk@gmail.com', '$2y$10$rmpy007sfWL7YeC2IdrZt.hp8MM7fEinPq6kKWq7Vsw0xFxDFxFda', '2025-01-11 10:20:57', 'engineer'),
(15, 'Afnan', 'raees@gmail.com', '$2y$10$P3MFPDHEaN0xWXEtZh8jUedj6fzFI5OGk4qUoyQLrHEG9rnHtbkgq', '2025-01-11 10:21:21', 'client'),
(16, 'abc', 'engineer@gmail.com', '$2y$10$ETi70VXYOXVwmCUOcu815eBedLqG.1dkQQwDmZlzyyH1zTl9tXwPG', '2025-01-13 12:41:56', 'engineer'),
(17, 'Client1', 'client1@gmail.com', '$2y$10$PjRmJSQPGlRvACXhYPah7.1qNUWuHeJ.NMjsjxFZhkblpRHi3CJFm', '2025-01-13 15:54:04', 'client');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `engineer_id` (`engineer_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `project_access`
--
ALTER TABLE `project_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `project_access`
--
ALTER TABLE `project_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_access`
--
ALTER TABLE `project_access`
  ADD CONSTRAINT `project_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `project_access_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
