-- LSPD Management Application
-- Database Setup Script
-- Version 1.1

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

--
-- Table structure for table `officers`
--
DROP TABLE IF EXISTS `officers`;
CREATE TABLE `officers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `badgeNumber` VARCHAR(20) NOT NULL UNIQUE,
  `firstName` VARCHAR(50) NOT NULL,
  `lastName` VARCHAR(50) NOT NULL,
  `phoneNumber` VARCHAR(50) DEFAULT NULL,
  `gender` ENUM('male', 'female') NOT NULL,
  `rank` VARCHAR(50) NOT NULL,
  `totalHours` INT NOT NULL DEFAULT 0,
  `isActive` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `users` (for login)
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
-- Table structure for table `departments`
--
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Junction table for `officer_departments`
--
DROP TABLE IF EXISTS `officer_departments`;
CREATE TABLE `officer_departments` (
  `officer_id` INT NOT NULL,
  `department_id` INT NOT NULL,
  PRIMARY KEY (`officer_id`, `department_id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `licenses`
--
DROP TABLE IF EXISTS `licenses`;
CREATE TABLE `licenses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Junction table for `officer_licenses`
--
DROP TABLE IF EXISTS `officer_licenses`;
CREATE TABLE `officer_licenses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `officer_id` INT NOT NULL,
  `license_id` INT NOT NULL,
  `issuedBy` VARCHAR(100) NOT NULL,
  `issuedAt` DATE NOT NULL,
  `expiresAt` DATE NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`license_id`) REFERENCES `licenses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `vehicles` (Master Fleet)
--
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `capacity` INT NOT NULL,
  `licensePlate` VARCHAR(20) NOT NULL UNIQUE,
  `mileage` INT NOT NULL,
  `lastMileage` INT DEFAULT NULL,
  `lastCheckup` DATE DEFAULT NULL,
  `nextCheckup` DATE DEFAULT NULL,
  `on_duty` BOOLEAN NOT NULL DEFAULT FALSE,
  `current_status` INT DEFAULT NULL,
  `current_funk` VARCHAR(50) DEFAULT NULL,
  `current_callsign` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `vehicle_assignments` (real-time seat assignments)
--
DROP TABLE IF EXISTS `vehicle_assignments`;
CREATE TABLE `vehicle_assignments` (
  `vehicle_id` INT NOT NULL,
  `officer_id` INT NOT NULL,
  `seat_index` INT NOT NULL,
  PRIMARY KEY (`vehicle_id`, `seat_index`),
  UNIQUE KEY `officer_id_unique` (`officer_id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `header_assignments` (real-time header roles)
