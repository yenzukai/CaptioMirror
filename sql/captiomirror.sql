-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 09, 2024 at 04:35 AM
-- Server version: 10.10.2-MariaDB
-- PHP Version: 8.1.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `captiomirror`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_assistant_preferences`
--

DROP TABLE IF EXISTS `ai_assistant_preferences`;
CREATE TABLE IF NOT EXISTS `ai_assistant_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `assistant_name` varchar(255) NOT NULL,
  `voice_assistant` varchar(255) NOT NULL,
  `assistant_style` varchar(255) NOT NULL,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ai_assistant_preferences`
--

INSERT INTO `ai_assistant_preferences` (`id`, `user_id`, `assistant_name`, `voice_assistant`, `assistant_style`, `last_updated`) VALUES
(25, 22, 'Alex', 'Google US English', 'A math tutor who helps students of all levels understand and solve mathematical problems', '2024-09-25 07:08:55'),
(24, 19, 'Ayi', 'Google US English', 'Personal Financial Advisor', '2024-09-16 01:46:58'),
(19, 14, 'Alex', 'Google US English', 'Friendly Assistant', '2024-09-12 16:31:02'),
(20, 3, 'Jason', 'Will', 'A math tutor who helps students of all levels understand and solve mathematical problems', '2024-11-04 01:58:41'),
(21, 15, 'Trisha', 'Google UK English Female', 'Friendly Assistant', '2024-09-15 03:05:08'),
(27, 24, 'Alexa', 'Will', 'A friendly assistant who loves to chat and assist with the user', '2024-11-04 06:17:37'),
(28, 25, 'Alex', 'henry', 'Friendly Assistant', '2024-11-12 08:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `active`) VALUES
(1, 'DateTime', 1),
(2, 'Weather', 1),
(3, 'Stock Prices', 1),
(4, 'Quote of the Day', 1),
(5, 'Assistant', 1),
(6, 'Todo Lists', 1),
(7, 'Scheduler', 1),
(8, 'Music Player', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `is_cleared` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `created_at`, `is_read`, `is_cleared`) VALUES
(4, 3, 'A reminder for you, yenzukai: Graduation Pictorial is scheduled at 2024-10-21 17:25:00', '2024-10-21 09:24:23', 1, 0),
(3, 3, 'A reminder for you, yenzukai: halloween party is scheduled at 2024-10-21 17:11:00', '2024-10-21 09:10:19', 1, 0),
(5, 3, 'A reminder for you, yenzukai: acquaintance party is scheduled at 2024-10-21 17:33:00', '2024-10-21 09:32:19', 1, 1),
(6, 3, 'A reminder for you, yenzukai: Drinking with the Boys! is scheduled at 2024-10-22 11:05:00', '2024-10-22 03:04:32', 1, 0),
(7, 24, 'A reminder for you, Rogel: Go to school is scheduled at 2024-11-04 14:25:00', '2024-11-04 06:24:18', 1, 0),
(8, 3, 'A reminder for you, yenzukai: OJT is scheduled at 2024-11-19 14:27:00', '2024-11-19 06:12:18', 1, 0),
(9, 3, 'A reminder for you, yenzukai: halloween party is scheduled at 2024-12-03 22:54:00', '2024-12-03 14:53:16', 1, 0),
(10, 3, 'A reminder for you, yenzukai: Drinking with the Boys! is scheduled at 2024-12-03 22:58:00', '2024-12-03 14:57:46', 1, 0),
(11, 3, 'A reminder for you, yenzukai: Drinking with the Boys! is scheduled at 2024-12-03 23:09:00', '2024-12-03 15:08:15', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `reload_flags`
--

DROP TABLE IF EXISTS `reload_flags`;
CREATE TABLE IF NOT EXISTS `reload_flags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reload` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `reload_flags`
--

INSERT INTO `reload_flags` (`id`, `user_id`, `reload`, `created_at`, `updated_at`) VALUES
(57, 18, 0, '2024-09-16 01:29:28', '2024-10-24 07:09:10'),
(51, 7, 0, '2024-09-04 06:20:49', '2024-09-04 06:24:33'),
(49, 3, 0, '2024-09-03 13:56:41', '2024-12-09 04:33:08'),
(52, 13, 1, '2024-09-12 16:01:19', '2024-09-12 16:01:19'),
(53, 14, 1, '2024-09-12 16:18:01', '2024-09-12 16:18:01'),
(59, 22, 1, '2024-09-25 06:00:47', '2024-09-25 07:15:18'),
(58, 19, 0, '2024-09-16 01:44:02', '2024-09-16 01:48:37'),
(60, 24, 0, '2024-11-04 06:14:19', '2024-11-04 06:29:45'),
(61, 25, 0, '2024-11-12 08:56:48', '2024-11-12 12:36:16');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `alarm_before` int(11) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `notified` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `user_id`, `event_name`, `description`, `alarm_before`, `start_date`, `end_date`, `created_at`, `notified`) VALUES
(5, 22, 'Happy Halloween', 'What a scary event this would going to be!', 30, '2024-11-01 00:00:00', '2024-09-03 23:59:00', '2024-09-25 06:53:04', 0),
(4, 22, 'Birthday Party', 'Her birthday party would be extravagant!', 60, '2024-09-26 14:12:00', '2024-09-26 23:59:00', '2024-09-25 06:24:54', 1),
(17, 3, 'Graduation Pictorial', 'I will be the most attractive person during our graduation pictorial.', 1, '2024-10-21 17:25:00', '2024-10-22 22:00:00', '2024-10-02 02:46:56', 1),
(16, 3, 'Drinking with the Boys!', '', 1, '2024-12-03 23:09:00', '2024-12-03 23:59:00', '2024-09-29 14:58:04', 1),
(24, 3, 'Bestfriend\'s Wedding Party', 'It is going to be one of the most unforgettable moment for me as his bestfriend.', 1, '2024-10-07 10:23:00', '2024-10-07 23:50:00', '2024-10-07 02:18:54', 0),
(27, 3, 'halloween party', 'trick or treat', 1, '2024-12-03 22:54:00', '2024-12-03 23:59:00', '2024-10-15 13:00:31', 1),
(28, 3, 'acquaintance party', '', 1, '2024-10-21 17:33:00', '2024-10-22 00:00:00', '2024-10-17 11:58:25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `schedules_history`
--

DROP TABLE IF EXISTS `schedules_history`;
CREATE TABLE IF NOT EXISTS `schedules_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `alarm_before` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `schedules_history`
--

