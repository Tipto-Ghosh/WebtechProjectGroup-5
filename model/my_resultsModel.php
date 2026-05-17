<?php
require_once __DIR__ . "/../config/dbConnection.php";

function getStudentResults(int $student_id)
{
    $connection = get_database_connection();

    $sql = "SELECT a.id, q.title, a.score, q.total_marks, FLOOR(TIMESTAMPDIFF(SECOND, a.started_at, a.completed_at) / 60) AS duration, a.completed_at FROM attempts a JOIN quizzes q ON q.id = a.quiz_id WHERE a.student_id = ? AND a.completed_at IS NOT NULL AND a.score IS NOT NULL ORDER BY a.completed_at DESC ";

    $statement= $connection->prepare($sql);
    $statement->bind_param("i", $student_id);
    $statement->execute();

    $result= $statement->get_result();
    $results= $result->fetch_all(MYSQLI_ASSOC);

    $statement->close();
    $connection->close();

    return $results;
}
?>
