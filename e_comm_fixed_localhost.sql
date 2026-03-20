-- Localhost-ready SQL for e_comm
-- Based on original dump plus missing bootstrap and profile migration safeguards

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `e_comm`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `e_comm`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `donations`;
DROP TABLE IF EXISTS `volunteers`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `phone`, `address`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@charity.com', '$2y$10$ZC/Syr9lMlvK0bA1UWqTke4EktfYC7jnvMQt9ei7TIJ8U6HKEgUn6', 'admin', NULL, NULL, NULL, '2026-03-08 10:47:55', '2026-03-08 10:47:55'),
(2, 'PAOLO NAVA', 'paolonava24@gmail.com', '$2y$10$cIkShTrDFTkQ4ObW934JIOhXQsAp7QxQaOE3RBiTPbTq5nDzKbdEO', 'user', NULL, NULL, NULL, '2026-03-08 11:58:36', '2026-03-08 11:58:36');

CREATE TABLE `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `donor_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `anonymous` tinyint(1) DEFAULT 0,
  `recurring` tinyint(1) DEFAULT 0,
  `message` text DEFAULT NULL,
  `status` enum('pending','verified','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `proof_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `donations` (`id`, `user_id`, `donor_name`, `email`, `phone`, `amount`, `payment_method`, `reference_number`, `anonymous`, `recurring`, `message`, `status`, `created_at`, `proof_image`) VALUES
(1, 2, 'PAOLO NAVA', 'paolonava24@gmail.com', '09776043880', 100.00, 'QR Payment', '3038557604252', 1, 0, '', 'verified', '2026-03-08 13:58:42', NULL);

CREATE TABLE `volunteers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `availability` varchar(100) NOT NULL,
  `skills` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `volunteers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Safety migration section in case the application expects profile fields.
-- These are idempotent on MariaDB/MySQL versions that support IF NOT EXISTS.
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `phone` varchar(30) DEFAULT NULL AFTER `role`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `address` text DEFAULT NULL AFTER `phone`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `profile_image` varchar(255) DEFAULT NULL AFTER `address`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
