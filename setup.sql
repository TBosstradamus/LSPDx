-- LSPD Management Application
-- Database Setup Script
-- Version 3.1 - Fully Multi-Organization Schema

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

--
-- Table structure for table `organizations`
--
DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `short_name` VARCHAR(10) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `officers`
--
DROP TABLE IF EXISTS `officers`;
CREATE TABLE `officers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization_id` INT NOT NULL,
  `badgeNumber` VARCHAR(20) NOT NULL,
  `firstName` VARCHAR(50) NOT NULL,
  `lastName` VARCHAR(50) NOT NULL,
  `display_name` VARCHAR(100) DEFAULT NULL,
  `phoneNumber` VARCHAR(50) DEFAULT NULL,
  `gender` ENUM('male', 'female') NOT NULL,
  `rank` VARCHAR(50) NOT NULL,
  `totalHours` INT NOT NULL DEFAULT 0,
  `isActive` BOOLEAN NOT NULL DEFAULT TRUE,
  `last_assignment_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `org_badge` (`organization_id`, `badgeNumber`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `officer_id` INT NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `vehicles`
--
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `capacity` INT NOT NULL,
  `licensePlate` VARCHAR(20) NOT NULL,
  `mileage` INT NOT NULL,
  `on_duty` BOOLEAN NOT NULL DEFAULT FALSE,
  `current_status` INT DEFAULT NULL,
  `current_funk` VARCHAR(50) DEFAULT NULL,
  `current_callsign` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `org_license_plate` (`organization_id`, `licensePlate`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `dispatch_assignments` (Unified)
--
DROP TABLE IF EXISTS `dispatch_assignments`;
CREATE TABLE `dispatch_assignments` (
    `organization_id` INT NOT NULL,
    `officer_id` INT NOT NULL,
    `assignment_type` ENUM('vehicle', 'header', 'activity') NOT NULL,
    `assignment_id` VARCHAR(50) NOT NULL, -- vehicle_id, role_name, or activity_name
    `seat_index` INT DEFAULT NULL, -- Only for vehicles
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`organization_id`, `officer_id`),
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `sanctions`
--
DROP TABLE IF EXISTS `sanctions`;
CREATE TABLE `sanctions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization_id` INT NOT NULL,
  `officer_id` INT NOT NULL,
  `issued_by_officer_id` INT NOT NULL,
  `sanctionType` VARCHAR(50) NOT NULL,
  `reason` TEXT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`issued_by_officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `time_tracking`
--
DROP TABLE IF EXISTS `time_tracking`;
CREATE TABLE `time_tracking` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `officer_id` INT NOT NULL,
  `organization_id` INT NOT NULL,
  `clockInTime` TIMESTAMP NOT NULL,
  `clockOutTime` TIMESTAMP NULL,
  `duration` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `time_pause_log`
--
DROP TABLE IF EXISTS `time_pause_log`;
CREATE TABLE `time_pause_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `officer_id` INT NOT NULL,
  `organization_id` INT NOT NULL,
  `time_tracking_id` INT NOT NULL,
  `pause_start_time` TIMESTAMP NOT NULL,
  `pause_end_time` TIMESTAMP NULL,
  `duration` INT,
  `reason` VARCHAR(255) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by_id` INT,
  `reviewed_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`time_tracking_id`) REFERENCES `time_tracking`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by_id`) REFERENCES `officers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `documents`
--
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `created_by_id` INT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by_id`) REFERENCES `officers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `training_modules`
--
DROP TABLE IF EXISTS `training_modules`;
CREATE TABLE `training_modules` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `created_by_id` INT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by_id`) REFERENCES `officers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `officer_checklists`
--
DROP TABLE IF EXISTS `officer_checklists`;
CREATE TABLE `officer_checklists` (
  `officer_id` INT NOT NULL,
  `content` TEXT,
  `notes` TEXT,
  `assigned_fto_id` INT,
  PRIMARY KEY (`officer_id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_fto_id`) REFERENCES `officers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `emails`
--
DROP TABLE IF EXISTS `emails`;
CREATE TABLE `emails` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization_id` INT NOT NULL,
  `sender_id` INT,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('sent', 'draft') NOT NULL DEFAULT 'sent',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `officers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `email_recipients`
