-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 30, 2025 at 10:51 PM
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
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ai_assistant_preferences`
--

INSERT INTO `ai_assistant_preferences` (`id`, `user_id`, `assistant_name`, `voice_assistant`, `assistant_style`, `last_updated`) VALUES
(24, 19, 'Ayi', 'Google US English', 'Personal Financial Advisor', '2024-09-16 01:46:58'),
(20, 3, 'Jason', 'Will', 'A math tutor who helps students of all levels understand and solve mathematical problems', '2024-11-04 01:58:41'),
(27, 24, 'Alexa', 'Will', 'A friendly assistant who loves to chat and assist with the user', '2024-11-04 06:17:37'),
(29, 27, 'Alex', 'Will', 'A friendly assistant who loves to chat and assist with the user', '2025-08-30 21:27:32');

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
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `reload_flags`
--

INSERT INTO `reload_flags` (`id`, `user_id`, `reload`, `created_at`, `updated_at`) VALUES
(49, 3, 0, '2024-09-03 13:56:41', '2024-12-16 02:40:21'),
(58, 19, 0, '2024-09-16 01:44:02', '2024-09-16 01:48:37'),
(60, 24, 0, '2024-11-04 06:14:19', '2024-11-04 06:29:45'),
(62, 27, 0, '2025-08-30 21:05:01', '2025-08-30 22:26:01');

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
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=258 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone_number`, `date_of_birth`, `password`, `verification_code`, `remember_token`, `email_verified_at`, `created_at`, `profile_picture`, `background_path`) VALUES
(3, 'yenzukai', 'rinkashixd@gmail.com', '+639664809996', '2002-12-29', '$2y$10$j.lMzPOe9C4n08U2dBoGxeb/CYB/zRyu1fIXZH3lJt5CicbbOOjTy', '313868', NULL, '2024-08-19 09:32:29', '2024-08-18 12:44:09', '../uploads/profile_pictures/yenzukai_66f2602dad9f7.png', '../uploads/background_logo/yenzukai_6726413e4643a.png'),
(19, 'maaars_', 'gabaymadolores@gmail.com', '09165554966', '2003-08-21', '$2y$10$lsYJHcJiUQUApoCyrz3MP.pN805K38r.QQm2ciB3d5xM45vYHqMGW', '179801', NULL, '2024-09-16 09:42:26', '2024-09-16 01:34:25', NULL, NULL),
(27, 'emjay', 'emjayprojas@gmail.com', '+639935320145', '2002-12-29', '$2y$10$gli2xOXE1bs4FFz3nbtY5.GRU0ujqA/3MDfsqQxMSQZA43ss7478O', '156790', NULL, '2025-08-31 05:03:06', '2025-08-30 21:02:12', '../assets/svg/account-avatar-default.svg', NULL),
(24, 'Rogel', 'rogelnavarro74@gmail.com', '09984518951', NULL, '$2y$10$lYGsgKJHWSu6yBVkqnCiHuOZDtYi2VfbYFYaz7glEh/Es0N84UZN6', '169239', NULL, '2024-11-04 14:13:29', '2024-11-04 06:12:36', NULL, NULL);

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
(3, 6, 1, '2024-11-21 15:03:23'),
(24, 6, 1, '2024-11-04 06:14:19'),
(19, 5, 1, '2024-09-16 01:44:44'),
(19, 1, 1, '2024-09-16 01:44:44'),
(19, 4, 1, '2024-09-16 01:44:44'),
(19, 3, 1, '2024-09-16 01:44:44'),
(19, 6, 1, '2024-09-16 01:44:44'),
(19, 2, 1, '2024-09-16 01:44:44'),
(3, 7, 1, '2024-11-21 15:03:23'),
(24, 4, 0, '2024-11-04 06:14:19'),
(25, 6, 1, '2024-11-12 08:56:48'),
(25, 2, 1, '2024-11-12 08:56:48'),
(3, 8, 1, '2024-11-21 15:03:23'),
(27, 5, 1, '2025-08-30 21:05:01'),
(27, 1, 1, '2025-08-30 21:05:01'),
(27, 8, 1, '2025-08-30 21:05:01'),
(27, 4, 1, '2025-08-30 21:05:01'),
(27, 7, 1, '2025-08-30 21:05:01'),
(27, 3, 1, '2025-08-30 21:05:01'),
(27, 6, 1, '2025-08-30 21:05:01'),
(27, 2, 1, '2025-08-30 21:05:01');

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
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `weather_location`, `date_time_format`, `show_background`, `text_size`, `last_updated`) VALUES
(30, 24, 'New York', 'en-US', 1, 'normal', '2024-11-04 06:14:29'),
(27, 19, 'Goa, Camarines Sur', 'en-US', 1, 'normal', '2024-09-16 01:46:58'),
(23, 3, 'Lagonoy, Camarines Sur', 'en-US', 1, 'normal', '2024-12-16 02:04:57'),
(32, 27, 'Manila', 'en-US', 1, 'normal', '2025-08-30 22:26:00');

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
) ENGINE=MyISAM AUTO_INCREMENT=530 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_stock_symbols`
--

INSERT INTO `user_stock_symbols` (`id`, `user_id`, `stock_symbol`, `last_updated`) VALUES
(472, 22, 'TSLA', '2024-09-25 07:10:00'),
(511, 3, 'AAPL', '2024-11-04 01:58:41'),
(510, 3, 'GOOGL', '2024-11-04 01:58:41'),
(509, 3, 'TSLA', '2024-11-04 01:58:41'),
(418, 19, 'TSLA', '2024-09-16 01:46:58'),
(417, 19, 'GOOGL', '2024-09-16 01:46:58'),
(416, 19, 'AAPL', '2024-09-16 01:46:58'),
(517, 24, 'AAPL', '2024-11-04 06:17:37'),
(516, 24, 'GOOGL', '2024-11-04 06:17:37'),
(515, 24, 'TSLA', '2024-11-04 06:17:37'),
(529, 27, 'AAPL', '2025-08-30 22:26:00'),
(528, 27, 'GOOGL', '2025-08-30 22:26:00'),
(527, 27, 'TSLA', '2025-08-30 22:26:00');

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
