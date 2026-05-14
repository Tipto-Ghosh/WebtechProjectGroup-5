<?php
require_once __DIR__ . "/../config/dbConnection.php";

function getLatestStudentAttemptId(int $student_id)
{
    $connection = get_database_connection();

    $sql = "SELECT id FROM attempts WHERE student_id = ? AND completed_at IS NOT NULL AND score IS NOT NULL ORDER BY completed_at DESC LIMIT 1
    ";

    $statement = $connection->prepare($sql);
    $statement->bind_param("i", $student_id);
    $statement->execute();

    $result = $statement->get_result();
    $row = $result->fetch_assoc();

    $statement->close();
    $connection->close();

    return $row ? (int)$row["id"] : 0;
}

function getAttemptById(int $attempt_id)
{
    $connection  = get_database_connection();

    $sql= "SELECT a.id, a.quiz_id, a.student_id, q.instructor_id, u.name AS student_name, q.title, a.score, q.total_marks, TIMESTAMPDIFF(MINUTE, a.started_at, a.completed_at) AS duration, a.started_at, a.completed_at FROM attempts a JOIN quizzes q ON q.id = a.quiz_id JOIN users u ON u.id = a.student_id WHERE a.id = ? AND a.completed_at IS NOT NULL AND a.score IS NOT NULL LIMIT 1 ";

    $statement   = $connection->prepare($sql);
    $statement->bind_param("i", $attempt_id);
    $statement->execute();

    $result= $statement->get_result();
    $attempt   = $result->fetch_assoc();

    $statement->close();
    $connection->close();

    return $attempt ?: [];
}

function getAnswerBreakdown(int $attempt_id)
{
    $connection = get_database_connection();

    $sql = "SELECT q.question_text, selected.option_text AS selected_answer, correct.option_text AS correct_answer, selected.is_correct, q.marks FROM answers ans JOIN questions q ON q.id = ans.question_id JOIN options selected ON selected.id = ans.selected_option_id JOIN options correct ON correct.question_id = q.id AND correct.is_correct = 1 WHERE ans.attempt_id = ? ORDER BY q.order_index ASC, q.id ASC ";

    $statement    = $connection->prepare($sql);
    $statement->bind_param("i", $attempt_id);
    $statement->execute();

    $result= $statement->get_result();
    $breakdown   = $result->fetch_all(MYSQLI_ASSOC);

    $statement->close();
    $connection->close();

    return $breakdown;
}