INSERT INTO `schedules_history` (`id`, `user_id`, `event_name`, `description`, `start_date`, `alarm_before`, `created_at`) VALUES
(1, 3, 'Drinking with the Boys!', '', '2024-09-29 23:00:00', 1, '2024-09-29 22:59:00'),
(2, 3, 'Graduation Pictorial', 'I will be the most attractive person during our graduation pictorial.', '2024-10-02 10:53:00', 1, '2024-10-02 10:52:00'),
(3, 3, 'Gender Reveal', 'I hope it will be a boy', '2024-10-03 15:50:00', 1, '2024-10-03 15:49:01'),
(4, 3, 'Gender Reveal Part 2', 'I hope it will be a girl', '2024-10-03 16:21:00', 1, '2024-10-03 16:20:00'),
(5, 18, 'Irinuman', 'Ata iyoo padiu', '2024-10-05 15:29:00', 1, '2024-10-05 15:28:00'),
(6, 3, 'Swimming sakalam', '', '2024-10-05 15:29:00', 1, '2024-10-05 15:28:00'),
(7, 3, 'Swimming sakalam', '', '2024-10-05 15:31:00', 1, '2024-10-05 15:30:06'),
(8, 18, 'Irinom', 'Atyaaaan', '2024-10-05 15:31:00', 1, '2024-10-05 15:30:06'),
(9, 3, 'Bestfriend\'s Wedding Party', 'It is going to be one of the most unforgettable moment for me as his bestfriend.', '2024-10-07 10:23:00', 1, '2024-10-07 10:22:00'),
(10, 22, 'Birthday Party', 'Her birthday party would be extravagant!', '2024-09-26 14:12:00', 60, '2024-10-07 21:58:19'),
(11, 3, 'Honeymoon', '', '2024-10-07 21:59:00', 1, '2024-10-07 21:58:36'),
(12, 22, 'Birthday Party', 'Her birthday party would be extravagant!', '2024-09-26 14:12:00', 60, '2024-10-07 21:58:55'),
(13, 3, 'Honeymoon', '', '2024-10-07 21:59:00', 1, '2024-10-07 21:59:13'),
(14, 22, 'Birthday Party', 'Her birthday party would be extravagant!', '2024-09-26 14:12:00', 60, '2024-10-07 21:59:30'),
(15, 3, 'Honeymoon', '', '2024-10-07 21:59:00', 1, '2024-10-07 21:59:48'),
(16, 22, 'Birthday Party', 'Her birthday party would be extravagant!', '2024-09-26 14:12:00', 60, '2024-10-07 22:16:18'),
(17, 3, 'Honeymoon', '', '2024-10-07 22:17:00', 1, '2024-10-07 22:16:35'),
(18, 22, 'Birthday Party', 'Her birthday party would be extravagant!', '2024-09-26 14:12:00', 60, '2024-10-07 22:16:54'),
(19, 3, 'Honeymoon', '', '2024-10-07 22:17:00', 1, '2024-10-07 22:17:11'),
(20, 22, 'Birthday Party', 'Her birthday party would be extravagant!', '2024-09-26 14:12:00', 60, '2024-10-07 22:22:17'),
(21, 18, 'Irinom', 'Atyaaaan', '2024-10-07 22:23:00', 1, '2024-10-07 22:22:34'),
(22, 3, 'Honeymoon', '', '2024-10-07 22:23:00', 1, '2024-10-07 22:22:51'),
(23, 22, 'Birthday Party', 'Her birthday party would be extravagant!', '2024-09-26 14:12:00', 60, '2024-10-07 22:23:07'),
(24, 18, 'Irinom', 'Atyaaaan', '2024-10-07 22:23:00', 1, '2024-10-07 22:23:26'),
(25, 3, 'Honeymoon', '', '2024-10-07 22:23:00', 1, '2024-10-07 22:23:45'),
(26, 3, 'halloween party', 'trick or treat', '2024-10-15 21:15:00', 1, '2024-10-15 21:14:23'),
(27, 3, 'halloween party', 'trick or treat', '2024-10-15 21:15:00', 1, '2024-10-15 21:14:44'),
(28, 3, 'halloween party', 'trick or treat', '2024-10-21 11:30:00', 1, '2024-10-21 11:29:18'),
(29, 3, 'halloween party', 'trick or treat', '2024-10-21 11:30:00', 1, '2024-10-21 11:29:36'),
(30, 3, 'halloween party', 'trick or treat', '2024-10-21 17:11:00', 1, '2024-10-21 17:10:19'),
(31, 3, 'halloween party', 'trick or treat', '2024-10-21 17:11:00', 1, '2024-10-21 17:10:36'),
(32, 3, 'Graduation Pictorial', 'I will be the most attractive person during our graduation pictorial.', '2024-10-21 17:25:00', 1, '2024-10-21 17:24:23'),
(33, 3, 'Graduation Pictorial', 'I will be the most attractive person during our graduation pictorial.', '2024-10-21 17:25:00', 1, '2024-10-21 17:24:44'),
(34, 3, 'acquaintance party', '', '2024-10-21 17:33:00', 1, '2024-10-21 17:32:19'),
(35, 3, 'Drinking with the Boys!', '', '2024-10-22 11:05:00', 1, '2024-10-22 11:04:32'),
(36, 24, 'Go to school', 'Back to school', '2024-11-04 14:25:00', 1, '2024-11-04 14:24:18'),
(37, 3, 'OJT', '', '2024-11-19 14:27:00', 15, '2024-11-19 14:12:18'),
(38, 3, 'halloween party', 'trick or treat', '2024-12-03 22:54:00', 1, '2024-12-03 22:53:16'),
(39, 3, 'Drinking with the Boys!', '', '2024-12-03 22:58:00', 1, '2024-12-03 22:57:46'),
(40, 3, 'Drinking with the Boys!', '', '2024-12-03 23:09:00', 1, '2024-12-03 23:08:15');

