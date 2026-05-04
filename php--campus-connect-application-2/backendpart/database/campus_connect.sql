-- Use the same database
USE campus_connect;
-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fullname` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `student_id` VARCHAR(50) NOT NULL UNIQUE,
    `department` VARCHAR(100) DEFAULT 'Software Engineering',
    `year` INT DEFAULT 1,
    `phone` VARCHAR(20) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `role` ENUM('student', 'admin') DEFAULT 'student',
    `is_active` TINYINT DEFAULT 1,
    `remember_token` VARCHAR(255) DEFAULT NULL,
    `remember_token_expiry` DATETIME DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. ANNOUNCEMENTS TABLE
CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `created_by` VARCHAR(100) DEFAULT 'Admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. COURSES TABLE
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_code` VARCHAR(20) NOT NULL,
    `course_name` VARCHAR(200) NOT NULL,
    `credit_hours` INT NOT NULL,
    `prerequisites` VARCHAR(200) DEFAULT 'None',
    `year` INT NOT NULL,
    `semester` INT NOT NULL,
    `field` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. GROUPS TABLE (Study Groups)
CREATE TABLE IF NOT EXISTS `groups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `course_name` VARCHAR(100) DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT 'Software Engineering',
    `year` VARCHAR(20) DEFAULT NULL,
    `days` VARCHAR(100) DEFAULT NULL,
    `start_time` TIME DEFAULT NULL,
    `end_time` TIME DEFAULT NULL,
    `created_by` INT DEFAULT NULL,
    `member_count` INT DEFAULT 1,
    `max_members` INT DEFAULT 20,
    `status` ENUM('active', 'closed', 'archived') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `organizer` VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- 5. GROUP MEMBERS TABLE
CREATE TABLE IF NOT EXISTS `group_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `role` ENUM('admin', 'member') DEFAULT 'member',
    `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- 6. GROUP MESSAGES TABLE
CREATE TABLE IF NOT EXISTS `group_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- 7. INTERNSHIPS TABLE
CREATE TABLE IF NOT EXISTS `internships` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `company` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `location` VARCHAR(200) DEFAULT NULL,
    `stipend` VARCHAR(50) DEFAULT NULL,
    `stipend_type` ENUM('paid', 'unpaid') DEFAULT 'unpaid',
    `duration` VARCHAR(50) DEFAULT NULL,
    `deadline` DATE DEFAULT NULL,
    `requirements` TEXT,
    `year_requirement` VARCHAR(50) DEFAULT NULL,
    `work_type` ENUM('remote', 'on-site', 'hybrid') DEFAULT 'on-site',
    `posted_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`posted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- 8. MATERIALS TABLE
CREATE TABLE IF NOT EXISTS `materials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `full_name` VARCHAR(100) DEFAULT NULL,
    `year` VARCHAR(20) DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `material_type` VARCHAR(50) DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT DEFAULT NULL,
    `uploaded_by` INT DEFAULT NULL,
    `downloads_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- 9. MENTORS TABLE
CREATE TABLE IF NOT EXISTS `mentors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `skills` TEXT,
    `department` VARCHAR(100) DEFAULT 'Software Engineering',
    `year` VARCHAR(20) DEFAULT NULL,
    `bio` TEXT,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 10. MENTORSHIP REQUESTS TABLE
CREATE TABLE IF NOT EXISTS `mentorship_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `mentee_name` VARCHAR(100) NOT NULL,
    `mentor_name` VARCHAR(100) NOT NULL,
    `mentor_id` INT DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `message` TEXT,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`mentor_id`) REFERENCES `mentors`(`id`) ON DELETE SET NULL
);

-- 11. NOTIFICATIONS TABLE
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` VARCHAR(50) DEFAULT 'general',
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT,
    `link` VARCHAR(500) DEFAULT NULL,
    `is_read` TINYINT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- 12. PASSWORD CHANGES TABLE
CREATE TABLE IF NOT EXISTS `password_changes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);