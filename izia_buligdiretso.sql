-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 23, 2026 at 02:03 AM
-- Server version: 11.8.5-MariaDB-log
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `izia_buligdiretso`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `target_type`, `target_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, 'Admin logged in', '127.0.0.1', '2026-03-03 03:20:06'),
(2, 2, 'login', NULL, NULL, 'User logged in', '192.168.1.10', '2026-03-03 03:20:06'),
(3, 2, 'report_submitted', 'emergency', 1, 'Emergency ER-A7ZX submitted (medical/critical)', '192.168.1.10', '2026-03-03 03:20:06'),
(4, 1, 'responder_assigned', 'emergency', 1, 'Responder Kim Taehyung assigned to ER-A7ZX', '127.0.0.1', '2026-03-03 03:20:06'),
(5, 9, 'report_submitted', 'emergency', 2, 'Emergency ER-B4FG submitted (fire/moderate)', '192.168.1.20', '2026-03-03 03:20:06'),
(6, 3, 'report_submitted', 'emergency', 3, 'Emergency ER-C9KL submitted (accident/critical)', '192.168.1.30', '2026-03-03 03:20:06'),
(7, 2, 'report_submitted', 'emergency', 4, 'Emergency ER-D2MN submitted (medical/low)', '192.168.1.10', '2026-03-03 03:20:06'),
(8, 1, 'emergency_resolved', 'emergency', 4, 'Emergency ER-D2MN marked as resolved', '127.0.0.1', '2026-03-03 03:20:06');

-- --------------------------------------------------------

--
-- Table structure for table `chart_datasets`
--

CREATE TABLE `chart_datasets` (
  `id` int(10) UNSIGNED NOT NULL,
  `chart_key` varchar(60) NOT NULL,
  `parent_chart` varchar(60) NOT NULL,
  `chart_name` varchar(120) NOT NULL,
  `dataset_label` varchar(80) NOT NULL,
  `chart_type` enum('bar','line','doughnut','pie') NOT NULL DEFAULT 'bar',
  `color` varchar(25) NOT NULL DEFAULT '#E74C3C',
  `display_order` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chart_datasets`
--

INSERT INTO `chart_datasets` (`id`, `chart_key`, `parent_chart`, `chart_name`, `dataset_label`, `chart_type`, `color`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'monthly_medical', 'monthly_volume', 'Monthly Emergency Volume', 'Medical', 'bar', 'rgba(231,76,60,0.82)', 0, '2026-04-21 02:06:49', '2026-04-21 02:06:49'),
(2, 'monthly_fire', 'monthly_volume', 'Monthly Emergency Volume', 'Fire', 'bar', 'rgba(243,156,18,0.82)', 1, '2026-04-21 02:06:49', '2026-04-21 02:06:49'),
(3, 'monthly_accident', 'monthly_volume', 'Monthly Emergency Volume', 'Accident', 'bar', 'rgba(52,152,219,0.82)', 2, '2026-04-21 02:06:49', '2026-04-21 02:06:49'),
(4, 'type_dist', 'type_distribution', 'Type Distribution', 'Distribution', 'doughnut', '#E74C3C', 0, '2026-04-21 02:06:49', '2026-04-21 02:06:49'),
(5, 'response_time', 'response_time_trend', 'Response Time Trend', 'Avg Time (min)', 'line', '#E74C3C', 0, '2026-04-21 02:06:49', '2026-04-21 02:06:49'),
(6, 'status_breakdown', 'status_breakdown', 'Status Breakdown', 'Count', 'bar', '#27AE60', 0, '2026-04-21 02:06:49', '2026-04-21 02:06:49'),
(7, 'peak_hours', 'peak_hours', 'Peak Hours', 'Emergencies', 'bar', 'rgba(231,76,60,0.7)', 0, '2026-04-21 02:06:49', '2026-04-21 02:06:49');

-- --------------------------------------------------------

