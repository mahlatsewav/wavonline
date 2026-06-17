-- Database Schema

CREATE DATABASE IF NOT EXISTS wavonline_c2c;
USE wavonline_c2c;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    profile_picture VARCHAR(255),
    role ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
    status ENUM('active', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Listings Table
CREATE TABLE IF NOT EXISTS listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    province VARCHAR(100) NOT NULL DEFAULT 'Gauteng',
    city VARCHAR(100) NOT NULL,
    suburb VARCHAR(100),
    condition_state ENUM('new', 'used') NOT NULL DEFAULT 'used',
    status ENUM('active', 'sold', 'removed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Listing Images Table
CREATE TABLE IF NOT EXISTS listing_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

-- Default Admin User (Password: admin123)
-- password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password_hash, role) VALUES 
('System Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Categories
INSERT INTO categories (name, description) VALUES 
('Electronics', 'Gadgets, phones, computers, and accessories.'),
('Vehicles', 'Cars, motorcycles, and other vehicles.'),
('Furniture', 'Home and office furniture.'),
('Clothing', 'Apparel, shoes, and accessories for men and women.'),
('Books', 'Textbooks, novels, and other reading materials.'),
('Musical Instruments', 'Guitars, keyboards, drums, and other instruments.'),
('Sports', 'Sporting goods, exercise equipment, and activewear.'),
('Outside/Garden', 'Tools, patio furniture, and gardening supplies.'),
('Kitchen', 'Appliances, cookware, and dining sets.'),
('Toys', 'Action figures, dolls, board games, and more.')
ON DUPLICATE KEY UPDATE id=id;
