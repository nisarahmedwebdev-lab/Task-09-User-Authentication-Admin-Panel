CREATE DATABASE IF NOT EXISTS user_auth_system;
USE user_auth_system;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    country VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    role ENUM('user', 'admin') DEFAULT 'user',
    privacy_agreed TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to INT,
    created_by INT NOT NULL,
    due_date DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert admin user (password: Admin@123)
INSERT INTO users (full_name, email, password, gender, country, role, privacy_agreed) 
VALUES (
    'Admin',
    'admin@example.com',
    '$2y$12$nCUAUvAwTtrqlJBz65IxhunFEudrtShGqMpG07aav6Xvow1ZkV6OK',
    'male',
    'Pakistan',
    'admin',
    1
);

-- Insert sample tasks
INSERT INTO tasks (title, description, status, priority, assigned_to, created_by, due_date) 
VALUES 
('Complete Project Documentation', 'Write complete documentation for the project including API docs', 'in_progress', 'high', 1, 1, DATE_ADD(NOW(), INTERVAL 2 DAY)),
('Design Database Schema', 'Design and implement the database schema for the new feature', 'completed', 'medium', 1, 1, DATE_ADD(NOW(), INTERVAL -1 DAY)),
('Setup Development Environment', 'Configure development environment with all necessary tools', 'pending', 'low', 1, 1, DATE_ADD(NOW(), INTERVAL 3 DAY)),
('Create User Interface', 'Design and implement the user interface for admin panel', 'in_progress', 'high', 1, 1, DATE_ADD(NOW(), INTERVAL 5 DAY)),
('Write Unit Tests', 'Write comprehensive unit tests for all modules', 'pending', 'medium', 1, 1, DATE_ADD(NOW(), INTERVAL 7 DAY));

USE user_auth_system;

-- Create tasks table (if not exists)
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to INT NULL,
    created_by INT NOT NULL,
    due_date DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create task_submissions table for user submissions
CREATE TABLE IF NOT EXISTS task_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample tasks for users
INSERT INTO tasks (title, description, status, priority, assigned_to, created_by, due_date) 
VALUES 
('Complete Project Documentation', 'Write complete documentation for the project including API docs', 'in_progress', 'high', 1, 1, DATE_ADD(NOW(), INTERVAL 2 DAY)),
('Design Database Schema', 'Design and implement the database schema for the new feature', 'pending', 'medium', 1, 1, DATE_ADD(NOW(), INTERVAL 3 DAY)),
('Setup Development Environment', 'Configure development environment with all necessary tools', 'pending', 'low', 1, 1, DATE_ADD(NOW(), INTERVAL 5 DAY));

ALTER TABLE task_submissions ADD COLUMN admin_viewed TINYINT(1) DEFAULT 0;
ALTER TABLE task_submissions ADD COLUMN admin_comment TEXT NULL;
ALTER TABLE task_submissions ADD COLUMN reviewed_at DATETIME NULL;