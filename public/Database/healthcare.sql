-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 20, 2025 at 11:21 AM
-- Server version: 9.1.0
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `healthcare`
--

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

DROP TABLE IF EXISTS `batches`;
CREATE TABLE IF NOT EXISTS `batches` (
  `batch_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `batch_code` varchar(50) NOT NULL,
  `quantity` int NOT NULL,
  `remaining_quantity` int NOT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `received_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `location` enum('main','reserve') DEFAULT 'reserve',
  PRIMARY KEY (`batch_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`batch_id`, `item_id`, `batch_code`, `quantity`, `remaining_quantity`, `manufacture_date`, `expiry_date`, `received_date`, `location`) VALUES
(3, 2, '1232', 10, 0, NULL, '2025-12-04', '2025-11-29 00:00:00', 'main');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `batch_id` int NOT NULL,
  `item_id` int NOT NULL,
  `transaction_type` enum('received','transferred','shipped_out','refilled') NOT NULL,
  `quantity` int NOT NULL,
  `transaction_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `remarks` text,
  PRIMARY KEY (`transaction_id`),
  KEY `batch_id` (`batch_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `item_description` text,
  `category` enum('Medicine','Equipment','Supply','Other') DEFAULT 'Medicine',
  `unit` varchar(20) DEFAULT 'pcs',
  `critical_level` int DEFAULT '10',
  `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Ongoing','Pending','Ended','Cancelled','Expired') DEFAULT 'Ongoing',
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `item_description`, `category`, `unit`, `critical_level`, `date_added`, `status`) VALUES
(2, 'Biogesic', 'Cold/Fever Medicine', 'Medicine', 'pcs', 10, '2025-11-13 10:45:01', 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `age` int NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `disease` varchar(150) DEFAULT NULL,
  `type` enum('Check-up','Home Visit','Prescription','Follow-up') NOT NULL,
  `medicine` varchar(150) DEFAULT NULL,
  `schedule` date DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `status` enum('Ongoing','Pending','Ended','Cancelled') NOT NULL DEFAULT 'Pending',
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refills`
--

DROP TABLE IF EXISTS `refills`;
CREATE TABLE IF NOT EXISTS `refills` (
  `refill_id` int NOT NULL AUTO_INCREMENT,
  `from_batch_id` int NOT NULL,
  `to_batch_id` int NOT NULL,
  `quantity` int NOT NULL,
  `refill_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `remarks` text,
  PRIMARY KEY (`refill_id`),
  KEY `from_batch_id` (`from_batch_id`),
  KEY `to_batch_id` (`to_batch_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_data` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci,
  `timestamp` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `session_unique_id` (`id`),
  KEY `idx_user_browser_sessiondata` (`user_id`,`browser`,`session_data`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `age` int NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email_token` varchar(100) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL DEFAULT 'changeme',
  `remember_token` varchar(100) DEFAULT NULL,
  `google_oauth_id` varchar(255) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `age`, `email`, `address`, `email_token`, `email_verified_at`, `password`, `remember_token`, `google_oauth_id`, `role`, `created_at`, `updated_at`) VALUES
(2, 'Rey', 'Cabral', 21, 'jhonreycabral@gmail.com', 'Masipit', NULL, NULL, '$2y$10$1QPJARq0liG.x5V0Bg9jUuAC8igW9NVGcIsp9WAynGnhvXU9Kg02y', NULL, NULL, 'admin', '2025-11-12 18:38:15', '2025-11-12 18:38:15'),
(8, 'cj', 'crisporo', 65, 'kaitonero11@gmail.com', 'Masipit', NULL, NULL, '$2y$10$ydQ3Oaa4Ty89L3rePJvuHubV4ZXRY.saP3Y/mWbBGghCGLoRidkyy', NULL, NULL, 'user', '2025-11-20 02:22:45', '2025-11-20 02:22:45');

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

DROP TABLE IF EXISTS `verification_codes`;
CREATE TABLE IF NOT EXISTS `verification_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `purpose` (`purpose`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