-- --------------------------------------------------------

--
-- Table structure for table `session_tokens`
--

DROP TABLE IF EXISTS `session_tokens`;
CREATE TABLE IF NOT EXISTS `session_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `logged_in` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=251 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `session_tokens`
--

INSERT INTO `session_tokens` (`id`, `token`, `user_id`, `logged_in`, `created_at`, `expires_at`) VALUES
(212, 'adc0b9d608a50c6abeb83bbfd032b9ba', 3, 1, '2024-10-29 12:18:15', NULL),
(213, '845d6234248090d9a11ab1ebc4d88cab', 3, 0, '2024-10-30 04:59:54', NULL),
(214, '6d04a7467d4add749935ca4c4d2dc02f', NULL, 0, '2024-10-30 05:02:06', NULL),
(215, '909552cef38a48ad914b85acc2f1152a', NULL, 0, '2024-10-30 13:20:13', NULL),
(216, '2928a591fd5a75c86af0ab23b23c6819', 3, 0, '2024-11-02 14:16:50', NULL),
(217, '596b9be2e145c2ab84b5c657ad322909', 3, 0, '2024-11-04 06:00:06', NULL),
(218, 'c4b881b8a7fb18b2e2205bdcd2e7e447', 24, 1, '2024-11-04 06:13:36', NULL),
(219, 'fc84fd77fac968e2b6cbcde970a3d940', 3, 1, '2024-11-07 02:26:34', NULL),
(220, '1789d72b8eac54172d73eea620f2dfea', 3, 0, '2024-11-07 02:42:18', NULL),
(221, 'e8cfd17f949fa6fda3eb45012441b5eb', 3, 1, '2024-11-08 15:10:05', NULL),
(222, '043e598e9265c5df15db3613998dffbe', 3, 1, '2024-11-10 11:21:01', NULL),
(223, '3ecf7e653d053bd17d9f9455bf914b12', 3, 1, '2024-11-10 11:22:10', NULL),
(226, '5d3c105d9ebaa4ad5fa521edbd6f91f6', 25, 0, '2024-11-12 08:56:05', NULL),
(225, 'bb6b6229f39f328f1e2ef26d80390b12', 3, 1, '2024-11-10 11:27:58', NULL),
(227, '26e85382ac11f235a3cddf74e0ff809d', NULL, 0, '2024-11-15 10:54:04', NULL),
(228, '2489aa1f6b8121d83042145c09a346fb', NULL, 0, '2024-11-15 10:58:48', NULL),
(229, '2ae00c7f8365a014828251d98850d17c', NULL, 0, '2024-11-15 11:00:32', NULL),
(230, 'e48dd73e498ef3adfd6cd210c67a311d', 3, 1, '2024-11-15 11:03:54', NULL),
(231, '6a12a01aed152878702165d058f6b214', 3, 1, '2024-11-15 11:07:04', NULL),
(232, '92f30c3bceada081f7f1b4a7511b6ac1', 3, 1, '2024-11-19 15:12:13', NULL),
(233, '9d8048bade8c8787bb9f95e6b545d6ed', 3, 0, '2024-11-20 15:33:29', NULL),
(234, '9a1e57361617478326353e5e31078668', 3, 0, '2024-11-20 16:41:06', NULL),
(235, 'ebfd842fe5a07c980844ae4f2dd48995', 3, 0, '2024-11-21 04:40:47', NULL),
(236, '158a5d7480410e7ec244387732e46dcc', 3, 1, '2024-11-21 13:53:02', NULL),
(237, '62577d1f98eb526dbf2702a7c1af46f2', 3, 0, '2024-11-21 13:53:45', NULL),
(238, '4e2d99975d5a28174c895ea17d375e58', NULL, 0, '2024-11-24 13:41:33', NULL),
(239, '648523d3b0b4046dc9a62145d85cf37a', 3, 1, '2024-11-24 13:43:21', NULL),
(240, '05d8531b305d1d6db51168beb6133517', 3, 1, '2024-11-24 13:52:02', NULL),
(241, '7b5fc7386a4317f0302d1780914251a6', 3, 1, '2024-11-25 07:17:38', NULL),
(242, '986db4f328c60b7f1af3b6f258e4ddc6', NULL, 0, '2024-11-25 07:18:51', NULL),
(243, 'd2594b11135165824b7c5aa78afcdb24', 3, 1, '2024-11-25 07:19:32', NULL),
(244, '907740ce09aa69e485895aab144fb8a6', 3, 1, '2024-11-25 08:00:44', NULL),
(245, '1ee905a29291dbc16f16e5d71d0906ce', 3, 1, '2024-11-25 08:01:29', NULL),
(246, '6ccf7c6a211e9577a5adaf9c2fecfdaa', 3, 1, '2024-12-03 14:44:48', NULL),
(247, '6e5eb12af4a20f556477af4891701178', 3, 0, '2024-12-03 14:45:31', NULL),
(248, '05ba5f65e9c055449f71f53cb4f1d5a9', 3, 0, '2024-12-04 07:32:15', NULL),
(249, 'b9cf89bf6f61799d01c93deccf170345', 3, 1, '2024-12-09 04:19:36', NULL),
(250, '72e7b80c7c3e170a6e26a91dc025782e', 3, 0, '2024-12-09 04:20:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `verification_code` varchar(25) DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL,
  `email_verified_at` varchar(25) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT '../assets/svg/account-avatar-default.svg',
  `background_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone_number`, `date_of_birth`, `password`, `verification_code`, `remember_token`, `email_verified_at`, `created_at`, `profile_picture`, `background_path`) VALUES
(3, 'yenzukai', 'rinkashixd@gmail.com', '+639664809996', '2002-12-29', '$2y$10$j.lMzPOe9C4n08U2dBoGxeb/CYB/zRyu1fIXZH3lJt5CicbbOOjTy', '313868', NULL, '2024-08-19 09:32:29', '2024-08-18 12:44:09', '../uploads/profile_pictures/yenzukai_66f2602dad9f7.png', '../uploads/background_logo/yenzukai_6726413e4643a.png'),
(19, 'maaars_', 'gabaymadolores@gmail.com', '09165554966', '2003-08-21', '$2y$10$lsYJHcJiUQUApoCyrz3MP.pN805K38r.QQm2ciB3d5xM45vYHqMGW', '179801', NULL, '2024-09-16 09:42:26', '2024-09-16 01:34:25', NULL, NULL),
(22, 'mjrojas_parsu', 'mjrojas.pbox@parsu.edu.ph', '09935320145', '2000-10-31', '$2y$10$c9jqN7.cz2xWQxuK.owOz.lBaO01H5j9pRE/MSFU5onv8ehdgn2/y', NULL, NULL, '2024-09-25 13:33:34', '2024-09-25 05:18:38', '../uploads/profile_pictures/mjrojas_parsu_66f3a359f293c.jpg', NULL),
(24, 'Rogel', 'rogelnavarro74@gmail.com', '09984518951', NULL, '$2y$10$lYGsgKJHWSu6yBVkqnCiHuOZDtYi2VfbYFYaz7glEh/Es0N84UZN6', '169239', NULL, '2024-11-04 14:13:29', '2024-11-04 06:12:36', NULL, NULL),
(26, 'emjay', 'emjayprojas@gmail.com', '09223344667', NULL, '$2y$10$daSqrcwGKM52kzuEEfMFkOecgdjbEXgk8ApXfCwKTUU/dmHF5sjjy', '281728', NULL, '2024-11-24 21:49:51', '2024-11-24 13:48:22', '../assets/svg/account-avatar-default.svg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_history`
--

