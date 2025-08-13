-- 0S-CARE Database Schema
-- Cancer Patient Care Management System

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `os_care_dev` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `os_care_dev`;

-- Users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('client','caregiver','admin') NOT NULL DEFAULT 'client',
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `privacy_settings` json DEFAULT NULL,
  `consent_given` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `account_status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User relationships (caregiver-client connections)
CREATE TABLE `user_relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caregiver_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `relationship_type` enum('primary','secondary','emergency') DEFAULT 'primary',
  `access_level` enum('view','moderate','full') DEFAULT 'view',
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_caregiver` (`caregiver_id`),
  KEY `fk_client` (`client_id`),
  CONSTRAINT `fk_caregiver` FOREIGN KEY (`caregiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily check-ins
CREATE TABLE `daily_checkins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `checkin_date` date NOT NULL,
  `mood` tinyint(1) NOT NULL COMMENT '1-5 scale',
  `energy_level` enum('high','okay','low','exhausted') NOT NULL,
  `pain_level` tinyint(1) NOT NULL COMMENT '0-10 scale',
  `pain_locations` json DEFAULT NULL,
  `appetite` enum('good','fair','poor') NOT NULL,
  `hydration_cups` tinyint(2) DEFAULT 0,
  `symptoms` json DEFAULT NULL,
  `activity_level` enum('none','light','moderate','active') NOT NULL,
  `sleep_quality` enum('good','fair','poor') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `daily_highlight` text DEFAULT NULL,
  `anxiety_or_worry` tinyint(1) DEFAULT 0,
  `photos` json DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`, `checkin_date`),
  KEY `fk_checkin_user` (`user_id`),
  CONSTRAINT `fk_checkin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medications
CREATE TABLE `medications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `frequency` varchar(100) NOT NULL,
  `instructions` text DEFAULT NULL,
  `prescribing_doctor` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `medication_type` enum('regular','prn','supplement') DEFAULT 'regular',
  `side_effects` text DEFAULT NULL,
  `reminders_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_medication_user` (`user_id`),
  CONSTRAINT `fk_medication_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medication logs
CREATE TABLE `medication_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medication_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `taken_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('taken','skipped','late','prn') NOT NULL,
  `notes` text DEFAULT NULL,
  `side_effects` text DEFAULT NULL,
  `logged_by` int(11) DEFAULT NULL COMMENT 'User ID who logged it',
  PRIMARY KEY (`id`),
  KEY `fk_medlog_medication` (`medication_id`),
  KEY `fk_medlog_user` (`user_id`),
  KEY `fk_medlog_logger` (`logged_by`),
  CONSTRAINT `fk_medlog_medication` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_medlog_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_medlog_logger` FOREIGN KEY (`logged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Patient the task is for',
  `assigned_by` int(11) DEFAULT NULL COMMENT 'Caregiver who assigned the task',
  `assigned_to` int(11) DEFAULT NULL COMMENT 'Who should complete the task',
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `category` enum('medication','appointment','exercise','nutrition','other') DEFAULT 'other',
  `due_date` datetime DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_task_user` (`user_id`),
  KEY `fk_task_assigned_by` (`assigned_by`),
  KEY `fk_task_assigned_to` (`assigned_to`),
  KEY `fk_task_completed_by` (`completed_by`),
  CONSTRAINT `fk_task_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_task_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_task_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_task_completed_by` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Appointments
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `appointment_date` datetime NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `location` varchar(255) DEFAULT NULL,
  `provider_name` varchar(100) DEFAULT NULL,
  `provider_phone` varchar(20) DEFAULT NULL,
  `appointment_type` enum('in_person','telehealth','phone') DEFAULT 'in_person',
  `telehealth_link` varchar(500) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
  `reminders_sent` json DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_appointment_user` (`user_id`),
  KEY `idx_appointment_date` (`appointment_date`),
  CONSTRAINT `fk_appointment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vitals tracking
CREATE TABLE `vitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `recorded_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `blood_pressure_systolic` int(11) DEFAULT NULL,
  `blood_pressure_diastolic` int(11) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `oxygen_saturation` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_vitals_user` (`user_id`),
  KEY `fk_vitals_recorder` (`recorded_by`),
  CONSTRAINT `fk_vitals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vitals_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Symptoms tracking
CREATE TABLE `symptoms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `symptom_name` varchar(100) NOT NULL,
  `severity` tinyint(1) NOT NULL COMMENT '1-10 scale',
  `location` varchar(100) DEFAULT NULL,
  `onset_time` datetime DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `triggers` text DEFAULT NULL,
  `relief_methods` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_symptom_user` (`user_id`),
  KEY `idx_symptom_date` (`recorded_at`),
  CONSTRAINT `fk_symptom_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Care notes
CREATE TABLE `care_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `note_type` enum('general','medical','behavioral','urgent') DEFAULT 'general',
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `visibility` enum('private','care_team','family') DEFAULT 'care_team',
  `is_urgent` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_note_patient` (`patient_id`),
  KEY `fk_note_author` (`author_id`),
  KEY `idx_note_created` (`created_at`),
  CONSTRAINT `fk_note_patient` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_note_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Emergency contacts
CREATE TABLE `emergency_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `phone_primary` varchar(20) NOT NULL,
  `phone_secondary` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_emergency_user` (`user_id`),
  CONSTRAINT `fk_emergency_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages between users
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `is_urgent` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `parent_message_id` int(11) DEFAULT NULL,
  `sent_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_message_sender` (`sender_id`),
  KEY `fk_message_recipient` (`recipient_id`),
  KEY `fk_message_parent` (`parent_message_id`),
  KEY `idx_message_sent` (`sent_at`),
  CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_parent` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Documents storage
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `document_type` enum('medical','legal','insurance','personal','other') DEFAULT 'other',
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_document_user` (`user_id`),
  KEY `fk_document_uploader` (`uploaded_by`),
  CONSTRAINT `fk_document_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_document_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Error logs
CREATE TABLE `error_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `error_level` enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') NOT NULL,
  `error_message` text NOT NULL,
  `error_file` varchar(255) DEFAULT NULL,
  `error_line` int(11) DEFAULT NULL,
  `stack_trace` text DEFAULT NULL,
  `request_uri` varchar(500) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_error_user` (`user_id`),
  KEY `idx_error_level` (`error_level`),
  KEY `idx_error_created` (`created_at`),
  CONSTRAINT `fk_error_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User preferences
CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_preference` (`user_id`, `preference_key`),
  KEY `fk_preference_user` (`user_id`),
  CONSTRAINT `fk_preference_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `consent_given`) VALUES
('diana', 'diana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'Diana', 'Johnson', 1),
('chance', 'chance@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'caregiver', 'Chance', 'Johnson', 1);

INSERT INTO `user_relationships` (`caregiver_id`, `client_id`, `relationship_type`, `access_level`, `status`) VALUES
(2, 1, 'primary', 'full', 'active');

INSERT INTO `medications` (`user_id`, `name`, `dosage`, `frequency`, `instructions`, `start_date`) VALUES
(1, 'Morphine', '10mg', 'Every 4 hours as needed', 'Take with food for pain management', '2024-01-01'),
(1, 'Ondansetron', '4mg', 'Every 8 hours as needed', 'For nausea and vomiting', '2024-01-01');

INSERT INTO `emergency_contacts` (`user_id`, `name`, `relationship`, `phone_primary`, `is_primary`) VALUES
(1, 'Chance Johnson', 'Son/Caregiver', '306-555-0123', 1),
(1, 'Dr. Sarah Wilson', 'Oncologist', '306-555-0456', 0);

COMMIT;