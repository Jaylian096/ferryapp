-- ============================================
-- Bantayan Ferry Booking & Scheduling System
-- Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS bantayan_ferry;
USE bantayan_ferry;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(20),
    role ENUM('user','admin') DEFAULT 'user',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admins Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shipping Lines Table
CREATE TABLE shipping_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    route VARCHAR(200),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fares Table
CREATE TABLE fares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipping_line_id INT NOT NULL,
    passenger_type ENUM('Regular','Student','Senior Citizen','PWD','Child') NOT NULL,
    class_type ENUM('Economy','Class','N/A') DEFAULT 'N/A',
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (shipping_line_id) REFERENCES shipping_lines(id) ON DELETE CASCADE
);

-- Cargo Rates Table
CREATE TABLE cargo_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipping_line_id INT NOT NULL,
    cargo_type ENUM('Small Box','Large Box','Vehicle','Motorcycle') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (shipping_line_id) REFERENCES shipping_lines(id) ON DELETE CASCADE
);

-- Schedules Table
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipping_line_id INT NOT NULL,
    route VARCHAR(200) NOT NULL,
    departure_time TIME NOT NULL,
    status ENUM('active','cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipping_line_id) REFERENCES shipping_lines(id) ON DELETE CASCADE
);

-- Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    shipping_line_id INT NOT NULL,
    schedule_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('confirmed','cancelled','completed') DEFAULT 'confirmed',
    reference_no VARCHAR(20) NOT NULL UNIQUE,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (shipping_line_id) REFERENCES shipping_lines(id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(id)
);

-- Booking Details Table
CREATE TABLE booking_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    passenger_type ENUM('Regular','Student','Senior Citizen','PWD','Child'),
    class_type VARCHAR(20) DEFAULT 'N/A',
    fare DECIMAL(10,2) DEFAULT 0,
    cargo_type VARCHAR(50) DEFAULT NULL,
    cargo_price DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- ============================================
-- DEFAULT DATA
-- ============================================

-- Default Admin (password: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Shipping Lines
INSERT INTO shipping_lines (name, route) VALUES
('Island Shipping', 'Santa Fe ↔ Hagnaya'),
('Super Shuttle Ferry', 'Santa Fe ↔ Hagnaya'),
('Aznar Shipping', 'Santa Fe ↔ Medellin');

-- Fares: Island Shipping (id=1)
INSERT INTO fares (shipping_line_id, passenger_type, class_type, price) VALUES
(1, 'Regular', 'N/A', 180.00),
(1, 'Student', 'N/A', 140.00),
(1, 'Senior Citizen', 'N/A', 126.00),
(1, 'PWD', 'N/A', 126.00),
(1, 'Child', 'N/A', 90.00);

-- Fares: Super Shuttle Ferry (id=2)
INSERT INTO fares (shipping_line_id, passenger_type, class_type, price) VALUES
(2, 'Regular', 'N/A', 190.00),
(2, 'Student', 'N/A', 150.00),
(2, 'Senior Citizen', 'N/A', 133.00),
(2, 'PWD', 'N/A', 133.00),
(2, 'Child', 'N/A', 95.00);

-- Fares: Aznar Shipping (id=3) Economy
INSERT INTO fares (shipping_line_id, passenger_type, class_type, price) VALUES
(3, 'Regular', 'Economy', 160.00),
(3, 'Student', 'Economy', 120.00),
(3, 'Senior Citizen', 'Economy', 112.00),
(3, 'PWD', 'Economy', 112.00),
(3, 'Child', 'Economy', 80.00);

-- Fares: Aznar Shipping (id=3) Class
INSERT INTO fares (shipping_line_id, passenger_type, class_type, price) VALUES
(3, 'Regular', 'Class', 280.00),
(3, 'Student', 'Class', 220.00),
(3, 'Senior Citizen', 'Class', 196.00),
(3, 'PWD', 'Class', 196.00),
(3, 'Child', 'Class', 140.00);

-- Cargo Rates: Island Shipping
INSERT INTO cargo_rates (shipping_line_id, cargo_type, price) VALUES
(1, 'Small Box', 150.00),
(1, 'Large Box', 300.00),
(1, 'Vehicle', 1500.00),
(1, 'Motorcycle', 500.00);

-- Cargo Rates: Super Shuttle Ferry
INSERT INTO cargo_rates (shipping_line_id, cargo_type, price) VALUES
(2, 'Small Box', 160.00),
(2, 'Large Box', 320.00),
(2, 'Vehicle', 1600.00),
(2, 'Motorcycle', 520.00);

-- Cargo Rates: Aznar Shipping
INSERT INTO cargo_rates (shipping_line_id, cargo_type, price) VALUES
(3, 'Small Box', 140.00),
(3, 'Large Box', 280.00),
(3, 'Vehicle', 1400.00),
(3, 'Motorcycle', 480.00);

-- Schedules: Island Shipping
INSERT INTO schedules (shipping_line_id, route, departure_time) VALUES
(1, 'Santa Fe → Hagnaya', '06:00:00'),
(1, 'Santa Fe → Hagnaya', '09:00:00'),
(1, 'Santa Fe → Hagnaya', '12:00:00'),
(1, 'Santa Fe → Hagnaya', '15:00:00'),
(1, 'Hagnaya → Santa Fe', '07:30:00'),
(1, 'Hagnaya → Santa Fe', '10:30:00'),
(1, 'Hagnaya → Santa Fe', '13:30:00'),
(1, 'Hagnaya → Santa Fe', '16:30:00');

-- Schedules: Super Shuttle Ferry
INSERT INTO schedules (shipping_line_id, route, departure_time) VALUES
(2, 'Santa Fe → Hagnaya', '07:00:00'),
(2, 'Santa Fe → Hagnaya', '10:00:00'),
(2, 'Santa Fe → Hagnaya', '13:00:00'),
(2, 'Santa Fe → Hagnaya', '16:00:00'),
(2, 'Hagnaya → Santa Fe', '08:30:00'),
(2, 'Hagnaya → Santa Fe', '11:30:00'),
(2, 'Hagnaya → Santa Fe', '14:30:00'),
(2, 'Hagnaya → Santa Fe', '17:30:00');

-- Schedules: Aznar Shipping
INSERT INTO schedules (shipping_line_id, route, departure_time) VALUES
(3, 'Santa Fe → Medellin', '08:00:00'),
(3, 'Santa Fe → Medellin', '14:00:00'),
(3, 'Medellin → Santa Fe', '10:00:00'),
(3, 'Medellin → Santa Fe', '16:00:00');
