-- database/taxi_booking.sql
-- Clean database schema for Taxi Booking System (all payment functionality removed)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `taxi_booking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `taxi_booking`;

-- Admin users table
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Car types table
CREATE TABLE IF NOT EXISTS `car_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `base_fare` decimal(10,2) NOT NULL DEFAULT 0.00,
  `per_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `per_minute` decimal(10,2) NOT NULL DEFAULT 0.00,
  `waiting_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `icon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Drivers table
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `car_model` varchar(100) DEFAULT NULL,
  `car_number` varchar(50) DEFAULT NULL,
  `car_type_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `last_location_update` datetime DEFAULT NULL,
  `status` enum('online','offline','on_ride') NOT NULL DEFAULT 'offline',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `documents` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `car_type_id` (`car_type_id`),
  CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`car_type_id`) REFERENCES `car_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Passengers table
CREATE TABLE IF NOT EXISTS `passengers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `documents` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rides table
CREATE TABLE IF NOT EXISTS `rides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `passenger_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `car_type_id` int(11) NOT NULL,
  `pickup_address` text NOT NULL,
  `pickup_latitude` decimal(10,8) NOT NULL,
  `pickup_longitude` decimal(11,8) NOT NULL,
  `destination_address` text NOT NULL,
  `destination_latitude` decimal(10,8) NOT NULL,
  `destination_longitude` decimal(11,8) NOT NULL,
  `distance` decimal(10,2) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `fare` decimal(10,2) NOT NULL,
  `status` enum('pending','accepted','driver_arrived','started','completed','cancelled') NOT NULL DEFAULT 'pending',
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `cancelled_by` enum('passenger','driver','admin') DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_at` datetime DEFAULT NULL,
  `driver_arrived_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `passenger_id` (`passenger_id`),
  KEY `driver_id` (`driver_id`),
  KEY `car_type_id` (`car_type_id`),
  CONSTRAINT `rides_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rides_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rides_ibfk_3` FOREIGN KEY (`car_type_id`) REFERENCES `car_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ratings table
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ride_id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ride_id` (`ride_id`),
  KEY `passenger_id` (`passenger_id`),
  KEY `driver_id` (`driver_id`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user with a CORRECTLY hashed password 'password123'
INSERT IGNORE INTO `admin_users` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');

-- Insert default settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('company_name', 'Taxi Booking', 'The name of the company.'),
('currency', 'USD', 'The currency used for transactions.'),
('currency_symbol', '$', 'The currency symbol used for display.'),
('min_fare', '5.00', 'The minimum fare for a ride.'),
('max_wait_time', '300', 'Maximum wait time for a driver in seconds.'),
('search_radius', '5', 'Radius in kilometers to search for drivers');

-- Insert default car types
INSERT IGNORE INTO `car_types` (`name`, `base_fare`, `per_km`, `per_minute`, `waiting_fee`, `icon`) VALUES
('Economy', 50.00, 15.00, 2.00, 1.00, 'car'),
('Comfort', 80.00, 20.00, 3.00, 1.50, 'car-side'),
('Premium', 120.00, 30.00, 5.00, 2.00, 'car-alt');

-- Insert sample drivers
INSERT IGNORE INTO `drivers` (`name`, `email`, `phone`, `password`, `license_number`, `car_model`, `car_number`, `car_type_id`, `status`, `is_verified`) VALUES
('John Doe', 'john.doe@example.com', '+1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DL123456', 'Toyota Camry', 'ABC123', 1, 'online', 1),
('Jane Smith', 'jane.smith@example.com', '+0987654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DL654321', 'Honda Accord', 'XYZ789', 2, 'offline', 1),
('Mike Johnson', 'mike.johnson@example.com', '+1122334455', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DL987654', 'Toyota Prius', 'DEF456', 1, 'on_ride', 1);

-- Insert sample passengers
INSERT IGNORE INTO `passengers` (`name`, `email`, `phone`, `password`) VALUES
('Alice Brown', 'alice@example.com', '+1555666777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Bob Wilson', 'bob@example.com', '+1888999000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Carol Davis', 'carol@example.com', '+1444333222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample rides
INSERT IGNORE INTO `rides` (`passenger_id`, `driver_id`, `car_type_id`, `pickup_address`, `pickup_latitude`, `pickup_longitude`, `destination_address`, `destination_latitude`, `destination_longitude`, `distance`, `duration`, `fare`, `status`, `requested_at`) VALUES
(1, 1, 1, '123 Main St, New York, NY', 40.712800, -74.006000, '456 Broadway, New York, NY', 40.720000, -74.000000, 5.2, 15, 78.00, 'completed', NOW() - INTERVAL 2 DAY),
(2, 2, 2, '789 Park Ave, New York, NY', 40.715000, -74.008000, '321 5th Ave, New York, NY', 40.725000, -74.002000, 3.8, 10, 96.00, 'completed', NOW() - INTERVAL 1 DAY),
(3, NULL, 1, '555 Liberty St, New York, NY', 40.710000, -74.012000, '999 Freedom Ave, New York, NY', 40.730000, -74.005000, 7.5, 20, 112.50, 'pending', NOW());

COMMIT;