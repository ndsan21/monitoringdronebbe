-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 28, 2026 at 03:39 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbdm`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` bigint UNSIGNED NOT NULL,
  `asset_id` varchar(255) NOT NULL,
  `serial_number` varchar(255) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `category` enum('DRONE','SPAREPART') NOT NULL,
  `sparepart_type` varchar(255) DEFAULT NULL,
  `drone_id` bigint UNSIGNED DEFAULT NULL,
  `entry_date` date NOT NULL,
  `status` enum('ready','in_use','on_repaired','out_of_service') NOT NULL DEFAULT 'ready',
  `owner_company_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `received_date` date NOT NULL,
  `received_by` varchar(255) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subscription_group_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_id`, `serial_number`, `asset_name`, `category`, `sparepart_type`, `drone_id`, `entry_date`, `status`, `owner_company_id`, `department_id`, `received_date`, `received_by`, `photo_path`, `created_at`, `updated_at`, `subscription_group_id`) VALUES
(1, '123', '2w223', 'DROMERRR', 'DRONE', NULL, NULL, '2026-05-28', 'ready', 2, 1, '2026-05-28', 'putri', NULL, '2026-05-28 15:26:46', '2026-05-28 15:26:46', NULL),
(2, '232', '1134221', 'batre', 'SPAREPART', 'Battery', 1, '2026-05-28', 'out_of_service', 3, 1, '2026-05-26', 'SS', NULL, '2026-05-28 15:27:58', '2026-05-28 15:31:36', NULL),
(3, '9923', '2343078', 'remote1', 'SPAREPART', 'Remote', 1, '2026-05-28', 'ready', 3, 1, '2026-05-28', 'putri', NULL, '2026-05-28 15:28:23', '2026-05-28 15:28:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subscription_group_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `logo_path`, `created_at`, `updated_at`, `subscription_group_id`) VALUES
(1, 'PT. Drone Inovasi Master', NULL, '2026-05-28 13:39:20', '2026-05-28 13:39:20', NULL),
(2, 'PT. BUKIT BAIDURI ENERGI', 'company-logos/01KSQDGWP60CAPEY67556AMGZ4.png', '2026-05-28 13:48:59', '2026-05-28 13:48:59', 1),
(3, 'PT KHOTAI MAKMUR INSAN ABADI', 'company-logos/01KSQDHQJJNF17SP07Y9R0QPBZ.png', '2026-05-28 13:49:26', '2026-05-28 13:49:26', 1);

-- --------------------------------------------------------

--
-- Table structure for table `damage_reports`
--

CREATE TABLE `damage_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `asset_id` bigint UNSIGNED NOT NULL,
  `reported_by_id` bigint UNSIGNED NOT NULL,
  `report_date` date NOT NULL,
  `damage_severity` enum('minor','moderate','major') NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time NOT NULL,
  `incident_location_name` varchar(255) NOT NULL,
  `incident_location_id` bigint UNSIGNED DEFAULT NULL,
  `chronology` text NOT NULL,
  `current_status` enum('reported','on_progress','resolved') NOT NULL DEFAULT 'reported',
  `condition_status` enum('good','damaged_replace','out_of_service') NOT NULL,
  `note` text,
  `evidences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `damage_reports`
--

INSERT INTO `damage_reports` (`id`, `asset_id`, `reported_by_id`, `report_date`, `damage_severity`, `incident_date`, `incident_time`, `incident_location_name`, `incident_location_id`, `chronology`, `current_status`, `condition_status`, `note`, `evidences`, `created_at`, `updated_at`) VALUES
(1, 2, 2, '2026-05-28', 'moderate', '2026-05-28', '23:31:00', 'Workshop / Maintenance Site Inspection', NULL, 'Terdeteksi otomatis rusak pada saat dilakukan Maintenance dengan tipe \'full_maintenance\' pada tanggal 28-05-2026.', 'reported', 'out_of_service', 'Auto-generated pipeline dari Maintenance Log nomor #1', NULL, '2026-05-28 15:31:36', '2026-05-28 15:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Surveyor Core', '2026-05-28 13:39:20', '2026-05-28 13:39:20');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flight_locations`
--

CREATE TABLE `flight_locations` (
  `id` bigint UNSIGNED NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `iup_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `flight_locations`
--

INSERT INTO `flight_locations` (`id`, `location_name`, `iup_number`, `created_at`, `updated_at`) VALUES
(1, 'merandai', NULL, '2026-05-28 15:24:24', '2026-05-28 15:24:24'),
(2, 'ks tubun', NULL, '2026-05-28 15:28:58', '2026-05-28 15:28:58');

-- --------------------------------------------------------

--
-- Table structure for table `flight_logs`
--

CREATE TABLE `flight_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `drone_id` bigint UNSIGNED NOT NULL,
  `pilot_id` bigint UNSIGNED NOT NULL,
  `co_pilot_id` bigint UNSIGNED DEFAULT NULL,
  `requester_id` bigint UNSIGNED DEFAULT NULL,
  `authorized_by_id` bigint UNSIGNED DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `flight_mode` enum('auto','tc','pn','sa') NOT NULL,
  `flight_area_name` varchar(255) NOT NULL,
  `flight_location_id` bigint UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `takeoff_time` datetime DEFAULT NULL,
  `landing_time` datetime DEFAULT NULL,
  `duration` int DEFAULT '0',
  `takeoff_lat` decimal(10,7) DEFAULT NULL,
  `takeoff_lng` decimal(10,7) DEFAULT NULL,
  `address_detail` varchar(255) DEFAULT NULL,
  `result` enum('safe_to_fly','postpone','cancel') DEFAULT NULL,
  `note` text,
  `sky_condition` varchar(255) DEFAULT NULL,
  `wind_speed_kmh` decimal(6,2) DEFAULT NULL,
  `wind_direction` varchar(255) DEFAULT NULL,
  `humidity_percent` decimal(5,2) DEFAULT NULL,
  `temperature_c` decimal(5,2) DEFAULT NULL,
  `rain_prob` varchar(255) DEFAULT NULL,
  `visibility_km` decimal(5,2) DEFAULT NULL,
  `hardware_checklist` json DEFAULT NULL,
  `system_function_checklist` json DEFAULT NULL,
  `environment_checklist` json DEFAULT NULL,
  `safety_permit_checklist` json DEFAULT NULL,
  `flight_evidences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pre_drone_motors` tinyint(1) DEFAULT NULL,
  `pre_drone_propellers` tinyint(1) DEFAULT NULL,
  `pre_drone_airframe` tinyint(1) DEFAULT NULL,
  `pre_phone_battery_ok` tinyint(1) DEFAULT NULL,
  `rc_serial_id` varchar(255) DEFAULT NULL,
  `rc_battery_start` int DEFAULT NULL,
  `battery_serial_id` varchar(255) DEFAULT NULL,
  `drone_battery_start` int DEFAULT NULL,
  `battery_temp` decimal(5,2) DEFAULT NULL,
  `app_readiness` json DEFAULT NULL,
  `calibration` json DEFAULT NULL,
  `link_gps` json DEFAULT NULL,
  `rc_sticks_switches` json DEFAULT NULL,
  `media_gimbal` json DEFAULT NULL,
  `app_self_check` json DEFAULT NULL,
  `flight_test` json DEFAULT NULL,
  `low_cell_v` decimal(5,2) DEFAULT NULL,
  `high_cell_v` decimal(5,2) DEFAULT NULL,
  `total_voltage_v` decimal(5,2) DEFAULT NULL,
  `battery_cycles` int DEFAULT NULL,
  `visual_condition` json DEFAULT NULL,
  `visibility` json DEFAULT NULL,
  `ground_safety` json DEFAULT NULL,
  `wind_dir` varchar(255) DEFAULT NULL,
  `temp_c` decimal(5,2) DEFAULT NULL,
  `wind_speed` decimal(5,2) DEFAULT NULL,
  `humidity` decimal(5,2) DEFAULT NULL,
  `pilot_health` json DEFAULT NULL,
  `observer_health` json DEFAULT NULL,
  `clearance` json DEFAULT NULL,
  `notam` tinyint(1) DEFAULT '0',
  `notam_details` text,
  `is_motor_ok` tinyint(1) DEFAULT NULL,
  `is_propeller_ok` tinyint(1) DEFAULT NULL,
  `is_airframe_ok` tinyint(1) DEFAULT NULL,
  `rc_battery_finish` int DEFAULT NULL,
  `drone_battery_finish` int DEFAULT NULL,
  `requesting_company_id` bigint UNSIGNED DEFAULT NULL,
  `requesting_department_id` bigint UNSIGNED DEFAULT NULL,
  `pic_requester_name` varchar(255) DEFAULT NULL,
  `flight_operation_notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `flight_logs`
--

INSERT INTO `flight_logs` (`id`, `drone_id`, `pilot_id`, `co_pilot_id`, `requester_id`, `authorized_by_id`, `purpose`, `flight_mode`, `flight_area_name`, `flight_location_id`, `date`, `takeoff_time`, `landing_time`, `duration`, `takeoff_lat`, `takeoff_lng`, `address_detail`, `result`, `note`, `sky_condition`, `wind_speed_kmh`, `wind_direction`, `humidity_percent`, `temperature_c`, `rain_prob`, `visibility_km`, `hardware_checklist`, `system_function_checklist`, `environment_checklist`, `safety_permit_checklist`, `flight_evidences`, `created_at`, `updated_at`, `pre_drone_motors`, `pre_drone_propellers`, `pre_drone_airframe`, `pre_phone_battery_ok`, `rc_serial_id`, `rc_battery_start`, `battery_serial_id`, `drone_battery_start`, `battery_temp`, `app_readiness`, `calibration`, `link_gps`, `rc_sticks_switches`, `media_gimbal`, `app_self_check`, `flight_test`, `low_cell_v`, `high_cell_v`, `total_voltage_v`, `battery_cycles`, `visual_condition`, `visibility`, `ground_safety`, `wind_dir`, `temp_c`, `wind_speed`, `humidity`, `pilot_health`, `observer_health`, `clearance`, `notam`, `notam_details`, `is_motor_ok`, `is_propeller_ok`, `is_airframe_ok`, `rc_battery_finish`, `drone_battery_finish`, `requesting_company_id`, `requesting_department_id`, `pic_requester_name`, `flight_operation_notes`) VALUES
(1, 1, 2, 3, NULL, NULL, 'documentation', 'tc', '-', 2, '2026-05-28', '2026-05-28 23:29:57', '2026-05-28 12:29:59', 46802, -0.4906000, 117.1529000, 'Bandara, Sungai Pinang, Samarinda, Kalimantan Timur, Kalimantan, 75243, Indonesia', 'safe_to_fly', NULL, 'SCATTERED CLOUDS', NULL, NULL, NULL, NULL, '0 mm/h', NULL, NULL, NULL, NULL, NULL, '[]', '2026-05-28 15:30:16', '2026-05-28 15:30:16', 1, 1, 1, 1, '2343078', 65, '1134221', 23, 54.00, '[\"firmware_stable\"]', '[\"compass_ok\"]', '[\"rc_link_connected\"]', '[\"dials_ok\", \"sticks_ok\"]', '[\"microsd_inserted\"]', '[\"battery\"]', '[\"hovering_stable\"]', 45.00, 65.00, 67.00, 87, '[\"sunny\"]', '[\"clear\"]', '[\"flat_surface\", \"clear_airspace\", \"non_magnetic\", \"no_bird\"]', 'S (197°)', 24.13, 4.64, 97.00, '[\"ppe\", \"imsafe\"]', '[\"ppe\", \"imsafe\"]', '[\"supervisor\", \"owner\"]', 0, NULL, 0, 0, 0, NULL, NULL, 2, 1, 'nanad gemink', 'test 1');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_hardware_items`
--

CREATE TABLE `maintenance_hardware_items` (
  `id` bigint UNSIGNED NOT NULL,
  `maintenance_log_id` bigint UNSIGNED NOT NULL,
  `asset_id` bigint UNSIGNED NOT NULL,
  `current_status` enum('reported','on_progress','resolved') NOT NULL DEFAULT 'reported',
  `condition` enum('good','damaged_replace','out_of_service') NOT NULL,
  `note` text,
  `replaced_with_sparepart_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `maintenance_hardware_items`
--

INSERT INTO `maintenance_hardware_items` (`id`, `maintenance_log_id`, `asset_id`, `current_status`, `condition`, `note`, `replaced_with_sparepart_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'reported', 'out_of_service', NULL, NULL, '2026-05-28 15:31:36', '2026-05-28 15:31:36'),
(2, 1, 3, 'reported', 'good', NULL, NULL, '2026-05-28 15:31:36', '2026-05-28 15:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_logs`
--

CREATE TABLE `maintenance_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `asset_id` bigint UNSIGNED NOT NULL,
  `technician_id` bigint UNSIGNED NOT NULL,
  `maintenance_type` enum('hardware_inspection','software_update','full_maintenance') NOT NULL,
  `maintenance_date` date DEFAULT NULL,
  `maintenance_status` varchar(255) DEFAULT NULL,
  `software_app_checklist` json DEFAULT NULL,
  `sensors_calibration_checklist` json DEFAULT NULL,
  `technical_notes` text,
  `photos_evidence` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `maintenance_logs`
--

INSERT INTO `maintenance_logs` (`id`, `date`, `asset_id`, `technician_id`, `maintenance_type`, `maintenance_date`, `maintenance_status`, `software_app_checklist`, `sensors_calibration_checklist`, `technical_notes`, `photos_evidence`, `created_at`, `updated_at`) VALUES
(1, '2026-05-28', 1, 2, 'full_maintenance', '2026-05-28', 'completed', '[\"app_stable\", \"firmware_latest\"]', '[\"imu_ok\"]', 'test 1', '[]', '2026-05-28 15:31:36', '2026-05-28 15:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_16_073504_create_drone_monitoring_system_tables', 1),
(5, '2026_05_16_123329_add_checklist_columns_to_flight_logs_table', 1),
(6, '2026_05_17_192604_add_note_to_maintenance_hardware_items_table', 1),
(7, '2026_05_17_202956_create_site_settings_table', 1),
(8, '2026_05_28_211437_create_subscription_groups_table', 1),
(9, '2026_05_28_211438_add_subscription_group_to_tenants_table', 1),
(10, '2026_05_28_225541_add_pilot_and_company_fields_to_users_table', 2),
(11, '2026_05_28_231327_add_subscription_group_id_to_users_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('4Rq0LbZhwNjiP3Jb40jd25jpL1J6Dlpg6qKFPwY4', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiYmJ1R21zakJYTkw2MjNHUkxFZGFHZ1hhbU9PRWE3RVNwTndSWWZaRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbW9uaXRvcmluZ2Ryb25lYmJlLnRlc3QvYWRtaW4iO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkcDVsWHcxWTk0R3E3RWR0a2p1WEhWdUpwOVZCWmpBeGFkanVwaDBwcWhpc29MRFNjbXU4am0iO3M6ODoiZmlsYW1lbnQiO2E6MDp7fX0=', 1779982756),
('TZRB7gKizBdUNRqflcwaL5Xs0mOfPrpw0JUuSIHv', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiTm9Dam1kM2d5anRteVBhRm5meTU2MkV1anI3eUlKVWZGeEpvV1RZSSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjU1OiJodHRwOi8vbW9uaXRvcmluZ2Ryb25lYmJlLnRlc3Qvc3VwZXItYWRtaW4vdXNlcnMvY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJEZkc3M2UXJvM1AvVnlMbk1RZVlPamVhcm9rcC9wbzVaQ09JVVBIMW40c1JUWDRybkhOYUE2Ijt9', 1779981042),
('yqpdU6We9iZoG3IPS5US8x0PlnLbLMUSa7mDmfMY', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoia1dPSlVTT3VxdVM1eEdhSXpRTXNReXd4aWVsRXZtZTdPWG1FOXNGNSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwczovL21vbml0b3Jpbmdkcm9uZWJiZS50ZXN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJEZkc3M2UXJvM1AvVnlMbk1RZVlPamVhcm9rcC9wbzVaQ09JVVBIMW40c1JUWDRybkhOYUE2IjtzOjg6ImZpbGFtZW50IjthOjA6e319', 1779979429);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `site_name` varchar(255) NOT NULL DEFAULT 'Drone Monitoring BBE',
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_groups`
--

CREATE TABLE `subscription_groups` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `package_type` varchar(255) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subscription_groups`
--

INSERT INTO `subscription_groups` (`id`, `group_name`, `package_type`, `logo_path`, `created_at`, `updated_at`) VALUES
(1, 'BBE-KMIA', 'premium', 'subscription-logos/01KSQD0MHJKYJ2X0Q8QXHWCN76.gif', '2026-05-28 13:40:06', '2026-05-28 13:40:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `employee_id` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','pilot') NOT NULL DEFAULT 'pilot',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `license_number` varchar(255) DEFAULT NULL,
  `license_issued_by` varchar(255) DEFAULT NULL,
  `license_expiration_date` date DEFAULT NULL,
  `digital_signature` text,
  `is_approved` tinyint(1) NOT NULL DEFAULT '1',
  `subscription_group_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `full_name`, `employee_id`, `photo_path`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`, `company_id`, `department_id`, `license_number`, `license_issued_by`, `license_expiration_date`, `digital_signature`, `is_approved`, `subscription_group_id`) VALUES
(1, 'Super Admin Drone', 'Super Admin Drone', 'ADMIN001', NULL, 'admin@drone.com', NULL, '$2y$12$Fdss6Qro3P/VyLnMQeYOjearokp/po5ZCOIUPH1n4sRTX4rnHNaA6', 'super_admin', NULL, '2026-05-28 13:39:21', '2026-05-28 13:39:21', 1, 1, NULL, NULL, NULL, NULL, 1, NULL),
(2, 'NADIA SANDY', 'NADIA SANDY', NULL, NULL, 'nadia@gmail.com', NULL, '$2y$12$p5lXw1Y94Gq7EdtkjuXHVuJp9VBZjAxadjuph0pqhisoLDScmu8jm', 'admin', NULL, '2026-05-28 14:04:51', '2026-05-28 14:04:51', 2, NULL, NULL, NULL, NULL, NULL, 1, 1),
(3, 'sandy putra', 'sandy putra', '2343078', NULL, 'sandy@gmail.com', NULL, '$2y$12$OYKNrhcbrRpp3i9WBnB5vOLCnbXagzzTS09WdZzVE0Zv24ErIueTO', 'pilot', NULL, '2026-05-28 15:24:13', '2026-05-28 15:24:13', 2, 1, '213334', 'APDI', '2026-05-28', NULL, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assets_asset_id_unique` (`asset_id`),
  ADD UNIQUE KEY `assets_serial_number_unique` (`serial_number`),
  ADD KEY `assets_drone_id_foreign` (`drone_id`),
  ADD KEY `assets_owner_company_id_foreign` (`owner_company_id`),
  ADD KEY `assets_department_id_foreign` (`department_id`),
  ADD KEY `assets_subscription_group_id_foreign` (`subscription_group_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `companies_subscription_group_id_foreign` (`subscription_group_id`);

--
-- Indexes for table `damage_reports`
--
ALTER TABLE `damage_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `damage_reports_asset_id_foreign` (`asset_id`),
  ADD KEY `damage_reports_reported_by_id_foreign` (`reported_by_id`),
  ADD KEY `damage_reports_incident_location_id_foreign` (`incident_location_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `flight_locations`
--
ALTER TABLE `flight_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flight_logs`
--
ALTER TABLE `flight_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flight_logs_drone_id_foreign` (`drone_id`),
  ADD KEY `flight_logs_pilot_id_foreign` (`pilot_id`),
  ADD KEY `flight_logs_co_pilot_id_foreign` (`co_pilot_id`),
  ADD KEY `flight_logs_requester_id_foreign` (`requester_id`),
  ADD KEY `flight_logs_authorized_by_id_foreign` (`authorized_by_id`),
  ADD KEY `flight_logs_flight_location_id_foreign` (`flight_location_id`),
  ADD KEY `flight_logs_requesting_company_id_foreign` (`requesting_company_id`),
  ADD KEY `flight_logs_requesting_department_id_foreign` (`requesting_department_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_hardware_items`
--
ALTER TABLE `maintenance_hardware_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_hardware_items_maintenance_log_id_foreign` (`maintenance_log_id`),
  ADD KEY `maintenance_hardware_items_asset_id_foreign` (`asset_id`),
  ADD KEY `maintenance_hardware_items_replaced_with_sparepart_id_foreign` (`replaced_with_sparepart_id`);

--
-- Indexes for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_logs_asset_id_foreign` (`asset_id`),
  ADD KEY `maintenance_logs_technician_id_foreign` (`technician_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_groups`
--
ALTER TABLE `subscription_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_employee_id_unique` (`employee_id`),
  ADD KEY `users_company_id_foreign` (`company_id`),
  ADD KEY `users_department_id_foreign` (`department_id`),
  ADD KEY `users_subscription_group_id_foreign` (`subscription_group_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `damage_reports`
--
ALTER TABLE `damage_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flight_locations`
--
ALTER TABLE `flight_locations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `flight_logs`
--
ALTER TABLE `flight_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_hardware_items`
--
ALTER TABLE `maintenance_hardware_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_groups`
--
ALTER TABLE `subscription_groups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assets_drone_id_foreign` FOREIGN KEY (`drone_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assets_owner_company_id_foreign` FOREIGN KEY (`owner_company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `assets_subscription_group_id_foreign` FOREIGN KEY (`subscription_group_id`) REFERENCES `subscription_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_subscription_group_id_foreign` FOREIGN KEY (`subscription_group_id`) REFERENCES `subscription_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `damage_reports`
--
ALTER TABLE `damage_reports`
  ADD CONSTRAINT `damage_reports_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  ADD CONSTRAINT `damage_reports_incident_location_id_foreign` FOREIGN KEY (`incident_location_id`) REFERENCES `flight_locations` (`id`),
  ADD CONSTRAINT `damage_reports_reported_by_id_foreign` FOREIGN KEY (`reported_by_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `flight_logs`
--
ALTER TABLE `flight_logs`
  ADD CONSTRAINT `flight_logs_authorized_by_id_foreign` FOREIGN KEY (`authorized_by_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `flight_logs_co_pilot_id_foreign` FOREIGN KEY (`co_pilot_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `flight_logs_drone_id_foreign` FOREIGN KEY (`drone_id`) REFERENCES `assets` (`id`),
  ADD CONSTRAINT `flight_logs_flight_location_id_foreign` FOREIGN KEY (`flight_location_id`) REFERENCES `flight_locations` (`id`),
  ADD CONSTRAINT `flight_logs_pilot_id_foreign` FOREIGN KEY (`pilot_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `flight_logs_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `flight_logs_requesting_company_id_foreign` FOREIGN KEY (`requesting_company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `flight_logs_requesting_department_id_foreign` FOREIGN KEY (`requesting_department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `maintenance_hardware_items`
--
ALTER TABLE `maintenance_hardware_items`
  ADD CONSTRAINT `maintenance_hardware_items_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_hardware_items_maintenance_log_id_foreign` FOREIGN KEY (`maintenance_log_id`) REFERENCES `maintenance_logs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_hardware_items_replaced_with_sparepart_id_foreign` FOREIGN KEY (`replaced_with_sparepart_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD CONSTRAINT `maintenance_logs_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_logs_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_subscription_group_id_foreign` FOREIGN KEY (`subscription_group_id`) REFERENCES `subscription_groups` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