--
DROP TABLE IF EXISTS `email_recipients`;
CREATE TABLE `email_recipients` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email_id` INT NOT NULL,
  `recipient_id` INT NOT NULL,
  `type` ENUM('to', 'cc') NOT NULL DEFAULT 'to',
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `is_deleted` BOOLEAN NOT NULL DEFAULT FALSE,
  `is_starred` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_recipient` (`email_id`, `recipient_id`),
  FOREIGN KEY (`email_id`) REFERENCES `emails`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`recipient_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `roles` and `permissions`
--
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization_id` INT NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255),
  PRIMARY KEY (`id`),
  UNIQUE KEY `org_role_name` (`organization_id`, `name`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255),
  `category` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `officer_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  PRIMARY KEY (`officer_id`, `role_id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `organization_sharing`
--
DROP TABLE IF EXISTS `organization_sharing`;
CREATE TABLE `organization_sharing` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `source_org_id` INT NOT NULL,
  `target_org_id` INT NOT NULL,
  `data_type` VARCHAR(50) NOT NULL,
  `can_access` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sharing_rule` (`source_org_id`, `target_org_id`, `data_type`),
  FOREIGN KEY (`source_org_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`target_org_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Default Data Insertion
--
INSERT INTO `organizations` (`id`, `name`, `short_name`) VALUES (1, 'Los Santos Police Department', 'LSPD'), (2, 'Federal Investigation Bureau', 'FIB'), (3, 'US Marshals Service', 'USMS'), (4, 'Department of Justice', 'DOJ');

INSERT INTO `officers` (`id`, `organization_id`, `badgeNumber`, `firstName`, `lastName`, `gender`, `rank`, `isActive`) VALUES (1, 1, '001', 'Admin', 'User', 'male', 'Chief of Police', 1);
INSERT INTO `users` (`officer_id`, `username`, `password_hash`) VALUES (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO `roles` (`id`, `organization_id`, `name`, `description`) VALUES
(1, 1, 'Admin', 'Vollzugriff auf alle Systembereiche'),
(2, 1, 'FTO', 'Zugriff auf FTO-Checklisten'),
(3, 1, 'Personalabteilung', 'Zugriff auf HR-Funktionen'),
(4, 1, 'Fuhrparkmanager', 'Zugriff auf die Fahrzeug-Stammdaten'),
(5, 1, 'Beamter', 'Standard-Zugriff');

INSERT INTO `user_roles` (`officer_id`, `role_id`) VALUES (1, 1);

INSERT INTO `permissions` (`id`, `name`, `description`, `category`) VALUES
(1, 'dispatch_view', 'Darf das Dispatch Board sehen', 'Dispatch'),
(2, 'dispatch_manage', 'Darf Einheiten im Dispatch zuweisen', 'Dispatch'),
(3, 'hr_view', 'Darf den Personalbereich sehen', 'Personal'),
(4, 'hr_officers_manage', 'Darf Beamte erstellen, bearbeiten, Rollen zuweisen', 'Personal'),
(5, 'hr_sanctions_manage', 'Darf Sanktionen verhängen', 'Personal'),
(6, 'hr_credentials_manage', 'Darf Passwörter zurücksetzen', 'Personal'),
(7, 'hr_time_approve', 'Darf pausierte Dienstzeiten genehmigen', 'Personal'),
(8, 'fleet_view', 'Darf die Fahrzeug-Stammdaten sehen', 'Fuhrpark'),
(9, 'fleet_manage', 'Darf Fahrzeuge bearbeiten/erstellen/löschen', 'Fuhrpark'),
(10, 'fleet_duty_manage', 'Darf Fahrzeuge in den Dienst stellen', 'Fuhrpark'),
(11, 'fto_view', 'Darf den FTO-Bereich sehen', 'FTO'),
(12, 'fto_checklists_manage', 'Darf FTO-Checklisten bearbeiten', 'FTO'),
(13, 'system_logs_view', 'Darf IT-Systemprotokolle einsehen', 'System'),
(14, 'system_org_manage', 'Darf Organisationen und Freigaben verwalten', 'System'),
(15, 'system_rights_manage', 'Darf Rollen und Berechtigungen verwalten', 'System');

INSERT INTO `role_permissions` (`role_id`, `permission_id`) SELECT 1, id FROM permissions;
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (2, 11), (2, 12);
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (3, 3), (3, 4), (3, 5), (3, 6), (3, 7);
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (4, 8), (4, 9), (4, 10);
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (5, 1);

SET foreign_key_checks = 1;