--
DROP TABLE IF EXISTS `header_assignments`;
CREATE TABLE `header_assignments` (
  `role_name` VARCHAR(50) NOT NULL,
  `officer_id` INT NOT NULL,
  PRIMARY KEY (`role_name`),
  UNIQUE KEY `officer_id_unique` (`officer_id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `sanctions`
--
DROP TABLE IF EXISTS `sanctions`;
CREATE TABLE `sanctions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `officer_id` INT NOT NULL,
  `issued_by_officer_id` INT NOT NULL,
  `sanctionType` VARCHAR(50) NOT NULL,
  `reason` TEXT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`issued_by_officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `it_logs`
--
DROP TABLE IF EXISTS `it_logs`;
CREATE TABLE `it_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actor_id` INT,
  `eventType` VARCHAR(50) NOT NULL,
  `details` TEXT NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `meta` JSON DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`actor_id`) REFERENCES `officers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `time_tracking`
--
DROP TABLE IF EXISTS `time_tracking`;
CREATE TABLE `time_tracking` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `officer_id` INT NOT NULL,
  `clockInTime` TIMESTAMP NOT NULL,
  `clockOutTime` TIMESTAMP NULL,
  `duration` INT DEFAULT NULL, -- in seconds
  PRIMARY KEY (`id`),
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Inserting default data
--
INSERT INTO `departments` (`name`) VALUES ('LSPD'), ('Personalabteilung'), ('Leitung Personalabteilung'), ('Field Training Officer'), ('Leitung Field Training Officer'), ('Fuhrparkmanager'), ('Interne Revision'), ('Internal Affairs'), ('Admin');
INSERT INTO `licenses` (`name`) VALUES ('Führerschein Klasse B'), ('Waffenschein'), ('Erste-Hilfe-Zertifikat'), ('Pilotenschein Klasse A'), ('Interne Mitführlizenz für Langwaffen');

--
-- Table structure for table `documents`
--
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `created_by_id` INT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by_id`) REFERENCES `officers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `training_modules`
--
DROP TABLE IF EXISTS `training_modules`;
CREATE TABLE `training_modules` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `created_by_id` INT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
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
-- Table structure for table `settings`
--
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` TEXT,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default checklist template
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('checklist_template', '# Allgemeine Checkliste\n- [ ] Ausrüstung geprüft\n- [ ] Fahrzeug-Check durchgeführt\n- [ ] Status im Funk gemeldet\n\n# Verhalten im Dienst\n- [ ] Respektvoller Umgang mit Bürgern\n- [ ] Einhaltung der StVO\n\n# Nach dem Dienst\n- [ ] Bericht geschrieben\n- [ ] Ausrüstung abgegeben');

-- Insert callsign data as JSON
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('callsign_data', '{"general":[{"code":"10-01","meaning":"Dienst Antritt"},{"code":"10-02","meaning":"Dienst Austritt"},{"code":"10-03","meaning":"Funkcheck"},{"code":"10-04","meaning":"Information verstanden"},{"code":"10-09","meaning":"Funkspruch"},{"code":"10-15","meaning":"Verdächtiger in Gewahrsam"},{"code":"10-17","meaning":"Rückkehr zum Department mit TV"},{"code":"10-19","meaning":"Rückkehr"},{"code":"10-20","meaning":"Aktuelle Position"},{"code":"10-22","meaning":"Abholung benötigt"},{"code":"10-28","meaning":"Aktueller Status"},{"code":"10-33","meaning":"Verunfallt"},{"code":"10-60","meaning":"Aktive Verkehrskontrolle"},{"code":"10-78","meaning":"Dringend Verstärkung benötigt"},{"code":"10-79","meaning":"Request Air Support"},{"code":"10-80","meaning":"Aktive Verfolgungsjagd"},{"code":"10-90","meaning":"Officer in Bedrängnis"},{"code":"11-99","meaning":"Officer unter starkem Beschuss (äußerster Notfall)"},{"code":"Shots fired","meaning":"Schüsse abgegeben oder gefallen"},{"code":"Security Check","meaning":"Frage von Duspatch ob alles in Ordnung ist"}],"status":[{"code":"Code 0","meaning":"Gamecrash / Kopfschmerzen"},{"code":"Code 1","meaning":"Einsatzbereit / Streifenfahrt"},{"code":"Code 2","meaning":"Anfahrt des Dispatches ohne Sonderrechte"},{"code":"Code 3","meaning":"Anfahrt des Dispatches mit Sonderrechte"},{"code":"Code 4","meaning":"Keine weiteren Einheiten benötigt / Einsatz beendet"},{"code":"Code 5","meaning":"Stand-By"},{"code":"Code 6","meaning":"Pause"}],"unit":[{"code":"Sam","meaning":"Einzelstreife"},{"code":"Adam","meaning":"Doppeltstreife"},{"code":"Paul","meaning":"Sergent Streife"},{"code":"Metro","meaning":"SWAT Streife"},{"code":"Air","meaning":"Overwatch"},{"code":"David","meaning":"Detective Streife"},{"code":"Motor","meaning":"Motorradeinheit"},{"code":"Tom","meaning":"Traffic Division"}]}');

--
-- Table structure for table `emails`
--
DROP TABLE IF EXISTS `emails`;
CREATE TABLE `emails` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sender_id` INT,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('sent', 'draft') NOT NULL DEFAULT 'sent',
  PRIMARY KEY (`id`),
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
-- Create default admin user
--
INSERT INTO `officers` (`id`, `badgeNumber`, `firstName`, `lastName`, `gender`, `rank`, `isActive`) VALUES (1, '001', 'Admin', 'User', 'male', 'Chief of Police', 1);
-- The password is 'password'. A bcrypt hash for this is generated here.
INSERT INTO `users` (`officer_id`, `username`, `password_hash`) VALUES (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Assign to 'Admin' department (ID should be 9 based on default data)
INSERT INTO `officer_departments` (`officer_id`, `department_id`) VALUES (1, 9);


SET foreign_key_checks = 1;