DROP TABLE IF EXISTS `users_history`;
CREATE TABLE IF NOT EXISTS `users_history` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users_history`
--

INSERT INTO `users_history` (`id`, `username`, `email`, `password`, `created_at`, `deleted_at`) VALUES
(18, 'Rogel', 'rogelnavarro74@gmail.com', '$2y$10$AxTonptIVbdMraijjX.KOeo/1.MElYO/naJoEd6QUGLdAh3x6sytq', '2024-09-16 09:27:46', '2024-10-24 15:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_modules`
--

DROP TABLE IF EXISTS `user_modules`;
CREATE TABLE IF NOT EXISTS `user_modules` (
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`,`module_id`),
  KEY `module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_modules`
--

INSERT INTO `user_modules` (`user_id`, `module_id`, `active`, `last_updated`) VALUES
(3, 1, 1, '2024-11-21 15:03:23'),
(3, 4, 1, '2024-11-21 15:03:23'),
(3, 3, 1, '2024-11-21 15:03:23'),
(3, 2, 1, '2024-11-21 15:03:23'),
(3, 5, 1, '2024-11-21 15:03:23'),
(24, 5, 1, '2024-11-04 06:14:19'),
(24, 3, 1, '2024-11-04 06:14:19'),
(24, 7, 1, '2024-11-04 06:14:19'),
(24, 2, 1, '2024-11-04 06:14:19'),
(24, 1, 1, '2024-11-04 06:14:19'),
(22, 2, 1, '2024-09-25 07:00:27'),
(22, 6, 1, '2024-09-25 07:00:27'),
(22, 3, 1, '2024-09-25 07:00:27'),
(22, 4, 1, '2024-09-25 07:00:27'),
(22, 1, 1, '2024-09-25 07:00:27'),
(3, 6, 1, '2024-11-21 15:03:23'),
(22, 5, 1, '2024-09-25 07:00:27'),
(24, 6, 1, '2024-11-04 06:14:19'),
(19, 5, 1, '2024-09-16 01:44:44'),
(19, 1, 1, '2024-09-16 01:44:44'),
(19, 4, 1, '2024-09-16 01:44:44'),
(19, 3, 1, '2024-09-16 01:44:44'),
(19, 6, 1, '2024-09-16 01:44:44'),
(19, 2, 1, '2024-09-16 01:44:44'),
(3, 7, 1, '2024-11-21 15:03:23'),
(24, 4, 0, '2024-11-04 06:14:19'),
(25, 5, 1, '2024-11-12 08:56:48'),
(25, 1, 1, '2024-11-12 08:56:48'),
(25, 4, 1, '2024-11-12 08:56:48'),
(25, 7, 1, '2024-11-12 08:56:48'),
(25, 3, 1, '2024-11-12 08:56:48'),
(25, 6, 1, '2024-11-12 08:56:48'),
(25, 2, 1, '2024-11-12 08:56:48'),
(3, 8, 1, '2024-11-21 15:03:23');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `weather_location` varchar(255) NOT NULL,
  `date_time_format` varchar(50) NOT NULL,
  `show_background` tinyint(1) NOT NULL DEFAULT 1,
  `text_size` varchar(10) DEFAULT 'normal',
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `weather_location`, `date_time_format`, `show_background`, `text_size`, `last_updated`) VALUES
(30, 24, 'New York', 'en-US', 1, 'normal', '2024-11-04 06:14:29'),
(31, 25, 'New York', 'en-US', 1, 'normal', '2024-11-12 08:52:26'),
(28, 22, 'Lagonoy, Camarines Sur', 'en-US', 1, 'normal', '2024-09-25 07:10:00'),
(27, 19, 'Goa, Camarines Sur', 'en-US', 1, 'normal', '2024-09-16 01:46:58'),
(22, 14, 'New York', 'en-US', 1, 'normal', '2024-09-12 16:31:02'),
(23, 3, 'Lagonoy, Camarines Sur', 'en-US', 1, 'normal', '2024-12-09 04:32:37'),
(24, 15, 'Lagonoy, Camarines Sur', 'en-US', 1, 'normal', '2024-09-15 03:05:08');

-- --------------------------------------------------------

--
-- Table structure for table `user_stock_symbols`
--

DROP TABLE IF EXISTS `user_stock_symbols`;
CREATE TABLE IF NOT EXISTS `user_stock_symbols` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stock_symbol` varchar(10) DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=521 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_stock_symbols`
--

INSERT INTO `user_stock_symbols` (`id`, `user_id`, `stock_symbol`, `last_updated`) VALUES
(472, 22, 'TSLA', '2024-09-25 07:10:00'),
(382, 15, 'TSLA', '2024-09-15 03:05:08'),
(381, 15, 'GOOGL', '2024-09-15 03:05:08'),
(380, 15, 'AAPL', '2024-09-15 03:05:08'),
(511, 3, 'AAPL', '2024-11-04 01:58:41'),
(510, 3, 'GOOGL', '2024-11-04 01:58:41'),
(509, 3, 'TSLA', '2024-11-04 01:58:41'),
(370, 14, 'TSLA', '2024-09-12 16:31:02'),
(369, 14, 'GOOGL', '2024-09-12 16:31:02'),
(368, 14, 'AAPL', '2024-09-12 16:31:02'),
(471, 22, 'GOOGL', '2024-09-25 07:10:00'),
(470, 22, 'AAPL', '2024-09-25 07:10:00'),
(418, 19, 'TSLA', '2024-09-16 01:46:58'),
(417, 19, 'GOOGL', '2024-09-16 01:46:58'),
(416, 19, 'AAPL', '2024-09-16 01:46:58'),
(517, 24, 'AAPL', '2024-11-04 06:17:37'),
(516, 24, 'GOOGL', '2024-11-04 06:17:37'),
(515, 24, 'TSLA', '2024-11-04 06:17:37'),
(518, 25, 'AAPL', '2024-11-12 08:52:26'),
(519, 25, 'GOOGL', '2024-11-12 08:52:26'),
(520, 25, 'TSLA', '2024-11-12 08:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `user_todo_lists`
--

DROP TABLE IF EXISTS `user_todo_lists`;
CREATE TABLE IF NOT EXISTS `user_todo_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `task` varchar(255) DEFAULT NULL,
  `checked` tinyint(1) DEFAULT 0,
  `removed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_todo_lists`
--

INSERT INTO `user_todo_lists` (`id`, `user_id`, `task`, `checked`, `removed`, `created_at`) VALUES
(42, 3, 'Brush your teeth everyday', 1, 1, '2024-11-21 04:41:58'),
(41, 3, 'Watch Hello, Love, Again', 1, 0, '2024-11-20 16:29:37');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
