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
?>
