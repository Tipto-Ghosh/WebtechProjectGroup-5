<?php
require_once __DIR__ . "/../config/dbConnection.php";

function getInstructorQuizzes(int $instructor_id): array
{
    $connection = get_database_connection();

    $sql= "SELECT q.id, q.title, q.total_marks, q.status, COUNT(a.id) AS attempt_count " ."FROM quizzes q " . "LEFT JOIN attempts a ON a.quiz_id = q.id " . "AND a.completed_at IS NOT NULL " . "AND a.score IS NOT NULL " . "WHERE q.instructor_id=" . (int)$instructor_id . " " . "GROUP BY q.id, q.title, q.total_marks, q.status, q.created_at " . "ORDER BY q.created_at DESC";

    $result   = $connection->query($sql);
    $quizzes= $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $connection->close();

    return $quizzes;
}

function getQuizAttemptsForInstructor(int $quiz_id, int $instructor_id): array
{
    $connection= get_database_connection();

    $sql= "SELECT a.id AS attempt_id, a.student_id, u.name AS student_name, a.score, q.total_marks, " . "TIMESTAMPDIFF(MINUTE, a.started_at, a.completed_at) AS duration, a.completed_at " . "FROM attempts a " . "JOIN users u ON u.id = a.student_id " . "JOIN quizzes q ON q.id = a.quiz_id " . "WHERE a.quiz_id=" . (int)$quiz_id . " " . "AND q.instructor_id=" . (int)$instructor_id . " " . "AND a.completed_at IS NOT NULL " . "AND a.score IS NOT NULL " . "ORDER BY a.completed_at DESC";

    $result= $connection->query($sql);
    $attempts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $connection->close();

    return $attempts;
}
