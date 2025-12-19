-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Dec 19, 2025 at 05:44 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hc_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `attr_activities`
--

CREATE TABLE `attr_activities` (
  `id` int NOT NULL,
  `activity_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attr_activities`
--

INSERT INTO `attr_activities` (`id`, `activity_name`, `created_at`) VALUES
(1, 'Internal', '2025-12-19 13:42:35'),
(2, 'External', '2025-12-19 13:42:35');

-- --------------------------------------------------------

--
-- Table structure for table `attr_skills`
--

CREATE TABLE `attr_skills` (
  `id` int NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attr_skills`
--

INSERT INTO `attr_skills` (`id`, `skill_name`, `created_at`) VALUES
(1, 'Soft Skill', '2025-12-19 13:42:35'),
(2, 'Hard Skill', '2025-12-19 13:42:35');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `org_id` int DEFAULT NULL,
  `position_id` int DEFAULT NULL,
  `level_id` int DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `full_name`, `org_id`, `position_id`, `level_id`, `status`, `created_at`, `updated_at`) VALUES
(2, 'JEM1212', 'paci', 3, 1, 2, 'Active', '2025-12-19 14:00:52', '2025-12-19 14:00:52'),
(3, 'SA001', 'Satu Satu', 1, 2, 4, 'Active', '2025-12-19 14:32:57', '2025-12-19 14:32:57');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `activity` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `activity`, `ip_address`, `created_at`) VALUES
(1, 1, 'User juliar berhasil login.', '192.168.65.1', '2025-12-19 12:17:44'),
(2, NULL, 'Registrasi sukses: tanjung (TJ002)', '192.168.65.1', '2025-12-19 12:18:20'),
(3, 1, 'User juliar berhasil login.', '192.168.65.1', '2025-12-19 12:18:28'),
(4, 1, 'User juliar berhasil login.', '192.168.65.1', '2025-12-19 12:29:50'),
(5, 1, 'User juliar berhasil login.', '192.168.65.1', '2025-12-19 12:36:46'),
(6, 1, 'User juliar berhasil login.', '192.168.65.1', '2025-12-19 12:42:45'),
(7, 1, 'Super Admin mengubah data user: tanjung', '192.168.65.1', '2025-12-19 12:47:15'),
(8, 1, 'Super Admin mengubah data user: tanjung22', '192.168.65.1', '2025-12-19 12:47:25'),
(9, 1, 'Super Admin menghapus user ID: 2', '192.168.65.1', '2025-12-19 12:47:29'),
(10, 1, 'Super Admin menambah user baru: tanjung', '192.168.65.1', '2025-12-19 12:48:00'),
(11, 1, 'Super Admin mengubah data user: tanjung', '192.168.65.1', '2025-12-19 12:48:07'),
(12, 1, 'Super Admin mengubah hak akses Role ID: 1', '192.168.65.1', '2025-12-19 12:48:59'),
(13, 1, 'Super Admin mengubah hak akses Role ID: 1', '192.168.65.1', '2025-12-19 12:49:12'),
(14, 1, 'Super Admin mengubah hak akses Role ID: 1', '192.168.65.1', '2025-12-19 12:49:25'),
(15, 1, 'Super Admin mengubah hak akses Role ID: 1', '192.168.65.1', '2025-12-19 12:49:31'),
(16, 1, 'Super Admin menambah role baru: Supervisor', '192.168.65.1', '2025-12-19 12:51:57'),
(17, 1, 'Super Admin mengubah role menjadi: Supervisor1', '192.168.65.1', '2025-12-19 12:52:02'),
(18, 1, 'Super Admin mengubah role menjadi: Supervisor', '192.168.65.1', '2025-12-19 12:52:07'),
(19, 1, 'Super Admin menghapus role ID: 6', '192.168.65.1', '2025-12-19 12:52:10'),
(20, 1, 'Super Admin memperbarui hak akses untuk Role ID: 1', '192.168.65.1', '2025-12-19 13:03:45'),
(21, 1, 'Super Admin memperbarui hak akses untuk Role ID: 1', '192.168.65.1', '2025-12-19 13:03:47'),
(22, 1, 'User juliar memperbarui profil.', '192.168.65.1', '2025-12-19 13:06:21'),
(23, 1, 'User juliar berhasil login.', '192.168.65.1', '2025-12-19 13:08:10'),
(24, 1, 'User juliar berhasil login.', '192.168.65.1', '2025-12-19 13:14:58'),
(25, 1, 'Super Admin memperbarui hak akses untuk Role ID: 1', '192.168.65.1', '2025-12-19 13:29:26'),
(26, 1, 'Super Admin memperbarui hak akses untuk Role ID: 1', '192.168.65.1', '2025-12-19 13:29:30'),
(27, 1, 'Super Admin memperbarui hak akses untuk Role ID: 1', '192.168.65.1', '2025-12-19 13:29:36'),
(28, 1, 'Super Admin memperbarui hak akses untuk Role ID: 1', '192.168.65.1', '2025-12-19 13:29:39'),
(29, 1, 'Super Admin mengelola data user: paci', '192.168.65.1', '2025-12-19 13:33:21'),
(30, 1, 'Super Admin mengelola data user: paci', '192.168.65.1', '2025-12-19 13:33:32'),
(31, 1, 'Menambah Activity baru: gabunngan', '192.168.65.1', '2025-12-19 13:43:53'),
(32, 1, 'Menghapus Activity ID: 3', '192.168.65.1', '2025-12-19 13:44:00'),
(33, 1, 'Menambah Skill baru: hard and soft', '192.168.65.1', '2025-12-19 13:44:10'),
(34, 1, 'Menghapus Skill ID: 3', '192.168.65.1', '2025-12-19 13:44:15'),
(35, 1, 'Mengubah Skill: Hard Skill 1', '192.168.65.1', '2025-12-19 13:44:20'),
(36, 1, 'Mengubah Skill: Hard Skill', '192.168.65.1', '2025-12-19 13:44:27'),
(37, 1, 'Mengelola Data Organization: Finance', '192.168.65.1', '2025-12-19 13:54:52'),
(38, 1, 'Mengelola Data Organization: Accounting', '192.168.65.1', '2025-12-19 13:54:57'),
(39, 1, 'Mengelola Data Job Level: Manager', '192.168.65.1', '2025-12-19 13:55:05'),
(40, 1, 'Mengelola Data Job Level: Supervisor', '192.168.65.1', '2025-12-19 13:55:10'),
(41, 1, 'Mengelola Data Job Level: Leader', '192.168.65.1', '2025-12-19 13:55:15'),
(42, 1, 'Mengelola Data Job Level: Leader 2', '192.168.65.1', '2025-12-19 13:55:21'),
(43, 1, 'Mengelola Data Job Level: Leader', '192.168.65.1', '2025-12-19 13:55:27'),
(44, 1, 'Mengelola Data Job Level: Leader', '192.168.65.1', '2025-12-19 13:55:35'),
(45, 1, 'Mengelola Data Organization: Tax', '192.168.65.1', '2025-12-19 13:55:53'),
(46, 1, 'Mengelola Data Job Position: Sales Supervisor', '192.168.65.1', '2025-12-19 13:56:03'),
(47, 1, 'Mengelola Data Job Position: Manager Accounting', '192.168.65.1', '2025-12-19 13:56:10'),
(48, 1, 'Mengelola Data Trainer: Paci Juliar', '192.168.65.1', '2025-12-19 13:56:29'),
(49, 1, 'Mengelola Data Trainer: Paci Juliar T', '192.168.65.1', '2025-12-19 13:56:45'),
(50, 1, 'Admin menambah karyawan baru: paci', '192.168.65.1', '2025-12-19 14:00:19'),
(51, 1, 'Admin mengubah data karyawan: paci JT', '192.168.65.1', '2025-12-19 14:00:27'),
(52, 1, 'Admin menghapus data karyawan ID: 1', '192.168.65.1', '2025-12-19 14:00:31'),
(53, 1, 'Admin menambah karyawan baru: paci', '192.168.65.1', '2025-12-19 14:00:52'),
(54, 1, 'Menambah data training baru: Leadership', '192.168.65.1', '2025-12-19 14:14:05'),
(55, 1, 'Admin menambah karyawan baru: Satu Satu', '192.168.65.1', '2025-12-19 14:32:57'),
(56, 1, 'Mengelola data training: Hardskill Operator', '192.168.65.1', '2025-12-19 14:34:24');

-- --------------------------------------------------------

--
-- Table structure for table `master_job_levels`
--

CREATE TABLE `master_job_levels` (
  `id` int NOT NULL,
  `level_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_job_levels`
--

INSERT INTO `master_job_levels` (`id`, `level_name`, `created_at`) VALUES
(1, 'Manager', '2025-12-19 13:55:05'),
(2, 'Supervisor', '2025-12-19 13:55:10'),
(4, 'Leader', '2025-12-19 13:55:35');

-- --------------------------------------------------------

--
-- Table structure for table `master_job_positions`
--

CREATE TABLE `master_job_positions` (
  `id` int NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_job_positions`
--

INSERT INTO `master_job_positions` (`id`, `position_name`, `created_at`) VALUES
(1, 'Sales Supervisor', '2025-12-19 13:56:03'),
(2, 'Manager Accounting', '2025-12-19 13:56:10');

-- --------------------------------------------------------

--
-- Table structure for table `master_organizations`
--

CREATE TABLE `master_organizations` (
  `id` int NOT NULL,
  `org_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_organizations`
--

INSERT INTO `master_organizations` (`id`, `org_name`, `created_at`) VALUES
(1, 'Finance', '2025-12-19 13:54:52'),
(2, 'Accounting', '2025-12-19 13:54:57'),
(3, 'Tax', '2025-12-19 13:55:53');

-- --------------------------------------------------------

--
-- Table structure for table `master_trainers`
--

CREATE TABLE `master_trainers` (
  `id` int NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `trainer_name` varchar(100) NOT NULL,
  `org_id` int DEFAULT NULL,
  `position_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_trainers`
--

INSERT INTO `master_trainers` (`id`, `employee_id`, `trainer_name`, `org_id`, `position_id`, `created_at`) VALUES
(1, 'TR001', 'Paci Juliar T', 2, 2, '2025-12-19 13:56:29');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int NOT NULL,
  `menu_name` varchar(100) NOT NULL,
  `menu_url` varchar(100) NOT NULL,
  `menu_icon` varchar(50) DEFAULT NULL,
  `menu_category` enum('Dashboard','Data Area','Analysis Area','System Area') NOT NULL,
  `order_no` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `menu_name`, `menu_url`, `menu_icon`, `menu_category`, `order_no`) VALUES
(1, 'Dashboard', '../dashboard/index.php', 'dashboard', 'Dashboard', 1),
(2, 'Management User', '../user-management/index.php', 'manage_accounts', 'Data Area', 2),
(3, 'Management Data', '../management-data/index.php', 'database', 'Data Area', 3),
(4, 'Atribut Management', '../atribut-management/index.php', 'settings_input_component', 'Data Area', 4),
(5, 'Management Menu', '../management-menu/index.php', 'menu', 'Data Area', 5),
(6, 'Employee Data', '../employee-data/index.php', 'badge', 'Data Area', 6),
(7, 'Average Training', '../average-training/index.php', 'equalizer', 'Analysis Area', 9),
(8, 'Training Data', '../training-data/index.php', 'history_edu', 'Data Area', 7),
(9, 'Training Penetration', '../training-penetration/index.php', 'groups_3', 'Analysis Area', 10),
(10, 'My Profile', '../profile/index.php', 'person', 'System Area', 10),
(11, 'Log Sistem', '../log-system/index.php', 'list_alt', 'System Area', 11),
(12, 'Training Detail', '../training-detail/index.php', 'receipt_long', 'Analysis Area', 8),
(13, 'Trainer Contribution', '../trainer-contribution/index.php', 'person_celebrate', 'Analysis Area', 11);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `created_at`) VALUES
(1, 'Super Admin', '2025-12-19 08:37:07'),
(2, 'Admin HC', '2025-12-19 08:37:07'),
(3, 'Manager', '2025-12-19 08:37:07'),
(4, 'Leader', '2025-12-19 08:37:07'),
(5, 'Employee', '2025-12-19 08:37:07');

-- --------------------------------------------------------

--
-- Table structure for table `role_access`
--

CREATE TABLE `role_access` (
  `id` int NOT NULL,
  `role_id` int NOT NULL,
  `menu_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role_access`
--

INSERT INTO `role_access` (`id`, `role_id`, `menu_id`) VALUES
(107, 1, 1),
(108, 1, 2),
(109, 1, 3),
(110, 1, 4),
(111, 1, 5),
(112, 1, 6),
(113, 1, 7),
(114, 1, 8),
(115, 1, 9),
(116, 1, 10),
(117, 1, 11),
(118, 1, 12),
(119, 1, 13);

-- --------------------------------------------------------

--
-- Table structure for table `trainings`
--

CREATE TABLE `trainings` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `trainer_id` int DEFAULT NULL,
  `held_by` varchar(255) DEFAULT NULL,
  `activity_id` int DEFAULT NULL,
  `skill_id` int DEFAULT NULL,
  `training_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `finish_time` time DEFAULT NULL,
  `fee` decimal(15,2) DEFAULT NULL,
  `is_certified` enum('Yes','No') DEFAULT 'No',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `trainings`
--

INSERT INTO `trainings` (`id`, `title`, `trainer_id`, `held_by`, `activity_id`, `skill_id`, `training_date`, `start_time`, `finish_time`, `fee`, `is_certified`, `created_at`) VALUES
(1, 'Leadership', 1, 'Jembo', 2, 2, '2025-12-20', '09:00:00', '12:00:00', 0.00, 'No', '2025-12-19 14:14:05'),
(2, 'Hardskill Operator', 1, 'Jembo Produksi', 1, 2, '2025-12-21', '08:00:00', '11:00:00', 0.00, 'No', '2025-12-19 14:34:24');

-- --------------------------------------------------------

--
-- Table structure for table `training_participants`
--

CREATE TABLE `training_participants` (
  `id` int NOT NULL,
  `training_id` int DEFAULT NULL,
  `employee_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `training_participants`
--

INSERT INTO `training_participants` (`id`, `training_id`, `employee_id`) VALUES
(1, 1, 2),
(2, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int DEFAULT '5',
  `is_verified` tinyint DEFAULT '0',
  `profile_photo` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `employee_id`, `email`, `phone`, `password`, `role_id`, `is_verified`, `profile_photo`, `created_at`) VALUES
(1, 'juliar', 'JT001', 'juliar@email.com', '081211366601', '$2y$12$Ite8osF.pEVirokdCl/cXOqUnC.fQfAL0/KyBMlk7wRpCDve2ewmS', 1, 1, 'profile_1_1766149581.png', '2025-12-19 08:39:11'),
(3, 'tanjung', 'JT002', 'tanjung@email.com', '081211366602', '$2y$12$wCfae2hu8hMP0IYUQ843U.ESvrIHqtyLXu4dDAIp/mwUUW2CsXxYK', 2, 1, 'default.png', '2025-12-19 12:48:00'),
(4, 'paci', 'PC002', 'paci@email.com', '081211366605', '$2y$12$eTMa0XBFaup8JRld7Y/94OH4YFuC0EWTSPfIO/DLpbRQBjqNdStfi', 4, 0, 'default.png', '2025-12-19 13:33:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attr_activities`
--
ALTER TABLE `attr_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attr_skills`
--
ALTER TABLE `attr_skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `master_job_levels`
--
ALTER TABLE `master_job_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_job_positions`
--
ALTER TABLE `master_job_positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_organizations`
--
ALTER TABLE `master_organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_trainers`
--
ALTER TABLE `master_trainers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_access`
--
ALTER TABLE `role_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `training_participants`
--
ALTER TABLE `training_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_id` (`training_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attr_activities`
--
ALTER TABLE `attr_activities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attr_skills`
--
ALTER TABLE `attr_skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `master_job_levels`
--
ALTER TABLE `master_job_levels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `master_job_positions`
--
ALTER TABLE `master_job_positions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_organizations`
--
ALTER TABLE `master_organizations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `master_trainers`
--
ALTER TABLE `master_trainers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_access`
--
ALTER TABLE `role_access`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `training_participants`
--
ALTER TABLE `training_participants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `master_organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `master_job_positions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`level_id`) REFERENCES `master_job_levels` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `master_trainers`
--
ALTER TABLE `master_trainers`
  ADD CONSTRAINT `master_trainers_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `master_organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `master_trainers_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `master_job_positions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_access`
--
ALTER TABLE `role_access`
  ADD CONSTRAINT `role_access_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_access_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trainings`
--
ALTER TABLE `trainings`
  ADD CONSTRAINT `trainings_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `master_trainers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `trainings_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `attr_activities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `trainings_ibfk_3` FOREIGN KEY (`skill_id`) REFERENCES `attr_skills` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_participants`
--
ALTER TABLE `training_participants`
  ADD CONSTRAINT `training_participants_ibfk_1` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_participants_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
