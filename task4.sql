-- ============================================================
--  quiz_platform — Complete Database Setup
--  Project 05: Online Quiz & Exam Platform
--  Web Technologies Lab Exam
--
--  HOW TO RUN:
--    phpMyAdmin : Database → SQL tab → paste all → Go
--    MySQL CLI  : mysql -u root -p < quiz_platform_database.sql
--
--  Safe to re-run: drops all tables first, then recreates.
--
--  Seed credentials (BCrypt, cost 10):
--    admin@quiz.com        password: Admin@1234
--    alice@instructor.com  password: Pass@1234
--    bob@instructor.com    password: Pass@1234
--    carol@student.com     password: Pass@1234
--    david@student.com     password: Pass@1234
--    eve@student.com       password: Pass@1234
--    frank@student.com     password: Pass@1234  (suspended)
--
--  !! Before going live, regenerate hashes in PHP:
--     echo password_hash('Admin@1234', PASSWORD_BCRYPT);
-- ============================================================


-- ──────────────────────────────────────────────────────────
--  SECTION 0 — Database
-- ──────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS quiz_platform
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE quiz_platform;


-- ──────────────────────────────────────────────────────────
--  SECTION 1 — Drop all tables (reverse FK dependency order)
-- ──────────────────────────────────────────────────────────

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS attempts;
DROP TABLE IF EXISTS options;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;


-- ──────────────────────────────────────────────────────────
--  SECTION 2 — users
--  Used by: ALL tasks
-- ──────────────────────────────────────────────────────────

