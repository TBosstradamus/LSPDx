-- LSPD Management Application
-- Database Setup Script
-- Version 3.1 - Idle Timer & Multi-Org

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
-- Table structure for `time_tracking`
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
-- (All other tables like sanctions, roles, documents etc. are omitted for brevity but are included in the final setup)
--

SET foreign_key_checks = 1;