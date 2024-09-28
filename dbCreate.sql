CREATE DATABASE IF NOT EXISTS examPlan;
USE examPlan;

-- Fakülteleri Oluştur
CREATE TABLE IF NOT EXISTS faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Bölümleri Oluştur
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    faculty_id INT,
    FOREIGN KEY (faculty_id) REFERENCES faculties(id)
);

-- Kullanıcıları Oluştur
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('assistant', 'secretary', 'head_of_department', 'head_of_secretary', 'dean') NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Asistanları Oluştur
CREATE TABLE IF NOT EXISTS assistants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT,
    user_id INT,
    score INT DEFAULT 0,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Dersleri Oluştur
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Ders Saatlerini Oluştur
CREATE TABLE IF NOT EXISTS course_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Sınavları Oluştur
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    name VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    num_assistants INT NOT NULL,
    num_classes INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Sınav Atamalarını Oluştur
CREATE TABLE IF NOT EXISTS exam_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT,
    assistant_id INT,
    FOREIGN KEY (exam_id) REFERENCES exams(id),
    FOREIGN KEY (assistant_id) REFERENCES assistants(id)
);

-- Asistan Derslerini Oluştur
CREATE TABLE IF NOT EXISTS assistant_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assistant_id INT,
    course_id INT,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Banlanan Dersleri Oluştur
CREATE TABLE IF NOT EXISTS banned_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assistant_id INT,
    course_id INT,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Haftalık Planı Oluştur
CREATE TABLE IF NOT EXISTS weekly_plan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assistant_id INT,
    course_name VARCHAR(100),
    exam_name VARCHAR(100),
    exam_date DATE,
    start_time TIME,
    end_time TIME,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id)
);

-- Fakülteleri Ekle
INSERT INTO faculties (name) VALUES ('Engineering Faculty');

-- Bölümleri Ekle
INSERT INTO departments (name, faculty_id) VALUES
('Computer Engineering', 1),
('Electrical Engineering', 1),
('Mechanical Engineering', 1);

-- Kullanıcıları Ekle
INSERT INTO users (username, password, role, department_id) VALUES
('assistant1', 'password123', 'assistant', 1),
('assistant2', 'password123', 'assistant', 1),
('assistant3', 'password123', 'assistant', 2),
('assistant4', 'password123', 'assistant', 3),
('secretary1', 'password123', 'secretary', 1),
('secretary2', 'password123', 'secretary', 2),
('secretary3', 'password123', 'secretary', 3),
('head_of_department1', 'password123', 'head_of_department', 1),
('head_of_department2', 'password123', 'head_of_department', 2),
('head_of_department3', 'password123', 'head_of_department', 3),
('head_of_secretary1', 'password123', 'head_of_secretary', NULL),
('dean1', 'password123', 'dean', NULL);

-- Asistanları Ekle
INSERT INTO assistants (name, department_id, user_id) VALUES
('Assistant One', 1, 1),
('Assistant Two', 1, 2),
('Assistant Three', 2, 3),
('Assistant Four', 3, 4);

-- Dersleri Ekle
INSERT INTO courses (name, department_id) VALUES
('CSE101', 1),
('CSE102', 1),
('ELE101', 2),
('ELE102', 2),
('MEC101', 3),
('MEC102', 3),
('ES224', 1),
('ES272', 1);

-- Ders Saatlerini Ekle
INSERT INTO course_schedules (course_id, day_of_week, start_time, end_time) VALUES
(1, 'Monday', '09:00:00', '10:00:00'),
(1, 'Wednesday', '09:00:00', '10:00:00'),
(2, 'Tuesday', '11:00:00', '12:00:00'),
(2, 'Thursday', '11:00:00', '12:00:00'),
(3, 'Monday', '13:00:00', '14:00:00'),
(3, 'Wednesday', '13:00:00', '14:00:00'),
(4, 'Tuesday', '15:00:00', '16:00:00'),
(4, 'Thursday', '15:00:00', '16:00:00'),
(5, 'Friday', '08:00:00', '09:00:00'),
(6, 'Friday', '10:00:00', '11:00:00'),
(7, 'Monday', '14:00:00', '15:00:00'),
(8, 'Wednesday', '14:00:00', '15:00:00');

