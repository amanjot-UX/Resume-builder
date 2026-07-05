-- Resume Builder Database Setup
-- Run this SQL to create the database and tables

CREATE DATABASE IF NOT EXISTS `resume_builder` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resume_builder`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Resumes Table
CREATE TABLE IF NOT EXISTS `resumes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL DEFAULT 'My Resume',
  `template` VARCHAR(50) NOT NULL DEFAULT 'modern',
  `theme_color` VARCHAR(20) DEFAULT '#6366f1',
  `is_public` TINYINT(1) DEFAULT 0,
  `public_slug` VARCHAR(100) UNIQUE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Personal Info Table
CREATE TABLE IF NOT EXISTS `personal_info` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resume_id` INT NOT NULL UNIQUE,
  `full_name` VARCHAR(150) DEFAULT '',
  `job_title` VARCHAR(150) DEFAULT '',
  `email` VARCHAR(150) DEFAULT '',
  `phone` VARCHAR(30) DEFAULT '',
  `location` VARCHAR(200) DEFAULT '',
  `website` VARCHAR(200) DEFAULT '',
  `linkedin` VARCHAR(200) DEFAULT '',
  `github` VARCHAR(200) DEFAULT '',
  `summary` TEXT DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Education Table
CREATE TABLE IF NOT EXISTS `education` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resume_id` INT NOT NULL,
  `institution` VARCHAR(200) DEFAULT '',
  `degree` VARCHAR(200) DEFAULT '',
  `field` VARCHAR(200) DEFAULT '',
  `start_date` VARCHAR(20) DEFAULT '',
  `end_date` VARCHAR(20) DEFAULT '',
  `current` TINYINT(1) DEFAULT 0,
  `gpa` VARCHAR(20) DEFAULT '',
  `description` TEXT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Experience Table
CREATE TABLE IF NOT EXISTS `experience` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resume_id` INT NOT NULL,
  `company` VARCHAR(200) DEFAULT '',
  `position` VARCHAR(200) DEFAULT '',
  `location` VARCHAR(200) DEFAULT '',
  `start_date` VARCHAR(20) DEFAULT '',
  `end_date` VARCHAR(20) DEFAULT '',
  `current` TINYINT(1) DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Skills Table
CREATE TABLE IF NOT EXISTS `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resume_id` INT NOT NULL,
  `name` VARCHAR(100) DEFAULT '',
  `level` TINYINT DEFAULT 3,
  `category` VARCHAR(100) DEFAULT 'Technical',
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resume_id` INT NOT NULL,
  `name` VARCHAR(200) DEFAULT '',
  `role` VARCHAR(200) DEFAULT '',
  `url` VARCHAR(300) DEFAULT '',
  `start_date` VARCHAR(20) DEFAULT '',
  `end_date` VARCHAR(20) DEFAULT '',
  `description` TEXT DEFAULT NULL,
  `technologies` TEXT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Certifications Table
CREATE TABLE IF NOT EXISTS `certifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resume_id` INT NOT NULL,
  `name` VARCHAR(200) DEFAULT '',
  `issuer` VARCHAR(200) DEFAULT '',
  `date` VARCHAR(20) DEFAULT '',
  `credential_id` VARCHAR(200) DEFAULT '',
  `url` VARCHAR(300) DEFAULT '',
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Languages Table
CREATE TABLE IF NOT EXISTS `languages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resume_id` INT NOT NULL,
  `name` VARCHAR(100) DEFAULT '',
  `proficiency` VARCHAR(50) DEFAULT 'Intermediate',
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert demo user (password: demo1234)
INSERT IGNORE INTO `users` (`name`, `email`, `password`) VALUES 
('Demo User', 'demo@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/Lewdg.5Y.KA6TKveq');
