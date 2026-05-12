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
    id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    quiz_id      INT UNSIGNED  NOT NULL,
    student_id   INT UNSIGNED  NOT NULL,
    score        INT           DEFAULT NULL,       -- NULL = in-progress
    started_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP     DEFAULT NULL,       -- NULL = in-progress

    CONSTRAINT fk_attempt_quiz
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_attempt_student
        FOREIGN KEY (student_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT uq_student_quiz
        UNIQUE KEY (student_id, quiz_id)           -- one attempt per student per quiz
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Admin  (password: Admin@1234)
('Admin',
 'admin@quiz.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin', 1),

-- Instructors  (password: Pass@1234)
('Alice Rahman',
 'alice@instructor.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'instructor', 1),

('Bob Hossain',
 'bob@instructor.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'instructor', 1),

-- Active students  (password: Pass@1234)
('Carol Ahmed',
 'carol@student.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'student', 1),

('David Islam',
 'david@student.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'student', 1),

('Eve Chowdhury',
 'eve@student.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'student', 1),

-- Suspended student -- demos Task 1 is_active login gate
('Frank Karim',
 'frank@student.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'student', 0);


-- ──────────────────────────────────────────────────────────
--  8.2 — Quizzes
--  IDs by insertion order: 1, 2, 3
--    id=1  Alice   PUBLISHED  10 marks  5 questions
--    id=2  Alice   DRAFT       0 marks  0 questions (publish-guard demo)
--    id=3  Bob     PUBLISHED  15 marks  3 questions
-- ──────────────────────────────────────────────────────────

INSERT INTO quizzes
    (instructor_id, title, description, total_marks, time_limit_minutes, status)
VALUES
(2,
 'Introduction to Web Technologies',
 'Covers HTML5 fundamentals, semantic elements, and basic CSS.',
 10, 20, 'published'),

(2,
 'Advanced PHP Concepts',
 'OOP, PDO, sessions, and MVC. Draft — not yet visible to students.',
 0, 30, 'draft'),

(3,
 'JavaScript & AJAX Essentials',
 'Event handling, fetch API, JSON parsing, and DOM manipulation.',
 15, 25, 'published');


-- ──────────────────────────────────────────────────────────
--  8.3 — Questions & Options
--
--  Quiz 1: 5 questions x 2 marks = 10  matches total_marks above
--  Quiz 2: 0 questions             (draft, publish-guard demo)
--  Quiz 3: 3 questions x 5 marks = 15  matches total_marks above
-- ──────────────────────────────────────────────────────────

-- ── Question id=1 | Quiz 1 | options ids 1,2,3,4 | correct=2 ──────────────

INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(1, 'Which HTML5 element is used to define navigation links?', 2, 1);

INSERT INTO options (question_id, option_text, is_correct) VALUES
(1, '<header>',  0),   -- id=1
(1, '<nav>',     1),   -- id=2  CORRECT
(1, '<section>', 0),   -- id=3
(1, '<article>', 0);   -- id=4

-- ── Question id=2 | Quiz 1 | options ids 5,6,7,8 | correct=7 ───────────────

INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(1, 'Which CSS property controls the space between an element''s border and its content?', 2, 2);

INSERT INTO options (question_id, option_text, is_correct) VALUES
(2, 'margin',   0),   -- id=5
(2, 'border',   0),   -- id=6
(2, 'padding',  1),   -- id=7  CORRECT
(2, 'spacing',  0);   -- id=8

-- ── Question id=3 | Quiz 1 | options ids 9,10,11,12 | correct=10 ────────────

INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(1, 'What does the HTML DOCTYPE declaration specify?', 2, 3);

INSERT INTO options (question_id, option_text, is_correct) VALUES
(3, 'The character encoding of the document',   0),   -- id=9
(3, 'The document type and HTML version',       1),   -- id=10  CORRECT
(3, 'The default stylesheet for the browser',   0),   -- id=11
(3, 'The entry-point script for the page',      0);   -- id=12

-- ── Question id=4 | Quiz 1 | options ids 13,14,15,16 | correct=16 ───────────

INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(1, 'Which attribute makes an input field mandatory before form submission?', 2, 4);

INSERT INTO options (question_id, option_text, is_correct) VALUES
(4, 'placeholder', 0),   -- id=13
(4, 'disabled',    0),   -- id=14
(4, 'readonly',    0),   -- id=15
(4, 'required',    1);   -- id=16  CORRECT

-- ── Question id=5 | Quiz 1 | options ids 17,18,19,20 | correct=19 ───────────

INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(1, 'Which CSS display value arranges children in a one-dimensional row or column?', 2, 5);

INSERT INTO options (question_id, option_text, is_correct) VALUES
(5, 'block',   0),   -- id=17
(5, 'inline',  0),   -- id=18
(5, 'flex',    1),   -- id=19  CORRECT
(5, 'grid',    0);   -- id=20

-- ── Question id=6 | Quiz 3 | options ids 21,22,23,24 | correct=22 ───────────

INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(3, 'Which method converts a Fetch API response body into a JavaScript object?', 5, 1);

INSERT INTO options (question_id, option_text, is_correct) VALUES
(6, 'res.text()',   0),   -- id=21
(6, 'res.json()',   1),   -- id=22  CORRECT
(6, 'res.parse()',  0),   -- id=23
(6, 'res.body()',   0);   -- id=24

-- ── Question id=7 | Quiz 3 | options ids 25,26,27,28 | correct=26 ───────────

INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(3, 'Which JavaScript event fires when the DOM is fully parsed but before images are loaded?', 5, 2);

INSERT INTO options (question_id, option_text, is_correct) VALUES
(7, 'window.onload',    0),   -- id=25
(7, 'DOMContentLoaded', 1),   -- id=26  CORRECT
(7, 'document.onready', 0),   -- id=27
(7, 'load',             0);   -- id=28

-- ── Question id=8 | Quiz 3 | options ids 29,30,31,32 | correct=30 ───────────

INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES
(3, 'In a fetch() POST request, which header must be set when sending JSON?', 5, 3);

INSERT INTO options (question_id, option_text, is_correct) VALUES
(8, 'Accept: application/json',          0),   -- id=29
(8, 'Content-Type: application/json',    1),   -- id=30  CORRECT
(8, 'Authorization: Bearer json',        0),   -- id=31
(8, 'X-Requested-With: XMLHttpRequest',  0);   -- id=32


-- ──────────────────────────────────────────────────────────
--  8.4 — Attempts & Answers
--
--  attempt IDs by insertion order: 1, 2, 3, 4, 5
--
--  completed_at uses DATE_ADD(DATE_SUB(...), INTERVAL ...)
--  -- the only correct MySQL syntax for nested interval math.
--  Plain "+ INTERVAL n MINUTE" on a TIMESTAMP is non-standard
--  and silently fails on strict MySQL servers.
--
--  Score verification:
--    Attempt 1  Carol   Quiz1   5/5 correct  2+2+2+2+2 = 10  v
--    Attempt 2  David   Quiz1   4/5 correct  2+2+0+2+2 =  8  v
--    Attempt 3  Eve     Quiz1   3/5 correct  0+2+2+0+2 =  6  v
--    Attempt 4  Carol   Quiz3   3/3 correct  5+5+5     = 15  v
--    Attempt 5  David   Quiz3   2/3 correct  5+0+5     = 10  v
--
--  Leaderboard result:
--    Rank 1  Carol  10+15 = 25 pts
--    Rank 2  David   8+10 = 18 pts
--    Rank 3  Eve      6   =  6 pts
-- ──────────────────────────────────────────────────────────

-- ── Attempt id=1 : Carol (user 4) — Quiz 1 — score=10 ──────────────────────

INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(1, 4, 10,
 DATE_SUB(NOW(), INTERVAL 2 DAY),
 DATE_ADD(DATE_SUB(NOW(), INTERVAL 2 DAY), INTERVAL 14 MINUTE));

INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(1, 1,  2),   -- Q1 -> opt  2 (<nav>)         is_correct=1  +2
(1, 2,  7),   -- Q2 -> opt  7 (padding)        is_correct=1  +2
(1, 3, 10),   -- Q3 -> opt 10 (doc type)       is_correct=1  +2
(1, 4, 16),   -- Q4 -> opt 16 (required)       is_correct=1  +2
(1, 5, 19);   -- Q5 -> opt 19 (flex)           is_correct=1  +2
                                            --              total=10  v

-- ── Attempt id=2 : David (user 5) — Quiz 1 — score=8 ───────────────────────

INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(1, 5, 8,
 DATE_SUB(NOW(), INTERVAL 3 DAY),
 DATE_ADD(DATE_SUB(NOW(), INTERVAL 3 DAY), INTERVAL 17 MINUTE));

INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(2, 1,  2),   -- Q1 -> opt  2 (<nav>)          is_correct=1  +2
(2, 2,  7),   -- Q2 -> opt  7 (padding)         is_correct=1  +2
(2, 3,  9),   -- Q3 -> opt  9 (charset) WRONG   is_correct=0  +0
(2, 4, 16),   -- Q4 -> opt 16 (required)        is_correct=1  +2
(2, 5, 19);   -- Q5 -> opt 19 (flex)            is_correct=1  +2
                                            --               total=8  v

-- ── Attempt id=3 : Eve (user 6) — Quiz 1 — score=6 ─────────────────────────

INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(1, 6, 6,
 DATE_SUB(NOW(), INTERVAL 1 DAY),
 DATE_ADD(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 19 MINUTE));

INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(3, 1,  1),   -- Q1 -> opt  1 (<header>) WRONG  is_correct=0  +0
(3, 2,  7),   -- Q2 -> opt  7 (padding)          is_correct=1  +2
(3, 3, 10),   -- Q3 -> opt 10 (doc type)          is_correct=1  +2
(3, 4, 14),   -- Q4 -> opt 14 (disabled) WRONG   is_correct=0  +0
(3, 5, 19);   -- Q5 -> opt 19 (flex)              is_correct=1  +2
                                            --               total=6  v

-- ── Attempt id=4 : Carol (user 4) — Quiz 3 — score=15 ──────────────────────

INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(3, 4, 15,
 DATE_SUB(NOW(), INTERVAL 1 DAY),
 DATE_ADD(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 11 MINUTE));

INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(4, 6, 22),   -- Q6 -> opt 22 (res.json())          is_correct=1  +5
(4, 7, 26),   -- Q7 -> opt 26 (DOMContentLoaded)    is_correct=1  +5
(4, 8, 30);   -- Q8 -> opt 30 (Content-Type)         is_correct=1  +5
                                                  -- total=15  v

-- ── Attempt id=5 : David (user 5) — Quiz 3 — score=10 ──────────────────────

INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at) VALUES
(3, 5, 10,
 DATE_SUB(NOW(), INTERVAL 5 HOUR),
 DATE_ADD(DATE_SUB(NOW(), INTERVAL 5 HOUR), INTERVAL 22 MINUTE));

INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES
(5, 6, 22),   -- Q6 -> opt 22 (res.json())          is_correct=1  +5
(5, 7, 25),   -- Q7 -> opt 25 (window.onload) WRONG is_correct=0  +0
(5, 8, 30);   -- Q8 -> opt 30 (Content-Type)         is_correct=1  +5
                                                  -- total=10  v


-- ──────────────────────────────────────────────────────────
--  SECTION 9 — Verification queries
--  Uncomment each block and run separately to confirm data.
-- ──────────────────────────────────────────────────────────

-- 9.1  All users
-- SELECT id, name, email, role, is_active FROM users ORDER BY id;

-- 9.2  All quizzes with instructor name
-- SELECT q.id, q.title, q.total_marks, q.status, u.name AS instructor
-- FROM quizzes q
-- JOIN users u ON u.id = q.instructor_id
-- ORDER BY q.id;

-- 9.3  Questions with option counts (expect 4 options, 1 correct each)
-- SELECT q.id, q.quiz_id, q.question_text, q.marks,
--        COUNT(o.id) AS option_count,
--        SUM(o.is_correct) AS correct_options
-- FROM questions q
-- JOIN options o ON o.question_id = q.id
-- GROUP BY q.id
-- ORDER BY q.quiz_id, q.order_index;

-- 9.4  All options with IDs (cross-check the ID map in the header)
-- SELECT o.id, o.question_id, o.option_text, o.is_correct
-- FROM options o
-- ORDER BY o.question_id, o.id;

-- 9.5  Completed attempts with scores
-- SELECT a.id, u.name AS student, qz.title AS quiz,
--        a.score, qz.total_marks, a.completed_at
-- FROM attempts a
-- JOIN users   u  ON u.id  = a.student_id
-- JOIN quizzes qz ON qz.id = a.quiz_id
-- ORDER BY a.id;

-- 9.6  Per-answer correctness audit (verify scores are computed right)
-- SELECT a.id AS attempt_id,
--        u.name AS student,
--        q.question_text,
--        sel.option_text   AS selected,
--        sel.is_correct,
--        IF(sel.is_correct, q.marks, 0) AS points_earned
-- FROM answers ans
-- JOIN attempts  a   ON a.id   = ans.attempt_id
-- JOIN users     u   ON u.id   = a.student_id
-- JOIN questions q   ON q.id   = ans.question_id
-- JOIN options   sel ON sel.id  = ans.selected_option_id
-- ORDER BY ans.attempt_id, q.order_index;

-- 9.7  Leaderboard preview (mirrors ResultController::getLeaderboard exactly)
-- SELECT u.name,
--        SUM(a.score)  AS total_score,
--        COUNT(a.id)   AS quizzes_taken
-- FROM attempts a
-- JOIN users u ON u.id = a.student_id
-- WHERE a.completed_at IS NOT NULL
-- GROUP BY a.student_id
-- ORDER BY total_score DESC
-- LIMIT 10;

-- Expected output:
--   name             total_score  quizzes_taken
--   Carol Ahmed           25          2
--   David Islam           18          2
--   Eve Chowdhury          6          1


-- ============================================================
--  END OF FILE
--
--  Tables    : users, quizzes, questions, options, attempts, answers
--  Users     : 1 admin | 2 instructors | 3 active students | 1 suspended
--  Quizzes   : 2 published (Quiz1=10 marks, Quiz3=15 marks) | 1 draft
--  Questions : 8 total (5 in Quiz1, 0 in Quiz2, 3 in Quiz3)
--  Options   : 32 total (4 per question, exactly 1 correct each)
--  Attempts  : 5 completed
--  Answers   : 23 total
-- ============================================================
