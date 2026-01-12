-- Database: taskflow_db
-- Created for: UAS Pemrograman Web Dasar

CREATE DATABASE IF NOT EXISTS taskflow_db;
USE taskflow_db;

-- Table: tasks
CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('kerja', 'kuliah', 'pribadi', 'belanja', 'lainnya') DEFAULT 'lainnya',
    deadline DATE NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample data
INSERT INTO tasks (title, description, category, deadline, priority, completed) VALUES
('Selesaikan Proyek UAS Web', 'Membuat aplikasi Todo List dengan PHP, MySQL, dan JavaScript', 'kuliah', '2024-12-15', 'high', FALSE),
('Presentasi Proyek TaskFlow', 'Mempresentasikan aplikasi Todo List di depan kelas', 'kuliah', '2024-12-20', 'medium', FALSE),
('Beli Bahan Makanan Mingguan', 'Beras, telur, sayuran, buah-buahan, susu', 'belanja', '2024-11-30', 'low', TRUE),
('Rapat Tim Developer', 'Membahas progress aplikasi dan bug fixing', 'kerja', '2024-12-05', 'medium', FALSE),
('Studi untuk Ujian Database', 'Mempelajari SQL queries dan normalisasi', 'kuliah', '2024-12-10', 'high', FALSE),
('Olahraga Pagi', 'Jogging 30 menit di taman', 'pribadi', '2024-11-28', 'low', TRUE),
('Update Portfolio Website', 'Menambahkan proyek TaskFlow ke portfolio', 'kerja', '2024-12-25', 'medium', FALSE),
('Meeting dengan Klien', 'Presentasi progress project web e-commerce', 'kerja', '2024-12-03', 'high', FALSE);

-- View: task_overview (optional)
CREATE VIEW task_overview AS
SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN completed = TRUE THEN 1 ELSE 0 END) as completed_tasks,
    SUM(CASE WHEN completed = FALSE THEN 1 ELSE 0 END) as pending_tasks,
    SUM(CASE WHEN deadline < CURDATE() AND completed = FALSE THEN 1 ELSE 0 END) as overdue_tasks
FROM tasks;

-- Stored Procedure: get_tasks_by_category (optional)
DELIMITER //
CREATE PROCEDURE get_tasks_by_category(IN cat VARCHAR(20))
BEGIN
    SELECT * FROM tasks WHERE category = cat ORDER BY deadline;
END //
DELIMITER ;

-- Database: taskflow_db
-- Dengan Sistem Login

CREATE DATABASE IF NOT EXISTS taskflow_db;
USE taskflow_db;

-- ==================== TABEL USERS ====================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    profile_image VARCHAR(255) DEFAULT 'default.png',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==================== TABEL TASKS ====================
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('kerja', 'kuliah', 'pribadi', 'belanja', 'lainnya') DEFAULT 'lainnya',
    deadline DATE NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==================== INDEXES ====================
CREATE INDEX idx_user_id ON tasks(user_id);
CREATE INDEX idx_task_status ON tasks(completed, deadline);
CREATE INDEX idx_user_email ON users(email);

-- ==================== SAMPLE DATA ====================
-- Password untuk semua user: password123 (sudah di-hash)

INSERT INTO users (username, email, password, full_name, role) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'user'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'user'),
('admin', 'admin@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

INSERT INTO tasks (user_id, title, description, category, deadline, priority, completed) VALUES
(1, 'Selesaikan Proyek UAS Web', 'Membuat aplikasi Todo List dengan login system', 'kuliah', '2024-12-15', 'high', FALSE),
(1, 'Presentasi di Kelas', 'Demo aplikasi TaskFlow dengan login', 'kuliah', '2024-12-20', 'medium', FALSE),
(2, 'Beli Bahan Makanan', 'Beras, telur, sayuran, buah-buahan', 'belanja', '2024-11-30', 'low', TRUE),
(2, 'Meeting dengan Klien', 'Presentasi progress project web', 'kerja', '2024-12-05', 'high', FALSE);