-- Online Quiz/Test System Database Schema
-- Created for learning database operations and web app structure

-- Drop database if exists and create new one
DROP DATABASE IF EXISTS quiz_system;
CREATE DATABASE quiz_system;
USE quiz_system;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(50) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quizzes table
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_time DATETIME DEFAULT NULL, -- When the quiz starts
    duration INT DEFAULT NULL,        -- Duration in minutes
    difficulty ENUM('easy', 'hard') DEFAULT 'easy',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- If you are updating an existing database, run these:
-- ALTER TABLE quizzes ADD COLUMN start_time DATETIME DEFAULT NULL;
-- ALTER TABLE quizzes ADD COLUMN duration INT DEFAULT NULL;
-- ALTER TABLE quizzes ADD COLUMN difficulty ENUM('easy', 'hard') DEFAULT 'easy';

-- Questions table
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('A', 'B', 'C', 'D') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- User answers table
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option ENUM('A', 'B', 'C', 'D') NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Results table
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    attempt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Insert sample admin user with plain text password
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin User', 'admin@quiz.com', 'password', TRUE);

-- Insert sample quizzes
INSERT INTO quizzes (title, description) VALUES 
('General Knowledge', 'Test your general knowledge with these interesting questions'),
('Mathematics', 'Basic mathematics quiz covering arithmetic and algebra'),
('Science', 'Science quiz covering physics, chemistry, and biology');

-- Insert sample questions for General Knowledge quiz
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES
(1, 'What is the capital of France?', 'London', 'Berlin', 'Paris', 'Madrid', 'C'),
(1, 'Which planet is known as the Red Planet?', 'Venus', 'Mars', 'Jupiter', 'Saturn', 'B'),
(1, 'Who wrote "Romeo and Juliet"?', 'Charles Dickens', 'William Shakespeare', 'Jane Austen', 'Mark Twain', 'B'),
(1, 'What is the largest ocean on Earth?', 'Atlantic Ocean', 'Indian Ocean', 'Arctic Ocean', 'Pacific Ocean', 'D'),
(1, 'Which year did World War II end?', '1943', '1944', '1945', '1946', 'C');

-- Insert sample questions for Mathematics quiz
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES
(2, 'What is 15 + 27?', '40', '42', '43', '41', 'B'),
(2, 'What is 8 x 7?', '54', '56', '58', '60', 'B'),
(2, 'What is the square root of 64?', '6', '7', '8', '9', 'C'),
(2, 'What is 100 ÷ 4?', '20', '25', '30', '35', 'B'),
(2, 'What is 3² + 4²?', '7', '12', '25', '49', 'C');

-- Insert sample questions for Science quiz
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES
(3, 'What is the chemical symbol for gold?', 'Ag', 'Au', 'Fe', 'Cu', 'B'),
(3, 'What is the hardest natural substance on Earth?', 'Steel', 'Iron', 'Diamond', 'Granite', 'C'),
(3, 'What is the largest organ in the human body?', 'Heart', 'Brain', 'Liver', 'Skin', 'D'),
(3, 'What is the atomic number of carbon?', '4', '6', '8', '12', 'B'),
(3, 'What is the speed of light?', '299,792 km/s', '199,792 km/s', '399,792 km/s', '499,792 km/s', 'A'); 

-- Resources table for storing learning materials (simplified)
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    resource_url VARCHAR(500) NOT NULL
);

-- User-Resource access table (Many-to-Many relationship)
-- This tracks which users have accessed which resources




-- Insert sample resources
INSERT INTO resources (title, description, resource_url) VALUES
('Introduction to Mathematics', 'Basic concepts of algebra and arithmetic', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
('Physics Fundamentals', 'Understanding basic physics concepts', 'https://example.com/physics_basics.pdf'),
('Chemistry Lab Safety', 'Important safety guidelines for chemistry experiments', 'https://www.youtube.com/watch?v=example2'),
('Biology Study Guide', 'Comprehensive biology study materials', 'https://example.com/biology_guide.pdf');

-- If you are updating an existing database, run these SQL statements:
-- ALTER TABLE resources CHANGE COLUMN url resource_url VARCHAR(500) NOT NULL;
-- ALTER TABLE resources DROP FOREIGN KEY resources_ibfk_1; -- only if exists
-- ALTER TABLE resources DROP INDEX idx_uploaded_by; -- only if exists
-- ALTER TABLE resources DROP COLUMN uploaded_by;
-- ALTER TABLE resources DROP COLUMN created_at;