<?php
include "../config/dbConnection.php";

function get_quizzes_with_attempts(int $student_id)
{
    $connection = get_database_connection();

    $sql = "SELECT 
                q.id AS quiz_id,
                q.title AS quiz_title,
                q.description,
                u.name AS instructor_name,
                q.total_marks,
                q.time_limit_minutes,
                q.created_at,
                CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END AS attempted,
                a.started_at,
                a.completed_at,
                a.score
            FROM quizzes q
            INNER JOIN users u 
                ON q.instructor_id = u.id
            LEFT JOIN attempts a 
                ON q.id = a.quiz_id 
               AND a.student_id = $student_id
            WHERE q.status = 'published'
            ORDER BY q.created_at DESC";

    $result = $connection->query($sql);
    $quizzes = $result->fetch_all(MYSQLI_ASSOC);

    return $quizzes;
}

function get_quiz_by_id(int $quizId) {
    $conn = get_database_connection();
    $sql = "SELECT * FROM quizzes WHERE id = '".$quizId."'";
    $quiz = $conn->query($sql);
    $result = $quiz->fetch_all(MYSQLI_ASSOC);
    return $result;
}

function has_attempted_quiz(int $studentId, int $quizId) {
    $conn = get_database_connection();
    $sql = "SELECT id FROM attempts WHERE quiz_id = '".$quizId."' AND student_id = '".$studentId."'";
    $qr = $conn->query($sql);
    $result = $qr->fetch_all(MYSQLI_ASSOC);
    return $result;
}

function create_attempt(int $studentId, int $quizId) {
    $conn = get_database_connection();
    $sql = "INSERT INTO attempts (quiz_id, student_id, started_at) VALUES ('".$quizId."', '".$studentId."', NOW())";
    $result = $conn->query($sql);
    return $conn->insert_id;
}

?>