--
-- Table structure for table `chart_data_points`
--

CREATE TABLE `chart_data_points` (
  `id` int(10) UNSIGNED NOT NULL,
  `dataset_id` int(10) UNSIGNED NOT NULL,
  `label` varchar(80) NOT NULL,
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `point_color` varchar(25) DEFAULT NULL,
  `display_order` tinyint(4) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chart_data_points`
--

INSERT INTO `chart_data_points` (`id`, `dataset_id`, `label`, `value`, `point_color`, `display_order`, `updated_at`) VALUES
(1, 1, 'Jan', 18.00, NULL, 0, '2026-04-21 02:07:37'),
(2, 1, 'Feb', 22.00, NULL, 1, '2026-04-21 02:07:37'),
(3, 1, 'Mar', 19.00, NULL, 2, '2026-04-21 02:07:37'),
(4, 1, 'Apr', 25.00, NULL, 3, '2026-04-21 02:07:37'),
(5, 1, 'May', 30.00, NULL, 4, '2026-04-21 02:07:37'),
(6, 1, 'Jun', 28.00, NULL, 5, '2026-04-21 02:07:37'),
(7, 1, 'Jul', 35.00, NULL, 6, '2026-04-21 02:07:37'),
(8, 1, 'Aug', 32.00, NULL, 7, '2026-04-21 02:07:37'),
(9, 1, 'Sep', 28.00, NULL, 8, '2026-04-21 02:07:37'),
(10, 1, 'Oct', 24.00, NULL, 9, '2026-04-21 02:07:37'),
(11, 1, 'Nov', 20.00, NULL, 10, '2026-04-21 02:07:37'),
(12, 1, 'Dec', 18.00, NULL, 11, '2026-04-21 02:07:37'),
(13, 2, 'Jan', 8.00, NULL, 0, '2026-04-21 02:07:37'),
(14, 2, 'Feb', 10.00, NULL, 1, '2026-04-21 02:07:37'),
(15, 2, 'Mar', 12.00, NULL, 2, '2026-04-21 02:07:37'),
(16, 2, 'Apr', 9.00, NULL, 3, '2026-04-21 02:07:37'),
(17, 2, 'May', 14.00, NULL, 4, '2026-04-21 02:07:37'),
(18, 2, 'Jun', 18.00, NULL, 5, '2026-04-21 02:07:37'),
(19, 2, 'Jul', 20.00, NULL, 6, '2026-04-21 02:07:37'),
(20, 2, 'Aug', 15.00, NULL, 7, '2026-04-21 02:07:37'),
(21, 2, 'Sep', 11.00, NULL, 8, '2026-04-21 02:07:37'),
(22, 2, 'Oct', 9.00, NULL, 9, '2026-04-21 02:07:37'),
(23, 2, 'Nov', 7.00, NULL, 10, '2026-04-21 02:07:37'),
(24, 2, 'Dec', 6.00, NULL, 11, '2026-04-21 02:07:37'),
(25, 3, 'Jan', 5.00, NULL, 0, '2026-04-21 02:07:37'),
(26, 3, 'Feb', 7.00, NULL, 1, '2026-04-21 02:07:37'),
(27, 3, 'Mar', 6.00, NULL, 2, '2026-04-21 02:07:37'),
(28, 3, 'Apr', 8.00, NULL, 3, '2026-04-21 02:07:37'),
(29, 3, 'May', 9.00, NULL, 4, '2026-04-21 02:07:37'),
(30, 3, 'Jun', 11.00, NULL, 5, '2026-04-21 02:07:37'),
(31, 3, 'Jul', 13.00, NULL, 6, '2026-04-21 02:07:37'),
(32, 3, 'Aug', 10.00, NULL, 7, '2026-04-21 02:07:37'),
(33, 3, 'Sep', 8.00, NULL, 8, '2026-04-21 02:07:37'),
(34, 3, 'Oct', 7.00, NULL, 9, '2026-04-21 02:07:37'),
(35, 3, 'Nov', 6.00, NULL, 10, '2026-04-21 02:07:37'),
(36, 3, 'Dec', 5.00, NULL, 11, '2026-04-21 02:07:37');

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `full_name` varchar(160) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_submissions`
--

INSERT INTO `contact_submissions` (`id`, `user_id`, `full_name`, `email`, `phone`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'Juan Dela Cruz', 'user@gmail.com', '09123456789', 'Question about response times', 'I submitted an emergency report but I am unsure how to track it. Can you help?', 0, '2026-03-03 03:20:06');

-- --------------------------------------------------------

--
-- Table structure for table `emergencies`
--

CREATE TABLE `emergencies` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_code` varchar(20) NOT NULL,
  `reporter_id` int(10) UNSIGNED NOT NULL,
  `emergency_type` enum('medical','accident','animal','disaster','fire','other') NOT NULL,
  `priority_level` enum('critical','high','moderate','low') NOT NULL DEFAULT 'moderate',
  `description` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `location_text` varchar(255) NOT NULL DEFAULT 'Unknown',
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','dispatched','en_route','on_scene','resolved','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `emergencies`
--

INSERT INTO `emergencies` (`id`, `reference_code`, `reporter_id`, `emergency_type`, `priority_level`, `description`, `latitude`, `longitude`, `location_text`, `photo_path`, `status`, `created_at`, `updated_at`, `resolved_at`) VALUES
(1, 'ER-A7ZX', 2, 'medical', 'critical', 'Chest pain and difficulty breathing. Patient is conscious but in severe pain.', 10.2074000, 122.9771000, 'Brgy. Balud, Isabela, Negros Occ.', NULL, 'dispatched', '2024-02-08 13:45:00', '2026-03-03 03:20:06', NULL),
(2, 'ER-B4FG', 9, 'fire', 'moderate', 'Kitchen fire. Smoke detected. Residents are evacuating.', 10.2090000, 122.9810000, 'Brgy. Puso, Isabela, Negros Occ.', NULL, 'en_route', '2024-02-08 13:30:00', '2026-03-03 03:20:06', NULL),
(3, 'ER-C9KL', 3, 'accident', 'critical', 'Car accident. Multiple injuries. Road is blocked — police assist required.', 10.2055000, 122.9750000, 'Isabela Town Proper, Negros Occ.', NULL, 'on_scene', '2024-02-08 13:20:00', '2026-03-03 03:20:06', NULL),
(4, 'ER-D2MN', 2, 'medical', 'low', 'Minor wound from a fall. Patient is stable.', 10.2080000, 122.9800000, 'Brgy. Cabcab, Isabela, Negros Occ.', NULL, 'resolved', '2024-02-08 13:10:00', '2026-03-03 03:20:06', '2024-02-08 14:00:00'),
(5, 'ER-E5PQ', 9, 'disaster', 'high', 'Flash flood threatening residential area.', 10.2100000, 122.9830000, 'Brgy. Quintin Remo, Isabela, Neg.', NULL, 'pending', '2024-02-08 14:00:00', '2026-03-03 03:20:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `emergency_assignments`
--

CREATE TABLE `emergency_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `emergency_id` int(10) UNSIGNED NOT NULL,
  `responder_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `emergency_assignments`
--

INSERT INTO `emergency_assignments` (`id`, `emergency_id`, `responder_id`, `assigned_at`, `completed_at`, `rating`, `notes`) VALUES
(1, 1, 3, '2024-02-08 13:47:00', NULL, NULL, 'Kim dispatched to ER-A7ZX'),
(2, 2, 4, '2024-02-08 13:32:00', NULL, NULL, 'Janelle en route to ER-B4FG'),
(3, 3, 5, '2024-02-08 13:22:00', NULL, NULL, 'Jungkook on scene at ER-C9KL'),
(4, 4, 1, '2024-02-08 13:12:00', '2024-02-08 14:00:00', 5, 'Resolved minor wound — patient sent home');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `display_order`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'How do I report an emergency?', 'Log in to BuligDiretso, go to \"Report\", choose the emergency type and priority, then submit. Our team will be notified immediately.', 1, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(2, 'What areas does BuligDiretso cover?', 'BuligDiretso currently covers the municipality of Isabela, Negros Occidental and its surrounding barangays.', 2, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(3, 'How long does a responder take to arrive?', 'Average response time is 5-15 minutes depending on location and responder availability.', 3, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(4, 'Can I track my emergency report?', 'Yes. Go to \"Tracking\" in the app to see real-time status updates for your submitted emergency report.', 4, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(5, 'Who are the responders?', 'Responders are trained volunteers and official personnel coordinated with LDRRMO, PNP, BFP, and MHO of Isabela.', 5, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(6, 'What should I do while waiting for help?', 'Stay calm, keep the affected person still, follow any guidance from the emergency coordinator, and do not leave the scene.', 6, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06');

-- --------------------------------------------------------

--
-- Table structure for table `responders`
--

CREATE TABLE `responders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','responding','offline') NOT NULL DEFAULT 'offline',
  `current_location` varchar(255) DEFAULT NULL,
  `total_responses` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `avg_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `rating_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `responders`
--

INSERT INTO `responders` (`id`, `user_id`, `status`, `current_location`, `total_responses`, `avg_rating`, `rating_count`, `created_at`, `updated_at`) VALUES
(1, 4, 'active', 'Brgy. Balud, Isabela', 82, 4.50, 82, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(2, 5, 'responding', 'Brgy. Cabcab, Isabela', 65, 4.20, 65, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(3, 6, 'active', 'Isabela Town Proper', 90, 4.80, 90, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(4, 7, 'responding', 'Brgy. Puso, Isabela', 55, 4.30, 55, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(5, 8, 'offline', 'Brgy. Quintin Remo, Isabela', 70, 4.60, 70, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(6, 16, 'offline', NULL, 0, 0.00, 0, '2026-03-24 01:03:06', '2026-03-24 01:03:06');

-- --------------------------------------------------------

--
-- Table structure for table `safety_guides`
--

CREATE TABLE `safety_guides` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `category` enum('medical','disaster','other') NOT NULL DEFAULT 'other',
  `icon_class` varchar(80) DEFAULT NULL,
  `read_time` varchar(20) DEFAULT NULL,
  `content` longtext NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `safety_guides`
--

INSERT INTO `safety_guides` (`id`, `slug`, `title`, `category`, `icon_class`, `read_time`, `content`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'cpr-instructions', 'CPR Instructions', 'medical', 'ri-heart-pulse-line', '5 min read', 'CPR (Cardiopulmonary Resuscitation) steps:\n1. Call for emergency help immediately.\n2. Place heel of hand on the center of the chest.\n3. Push down hard and fast — at least 2 inches deep, 100-120 compressions per minute.\n4. After 30 compressions, give 2 rescue breaths if trained.\n5. Continue until help arrives or person recovers.', 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(2, 'treating-burns', 'Treating Burns', 'medical', 'ri-fire-line', '3 min read', 'For minor burns:\n1. Cool the burn under cool (not cold) running water for 10-20 minutes.\n2. Do NOT use ice, butter, or toothpaste.\n3. Cover with a sterile non-stick bandage.\n4. Take a mild painkiller if needed.\n5. Seek medical help for large or severe burns.', 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(3, 'snake-bite-response', 'Snake Bite Response', 'medical', 'ri-alert-line', '4 min read', 'Snake bite first aid:\n1. Keep the victim calm and still.\n2. Remove rings or tight clothing near the bite.\n3. Keep the bitten limb below heart level.\n4. Do NOT cut the wound or suck out venom.\n5. Call emergency services immediately and note the snake appearance.', 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(4, 'earthquake-safety', 'Earthquake Safety', 'disaster', 'ri-earth-line', '6 min read', 'During an earthquake:\n1. DROP, COVER, and HOLD ON.\n2. Stay away from windows and falling objects.\n3. If outdoors, move away from buildings and power lines.\n4. After shaking stops, check for injuries and hazards.\n5. Be prepared for aftershocks.', 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(5, 'choking-relief', 'Choking Relief', 'medical', 'ri-lungs-line', '2 min read', 'Heimlich maneuver for choking adults:\n1. Stand behind the person and place one foot forward.\n2. Make a fist and place it just above the navel.\n3. Grasp your fist with the other hand.\n4. Give quick upward thrusts until the object is expelled.\n5. Call emergency services if the person loses consciousness.', 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(6, 'flood-evacuation', 'Flood Evacuation', 'disaster', 'ri-flood-line', '5 min read', 'Flood evacuation tips:\n1. Move immediately to higher ground.\n2. Do NOT walk or drive through floodwater.\n3. Take emergency kit: water, food, medicine, documents.\n4. Follow official evacuation routes.\n5. Stay informed via local emergency broadcasts.', 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `role` enum('admin','pwd','responder') NOT NULL DEFAULT 'pwd',
  `password_hash` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `address`, `role`, `password_hash`, `profile_photo`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@gmail.com', '09000000001', '1990-01-01', 'Isabela, Negros Occidental', 'admin', '$2y$12$KIXzH3A0zVm1gRMRPuFIaOjvBJp5jFr3GWVVSnMBhbPxwPdJV4nOK', NULL, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(2, 'Juan', 'Dela Cruz', 'user@gmail.com', '09123456789', '1995-06-15', '123 Main St, Isabela, Negros Occ.', '', '$2y$12$ByTGxHSl4E9R7kV2Yv5eCeEW7zDdp0i4yCaWPsUmFqLg5HUOkB.iW', NULL, 1, '2026-03-03 03:20:06', '2026-04-21 02:08:17'),
(3, 'Maria', 'Santos', 'maria.santos@email.com', '09987654321', '1993-03-22', 'Brgy. Balud, Isabela, Negros Occ.', '', '$2y$12$ByTGxHSl4E9R7kV2Yv5eCeEW7zDdp0i4yCaWPsUmFqLg5HUOkB.iW', NULL, 1, '2026-03-03 03:20:06', '2026-04-21 02:08:17'),
(4, 'John', 'Santoso', 'john.santoso@email.com', '09171112233', '1988-09-10', 'Brgy. Balud, Isabela, Negros Occ.', 'responder', '$2y$12$QpLvLMz6Nk8r2jT5qXbB7.HZ4cXkFDp5G3sOT7MkiK6Dm9Z3REqsO', NULL, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(5, 'Mario', 'Reyes', 'mario.reyes@email.com', '09229998877', '1985-12-05', 'Brgy. Cabcab, Isabela, Negros Occ.', 'responder', '$2y$12$QpLvLMz6Nk8r2jT5qXbB7.HZ4cXkFDp5G3sOT7MkiK6Dm9Z3REqsO', NULL, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(6, 'Kim', 'Taehyung', 'kim.taehyung@email.com', '09331234567', '1997-02-14', 'Isabela, Negros Occidental', 'responder', '$2y$12$QpLvLMz6Nk8r2jT5qXbB7.HZ4cXkFDp5G3sOT7MkiK6Dm9Z3REqsO', NULL, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(7, 'Janelle', 'Ba-al', 'janelle.baal@email.com', '09441234567', '1996-07-21', 'Brgy. Puso, Isabela, Negros Occ.', 'responder', '$2y$12$QpLvLMz6Nk8r2jT5qXbB7.HZ4cXkFDp5G3sOT7MkiK6Dm9Z3REqsO', NULL, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(8, 'Jeon', 'Jungkook', 'jeon.jungkook@email.com', '09551234567', '1997-09-01', 'Brgy. Quintin Remo, Isabela, Neg.', 'responder', '$2y$12$QpLvLMz6Nk8r2jT5qXbB7.HZ4cXkFDp5G3sOT7MkiK6Dm9Z3REqsO', NULL, 1, '2026-03-03 03:20:06', '2026-03-03 03:20:06'),
(9, 'Carlos', 'Mendoza', 'carlos.mendoza@email.com', '09661112233', '1992-04-30', '123 Main St, Isabela, Negros Occ.', '', '$2y$12$ByTGxHSl4E9R7kV2Yv5eCeEW7zDdp0i4yCaWPsUmFqLg5HUOkB.iW', NULL, 1, '2026-03-03 03:20:06', '2026-04-21 02:08:17'),
(10, 'Aizhelle', 'de la Cruz', 'aizhellegwynneth@gmail.com', '09949771317', '2005-08-23', 'Victorias City', '', '$2y$12$cTI7kszahdMfkWcTVBmtwu/nV4Zj0u1iHBo07g79jfUmD5u1wzyLu', NULL, 1, '2026-03-04 06:06:25', '2026-04-21 02:08:17'),
(12, 'JANELLE', 'BA-AL', 's240306@usls.edu.ph', '09123456789', '2005-12-14', 'Bacolod', '', '$2y$12$66AASDeDXaw2BJaSg4W5oOJ3vPG6D8we48rRzBqS/bdEnQfOa4dBy', NULL, 1, '2026-03-04 06:14:56', '2026-04-21 02:08:17'),
(15, 'Jesse', 'Pinkman', 'jesse@gmail.com', '09451961452', '2000-12-14', 'Zone 5 Barangay Handumanan Bacolod City', '', '$2y$12$GxsKYVtcXhOxk/jcJ.YF.OostggWuINg0f1XBMFbg9xqjwheNouou', NULL, 1, '2026-03-19 02:26:45', '2026-04-21 02:08:17'),
(16, 'Anne', 'Ynzon', 'anneynzon@gmail.com', '09612045422', '2004-11-02', 'Smith Street, Brgy. 2 (POB), Isabela, Negros Occidental', 'responder', '$2y$12$dcR1aGeHaftJeBJ0C/Np7uU3lIrAsDbJAiQ1luLbaHcE.jPseFNJS', NULL, 1, '2026-03-24 01:03:06', '2026-03-24 01:03:06'),
(17, 'Solistine', 'Samillano', 'sol@gmail.com', '09951674714', '2005-12-08', 'Isabela, Negros Occidental', 'admin', '$2y$12$0nZ3aqIL9rDQ3VWu4E1JzuIxAUp0fL0Sv/.vJH5lZExKtKRhKEcHm', NULL, 1, '2026-03-24 02:41:00', '2026-03-24 02:41:00'),
(19, 'Jeon', 'Jungkook', 'jeon@gmail.com', '09106301375', '1997-09-01', 'Soul Korea', '', '$2y$12$m37T7Arsg3n82TXgtQZXc.AVNEcqy3a/SNMBDE4hDyrYwm3Yy6U.W', NULL, 1, '2026-03-29 01:39:24', '2026-04-21 02:08:17'),
(20, 'Anne', 'Sanz', 'janelle14@gmail.com', '09636182369', '2004-12-02', 'Smith Street, Brgy. 2 (POB), Isabela, Negros Occidental', '', '$2y$12$HGY60BMPGZC/v9tfOoBphet2ZSaeV1w5z9ehteR/gpuCn7yqTeXy2', NULL, 1, '2026-04-07 02:14:49', '2026-04-21 02:08:17'),
(21, 'Kokie', 'Tan', 'kim@gmail.com', '09514725871', '1997-09-01', 'Zone 1 Barangay Handumanan Bacolod City', '', '$2y$12$aY/zWiVJz8zh6Tobzugyi.J4eF4/DEzLcvbNK421.972pbshcsAey', NULL, 1, '2026-04-13 03:12:56', '2026-04-21 02:08:17'),
(22, 'Alon', 'Puertas', 'lowkeychill04@gmail.com', '09914734213', '2004-12-12', 'Smith Street, Brgy. 2 (POB), Isabela, Negros Occidental', 'admin', '$2y$12$4giL/U7eGPp4.obYvV11De0tgqISXqYE1hiB6FktFX8zoVhydB5vy', NULL, 1, '2026-04-14 03:16:08', '2026-04-14 03:16:08'),
(23, 'Sol', 'Samillano', 'samillanomarisol@gmail.com', '09106301375', '2005-12-08', 'Brgy. Bulad, Isabela, Negros Occidental', '', '$2y$12$5R4/aUintZIRGtZWdtKYVeCPdkqzYasyccHiJlAYDFGkBkMuG7Wym', NULL, 1, '2026-04-14 03:21:04', '2026-04-21 02:08:17'),
(24, 'Angel', 'Yasay', 'angel@gmail.com', '09246810121', '2025-08-06', 'Baranga Banago', 'admin', '$2y$12$dF6K80.2QCMPYR9iPzG6S.G5vdPJ0Tu/KZsNDfe96H16z1PdG.kLq', NULL, 1, '2026-04-20 13:38:26', '2026-04-20 13:38:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_user` (`user_id`),
  ADD KEY `idx_log_action` (`action`);

--
-- Indexes for table `chart_datasets`
--
ALTER TABLE `chart_datasets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chart_key` (`chart_key`),
  ADD KEY `idx_cd_parent` (`parent_chart`);

--
-- Indexes for table `chart_data_points`
--
ALTER TABLE `chart_data_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cdp_dataset` (`dataset_id`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_user` (`user_id`);

--
-- Indexes for table `emergencies`
--
ALTER TABLE `emergencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_emergencies_refcode` (`reference_code`),
  ADD KEY `idx_emergencies_reporter` (`reporter_id`),
  ADD KEY `idx_emergencies_status` (`status`),
  ADD KEY `idx_emergencies_priority` (`priority_level`);

--
-- Indexes for table `emergency_assignments`
--
ALTER TABLE `emergency_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ea_emergency` (`emergency_id`),
  ADD KEY `idx_ea_responder` (`responder_id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `responders`
--
ALTER TABLE `responders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_responders_user` (`user_id`);

--
-- Indexes for table `safety_guides`
--
ALTER TABLE `safety_guides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_guides_slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `chart_datasets`
--
ALTER TABLE `chart_datasets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chart_data_points`
--
ALTER TABLE `chart_data_points`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `emergencies`
--
ALTER TABLE `emergencies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `emergency_assignments`
--
ALTER TABLE `emergency_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `responders`
--
ALTER TABLE `responders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `safety_guides`
--
ALTER TABLE `safety_guides`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `chart_data_points`
--
ALTER TABLE `chart_data_points`
  ADD CONSTRAINT `fk_cdp_dataset` FOREIGN KEY (`dataset_id`) REFERENCES `chart_datasets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD CONSTRAINT `fk_contact_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `emergencies`
--
ALTER TABLE `emergencies`
  ADD CONSTRAINT `fk_emergencies_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `emergency_assignments`
--
ALTER TABLE `emergency_assignments`
  ADD CONSTRAINT `fk_ea_emergency` FOREIGN KEY (`emergency_id`) REFERENCES `emergencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ea_responder` FOREIGN KEY (`responder_id`) REFERENCES `responders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `responders`
--
ALTER TABLE `responders`
  ADD CONSTRAINT `fk_responders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
