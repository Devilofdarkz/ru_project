CREATE DATABASE IF NOT EXISTS ru_project;
USE ru_project;

-- Table for Users
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  roll_no VARCHAR(50) NOT NULL,
  password VARCHAR(100) NOT NULL,
  user_type ENUM('student','admin') DEFAULT 'student'
);

-- Table for Courses
DROP TABLE IF EXISTS courses;
CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_name VARCHAR(100) NOT NULL
);

-- Table for Files
DROP TABLE IF EXISTS files;
CREATE TABLE files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_type VARCHAR(10) NOT NULL,     -- 'pdf' or 'image'
  course_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('pending','approved') DEFAULT 'pending',
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert an initial admin user (password = admin123, no hashing as requested)
INSERT INTO users (name, roll_no, password, user_type) 
VALUES ('Admin User', 'ADMIN001', 'admin123', 'admin');

-- Insert some sample courses
INSERT INTO courses (course_name) VALUES ('BSc Computer Science');
INSERT INTO courses (course_name) VALUES ('BCom General');
INSERT INTO courses (course_name) VALUES ('BA English');