-- Her ders için sınavları ekle
INSERT INTO exams (course_id, name, exam_date, start_time, end_time, num_assistants, num_classes) VALUES
(1, 'CSE101 Midterm', '2024-04-30', '18:00:00', '19:00:00', 2, 1),
(1, 'CSE101 Final', '2024-06-10', '14:00:00', '16:00:00', 2, 1),
(2, 'CSE102 Midterm', '2024-05-20', '09:00:00', '11:00:00', 2, 1),
(2, 'CSE102 Final', '2024-06-15', '10:00:00', '12:00:00', 2, 1),
(3, 'ELE101 Midterm', '2024-04-28', '13:00:00', '14:00:00', 1, 1),
(3, 'ELE101 Final', '2024-06-12', '11:00:00', '12:00:00', 1, 1),
(4, 'ELE102 Midterm', '2024-05-22', '14:00:00', '15:00:00', 1, 1),
(4, 'ELE102 Final', '2024-06-17', '15:00:00', '16:00:00', 1, 1),
(5, 'MEC101 Midterm', '2024-04-25', '08:00:00', '09:00:00', 1, 1),
(5, 'MEC101 Final', '2024-06-10', '10:00:00', '11:00:00', 1, 1),
(6, 'MEC102 Midterm', '2024-05-25', '12:00:00', '13:00:00', 1, 1),
(6, 'MEC102 Final', '2024-06-20', '14:00:00', '15:00:00', 1, 1),
(7, 'ES224 Midterm', '2024-04-22', '08:00:00', '10:00:00', 2, 1),
(8, 'ES272 Midterm', '2024-04-23', '08:00:00', '10:00:00', 2, 1);

-- Asistanları sınavlara ata
INSERT INTO exam_assignments (exam_id, assistant_id) VALUES
(1, 1), (1, 2),  -- CSE101 Midterm
(2, 1), (2, 2),  -- CSE101 Final
(3, 1), (3, 2),  -- CSE102 Midterm
(4, 1), (4, 2),  -- CSE102 Final
(5, 3),  -- ELE101 Midterm
(6, 3),  -- ELE101 Final
(7, 3),  -- ELE102 Midterm
(8, 3),  -- ELE102 Final
(9, 4),  -- MEC101 Midterm
(10, 4), -- MEC101 Final
(11, 4), -- MEC102 Midterm
(12, 4), -- MEC102 Final
(13, 1), (13, 2), -- ES224 Midterm
(14, 1), (14, 2); -- ES272 Midterm

-- Asistan derslerini doldur
INSERT INTO assistant_courses (assistant_id, course_id) VALUES
(1, 1), (1, 2), (1, 7), (1, 8),
(2, 1), (2, 2), (2, 7), (2, 8),
(3, 3), (3, 4),
(4, 5), (4, 6);

-- Banlanan dersler
INSERT INTO banned_courses (assistant_id, course_id) VALUES
(1, 2), (2, 1);

-- Haftalık Planı Temizle
DELETE FROM weekly_plan;

-- Haftalık Planı Doğru Şekilde Doldur
INSERT INTO weekly_plan (assistant_id, course_name, exam_name, exam_date, start_time, end_time) VALUES
(1, 'CSE101', 'CSE101 Midterm', '2024-04-30', '18:00:00', '19:00:00'),
(1, 'CSE102', 'CSE102 Midterm', '2024-05-20', '09:00:00', '11:00:00'),
(1, 'CSE102', 'CSE102 Final', '2024-06-15', '10:00:00', '12:00:00'),
(1, 'ES224', 'ES224 Midterm', '2024-04-22', '08:00:00', '10:00:00'),
(1, 'ES272', 'ES272 Midterm', '2024-04-23', '08:00:00', '10:00:00'),
(2, 'CSE101', 'CSE101 Midterm', '2024-04-30', '18:00:00', '19:00:00'),
(2, 'CSE102', 'CSE102 Midterm', '2024-05-20', '09:00:00', '11:00:00'),
(2, 'CSE102', 'CSE102 Final', '2024-06-15', '10:00:00', '12:00:00'),
(2, 'ES224', 'ES224 Midterm', '2024-04-22', '08:00:00', '10:00:00'),
(2, 'ES272', 'ES272 Midterm', '2024-04-23', '08:00:00', '10:00:00'),
(3, 'ELE101', 'ELE101 Midterm', '2024-04-28', '13:00:00', '14:00:00'),
(3, 'ELE101', 'ELE101 Final', '2024-06-12', '11:00:00', '12:00:00'),
(3, 'ELE102', 'ELE102 Midterm', '2024-05-22', '14:00:00', '15:00:00'),
(3, 'ELE102', 'ELE102 Final', '2024-06-17', '15:00:00', '16:00:00'),
(4, 'MEC101', 'MEC101 Midterm', '2024-04-25', '08:00:00', '09:00:00'),
(4, 'MEC101', 'MEC101 Final', '2024-06-10', '10:00:00', '11:00:00'),
(4, 'MEC102', 'MEC102 Midterm', '2024-05-25', '12:00:00', '13:00:00'),
(4, 'MEC102', 'MEC102 Final', '2024-06-20', '14:00:00', '15:00:00');

-- Asistan puanlarını güncelle
UPDATE assistants a
JOIN (
    SELECT assistant_id, COUNT(*) AS score
    FROM exam_assignments
    GROUP BY assistant_id
) ea ON a.id = ea.assistant_id
SET a.score = ea.score;