CREATE TABLE users (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('student','instructor','admin')
                                NOT NULL DEFAULT 'student',
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- id            -> stored as $_SESSION['user_id'] on login
-- email         -> UNIQUE; INSERT violation fires PDOException code 23000
-- password_hash -> password_hash($plain, PASSWORD_BCRYPT)
-- role          -> drives dashboard redirect and requireRole() gate
-- is_active     -> 0 = suspended; login blocked in AuthController::login()


-- ──────────────────────────────────────────────────────────
--  SECTION 3 — quizzes
--  Written by Task 2 | Read by Task 3, Task 4
-- ──────────────────────────────────────────────────────────

CREATE TABLE quizzes (
    id                 INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    instructor_id      INT UNSIGNED  NOT NULL,
    title              VARCHAR(200)  NOT NULL,
    description        TEXT,
    total_marks        INT UNSIGNED  NOT NULL DEFAULT 0,
    time_limit_minutes INT UNSIGNED  NOT NULL DEFAULT 30,
    status             ENUM('draft','published')
                                     NOT NULL DEFAULT 'draft',
    created_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_quiz_instructor
        FOREIGN KEY (instructor_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- instructor_id      -> ownership checked in QuizBuilderController before edit/delete
-- total_marks        -> recomputed by Quiz::recomputeMarks() after every question change
-- time_limit_minutes -> embedded as data-time-limit attribute in take_quiz.php for JS timer
-- status             -> Task 3 quiz listing: WHERE status = 'published'


-- ──────────────────────────────────────────────────────────
--  SECTION 4 — questions
--  Written by Task 2 | Read by Task 3, Task 4
-- ──────────────────────────────────────────────────────────

CREATE TABLE questions (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    quiz_id       INT UNSIGNED  NOT NULL,
    question_text TEXT          NOT NULL,
    marks         INT UNSIGNED  NOT NULL DEFAULT 1,
    order_index   INT UNSIGNED  NOT NULL DEFAULT 0,

    CONSTRAINT fk_question_quiz
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- order_index -> rendering order in take_quiz.php (ORDER BY order_index)
-- marks       -> added to score in Answer::gradeAndSave() when selected option is correct


-- ──────────────────────────────────────────────────────────
--  SECTION 5 — options
--  Written by Task 2 | Read by Task 3, Task 4
-- ──────────────────────────────────────────────────────────

CREATE TABLE options (
    id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    question_id  INT UNSIGNED  NOT NULL,
    option_text  TEXT          NOT NULL,
    is_correct   TINYINT(1)    NOT NULL DEFAULT 0,

    CONSTRAINT fk_option_question
        FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exactly 4 rows per question (Option A-D); exactly one is_correct = 1
-- Grading: SELECT is_correct FROM options WHERE id = :selected_option_id


-- ──────────────────────────────────────────────────────────
--  SECTION 6 — attempts
--  Written by Task 3 | Read by Task 4
-- ──────────────────────────────────────────────────────────

CREATE TABLE attempts (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    quiz_id       INT UNSIGNED NOT NULL,
    student_id    INT UNSIGNED NOT NULL,

    score         INT DEFAULT NULL,   -- NULL = in-progress

    started_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    completed_at  TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_attempt_quiz
        FOREIGN KEY (quiz_id)
        REFERENCES quizzes(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_attempt_student
        FOREIGN KEY (student_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_student_quiz
        UNIQUE KEY (student_id, quiz_id)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
-- score = NULL AND completed_at = NULL -> attempt in progress
-- UNIQUE (student_id, quiz_id)         -> server-side re-attempt block (Task 3 requirement)
-- SUM(score) GROUP BY student_id       -> leaderboard aggregation (Task 4)


-- ──────────────────────────────────────────────────────────
--  SECTION 7 — answers
--  Written by Task 3 | Read by Task 4
-- ──────────────────────────────────────────────────────────

CREATE TABLE answers (
    id                 INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    attempt_id         INT UNSIGNED  NOT NULL,
    question_id        INT UNSIGNED  NOT NULL,
    selected_option_id INT UNSIGNED  NOT NULL,

    CONSTRAINT fk_answer_attempt
        FOREIGN KEY (attempt_id) REFERENCES attempts(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_answer_question
        FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_answer_option
        FOREIGN KEY (selected_option_id) REFERENCES options(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per question per attempt.
-- Task 4 result.php JOINs answers -> options.is_correct for green/red row highlights.


-- ============================================================
--  SECTION 8 — SEED DATA
--
--  AUTO_INCREMENT option IDs (sequential, no gaps):
--
--    Q1 options: ids  1- 4 | correct = id  2  (<nav>)
--    Q2 options: ids  5- 8 | correct = id  7  (padding)
--    Q3 options: ids  9-12 | correct = id 10  (doc type)
--    Q4 options: ids 13-16 | correct = id 16  (required)
--    Q5 options: ids 17-20 | correct = id 19  (flex)
--    Q6 options: ids 21-24 | correct = id 22  (res.json())
--    Q7 options: ids 25-28 | correct = id 26  (DOMContentLoaded)
--    Q8 options: ids 29-32 | correct = id 30  (Content-Type)
--
--  Every selected_option_id in the answers section below
--  was cross-verified against this map.
-- ============================================================


-- ──────────────────────────────────────────────────────────
--  8.1 — Users
--  IDs by insertion order:
--    1 = Admin
--    2 = Alice Rahman   (instructor)
--    3 = Bob Hossain    (instructor)
--    4 = Carol Ahmed    (student)
--    5 = David Islam    (student)
--    6 = Eve Chowdhury  (student)
--    7 = Frank Karim    (student, is_active=0 SUSPENDED)
-- ──────────────────────────────────────────────────────────
INSERT INTO users (name, email, password_hash, role, is_active) VALUES
('Admin User', 'admin@quizplatform.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('Alice Rahman', 'alice@instructor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 1),
('Bob Hossain', 'bob@instructor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 1),
('Carol Ahmed', 'carol@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1),
('David Islam', 'david@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1),
('Eve Chowdhury', 'eve@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1),
('Frank Karim', 'frank@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 0);

-- Note: All passwords are 'password' (default test password)


-- ──────────────────────────────────────────────────────────
--  8.2 — Quizzes Insertion
-- ──────────────────────────────────────────────────────────

INSERT INTO quizzes (instructor_id, title, description, total_marks, time_limit_minutes, status) VALUES
(2, 'HTML & CSS Fundamentals', 'Test your knowledge of HTML5 and CSS3 basics', 10, 30, 'published'),
(2, 'Advanced PHP Concepts', 'OOP, PDO, sessions, and MVC concepts', 0, 30, 'draft'),
(3, 'JavaScript & AJAX Essentials', 'Event handling, fetch API, JSON parsing, and DOM manipulation', 15, 25, 'published');


-- ──────────────────────────────────────────────────────────
--  8.3 — Questions Insertion
-- ──────────────────────────────────────────────────────────

-- Quiz 1: HTML & CSS Fundamentals (5 questions)
INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(1, 'Which HTML5 element is used to define navigation links?', 2, 1),
(1, 'Which CSS property controls the space between an element\'s border and its content?', 2, 2),
(1, 'What does the HTML DOCTYPE declaration specify?', 2, 3),
(1, 'Which attribute makes an input field mandatory before form submission?', 2, 4),
(1, 'Which CSS display value arranges children in a one-dimensional row or column?', 2, 5);

-- Quiz 3: JavaScript & AJAX Essentials (3 questions)
INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(3, 'Which method converts a Fetch API response body into a JavaScript object?', 5, 1),
(3, 'Which JavaScript event fires when the DOM is fully parsed but before images are loaded?', 5, 2),
(3, 'In a fetch() POST request, which header must be set when sending JSON?', 5, 3);


-- ──────────────────────────────────────────────────────────
--  8.4 — Options Insertion
-- ──────────────────────────────────────────────────────────

-- Question 1 options (question_id = 1)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(1, '<header>', 0),
(1, '<nav>', 1),      -- CORRECT
(1, '<section>', 0),
(1, '<article>', 0);

-- Question 2 options (question_id = 2)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(2, 'margin', 0),
(2, 'border', 0),
(2, 'padding', 1),    -- CORRECT
(2, 'spacing', 0);

-- Question 3 options (question_id = 3)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(3, 'The character encoding of the document', 0),
(3, 'The document type and HTML version', 1),  -- CORRECT
(3, 'The default stylesheet for the browser', 0),
(3, 'The entry-point script for the page', 0);

-- Question 4 options (question_id = 4)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(4, 'placeholder', 0),
(4, 'disabled', 0),
(4, 'readonly', 0),
(4, 'required', 1);   -- CORRECT

-- Question 5 options (question_id = 5)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(5, 'block', 0),
(5, 'inline', 0),
(5, 'flex', 1),       -- CORRECT
(5, 'grid', 0);

-- Question 6 options (question_id = 6)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(6, 'res.text()', 0),
(6, 'res.json()', 1), -- CORRECT
(6, 'res.parse()', 0),
(6, 'res.body()', 0);

-- Question 7 options (question_id = 7)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(7, 'window.onload', 0),
(7, 'DOMContentLoaded', 1),  -- CORRECT
(7, 'document.onready', 0),
(7, 'load', 0);

-- Question 8 options (question_id = 8)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(8, 'Accept: application/json', 0),
(8, 'Content-Type: application/json', 1),  -- CORRECT
(8, 'Authorization: Bearer json', 0),
(8, 'X-Requested-With: XMLHttpRequest', 0);


-- ──────────────────────────────────────────────────────────
--  8.5 — Attempts Insertion
-- ──────────────────────────────────────────────────────────

-- Attempt 1: Carol Ahmed takes Quiz 1 (Score: 10/10)
INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(1, 4, 10, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 2 DAY), INTERVAL 14 MINUTE));

-- Attempt 2: David Islam takes Quiz 1 (Score: 8/10)
INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(1, 5, 8, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 3 DAY), INTERVAL 17 MINUTE));

-- Attempt 3: Eve Chowdhury takes Quiz 1 (Score: 6/10)
INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(1, 6, 6, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 19 MINUTE));

-- Attempt 4: Carol Ahmed takes Quiz 3 (Score: 15/15)
INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(3, 4, 15, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 11 MINUTE));

-- Attempt 5: David Islam takes Quiz 3 (Score: 10/15)
INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(3, 5, 10, DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_ADD(DATE_SUB(NOW(), INTERVAL 5 HOUR), INTERVAL 22 MINUTE));


-- ──────────────────────────────────────────────────────────
--  8.6 — Answers Insertion
-- ──────────────────────────────────────────────────────────

-- Attempt 1 Answers (Carol - Quiz 1 - All correct)
INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(1, 1, 2),   -- Q1: <nav> ✓
(1, 2, 7),   -- Q2: padding ✓
(1, 3, 10),  -- Q3: document type ✓
(1, 4, 16),  -- Q4: required ✓
(1, 5, 19);  -- Q5: flex ✓

-- Attempt 2 Answers (David - Quiz 1 - 4 correct, 1 wrong)
INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(2, 1, 2),   -- Q1: <nav> ✓
(2, 2, 7),   -- Q2: padding ✓
(2, 3, 9),   -- Q3: charset ✗ (wrong)
(2, 4, 16),  -- Q4: required ✓
(2, 5, 19);  -- Q5: flex ✓

-- Attempt 3 Answers (Eve - Quiz 1 - 3 correct, 2 wrong)
INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(3, 1, 1),   -- Q1: <header> ✗ (wrong)
(3, 2, 7),   -- Q2: padding ✓
(3, 3, 10),  -- Q3: document type ✓
(3, 4, 14),  -- Q4: disabled ✗ (wrong)
(3, 5, 19);  -- Q5: flex ✓

-- Attempt 4 Answers (Carol - Quiz 3 - All correct)
INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(4, 6, 22),  -- Q6: res.json() ✓
(4, 7, 26),  -- Q7: DOMContentLoaded ✓
(4, 8, 30);  -- Q8: Content-Type ✓

-- Attempt 5 Answers (David - Quiz 3 - 2 correct, 1 wrong)
INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(5, 6, 22),  -- Q6: res.json() ✓
(5, 7, 25),  -- Q7: window.onload ✗ (wrong)
(5, 8, 30);  -- Q8: Content-Type ✓ 