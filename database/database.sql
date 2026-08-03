-- =========================================================
-- Gym Management System - Database Schema & Initial Data
-- Database Name: gym_db
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `gym_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gym_db`;

-- Drop existing tables safely
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `members`;
DROP TABLE IF EXISTS `trainers`;
DROP TABLE IF EXISTS `plans`;

-- ---------------------------------------------------------
-- 1. Table: `plans` (Membership Packages)
-- ---------------------------------------------------------
CREATE TABLE `plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `duration_months` INT NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data for plans
INSERT INTO `plans` (`id`, `name`, `duration_months`, `price`, `description`, `status`) VALUES
(1, 'Basic Membership', 1, 3500, 'Access to gym equipment, locker rooms, and standard hours.', 'Active'),
(2, 'Standard Quarterly', 3, 9500, 'Full gym access, free Wi-Fi, and 1 complimentary trainer session.', 'Active'),
(3, 'Pro Semi-Annual', 6, 17500, 'Unlimited 24/7 access, sauna access, and monthly body composition analysis.', 'Active'),
(4, 'VIP Annual Pass', 12, 32000, 'All-inclusive VIP privileges, personal trainer sessions, nutrition plan & sauna.', 'Active');

-- ---------------------------------------------------------
-- 2. Table: `trainers` (Fitness Trainers / Staff)
-- ---------------------------------------------------------
CREATE TABLE `trainers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `specialization` VARCHAR(100) NOT NULL,
    `joining_date` DATE NOT NULL,
    `status` ENUM('Pending', 'Active', 'On Leave', 'Inactive') DEFAULT 'Active',
    `photo` VARCHAR(255) DEFAULT 'default_user.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- No seed data - trainers register themselves via register_trainer.php

-- ---------------------------------------------------------
-- 3. Table: `members` (Gym Members)
-- ---------------------------------------------------------
CREATE TABLE `members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_code` VARCHAR(20) NOT NULL UNIQUE,
    `first_name` VARCHAR(60) NOT NULL,
    `last_name` VARCHAR(60) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `dob` DATE NULL,
    `address` TEXT NULL,
    `plan_id` INT NOT NULL,
    `trainer_id` INT NULL,
    `join_date` DATE NOT NULL,
    `expire_date` DATE NOT NULL,
    `status` ENUM('Active', 'Expired', 'Pending', 'Suspended') DEFAULT 'Active',
    `photo` VARCHAR(255) DEFAULT 'default_user.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_members_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_members_trainer` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- No seed data - members register themselves via register_member.php

-- ---------------------------------------------------------
-- 4. Table: `attendance` (Daily Member Check-Ins)
-- ---------------------------------------------------------
CREATE TABLE `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `check_in` DATETIME NOT NULL,
    `check_out` DATETIME NULL,
    `date` DATE NOT NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_attendance_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- No seed data - filled in as real members check in

-- ---------------------------------------------------------
-- 5. Table: `payments` (Billing & Invoices)
-- ---------------------------------------------------------
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `invoice_no` VARCHAR(30) NOT NULL UNIQUE,
    `amount` DECIMAL(10, 2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `payment_method` ENUM('Cash', 'Credit Card', 'Debit Card', 'Bank Transfer', 'Online', 'Pending Payment') NOT NULL,
    `payment_status` ENUM('Paid', 'Pending', 'Failed', 'Refunded') DEFAULT 'Paid',
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_payments_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- No seed data - filled in as real payments are recorded

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- 6. Table: `users` (Login Accounts - Admin / Trainer / Member)
-- ---------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'trainer', 'member') NOT NULL,
    `ref_id` INT NULL COMMENT 'Links to trainers.id or members.id. NULL for admin.',
    `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `verification_code` VARCHAR(6) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: The one Admin account is auto-created on first visit to login.php
-- (email: admin@pulsefitgym.com / password: Admin@123) - see classes/Auth.php
