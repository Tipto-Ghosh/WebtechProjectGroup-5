<?php
include "../config/dbConnection.php";

function get_quiz_id($attemptId) {
    $connection = get_database_connection();
    $sql = "SELECT quiz_id FROM attempts WHERE id = '".$attemptId."'";
    $result = $connection->query($sql);
    return $result;
}

function get_quiz($quizId) {
    $connection = get_database_connection();
    $sql ="SELECT q.id AS question_id, q.question_text, q.marks,
                   o.id AS option_id, o.option_text, o.is_correct
            FROM questions q
            JOIN options o ON q.id = o.question_id
            WHERE q.quiz_id = ?
            ORDER BY q.order_index";
    $quiz = $connection->query($sql);
    $result = $quiz->fetch_all(MYSQLI_ASSOC);
    return $result;
}

function get_Instructor($quizId) {
    $connection = get_database_connection();
    $sql = "SELECT instructor_id FROM quizzes WHERE id =".$quizId."'";
    $instructorId = $connection->query($sql);
    $sql = "SELECT name FROM users WHERE id =".$instructorId."'";
    $result = $connection->query($sql);
    return $result;
}

function get_questions_with_options($quizId) {
    $connection = get_database_connection();
    $sql = "SELECT q.id AS question_id, q.question_text, q.marks,
                   o.id AS option_id, o.option_text, o.is_correct
            FROM questions q
            JOIN options o ON q.id = o.question_id
            WHERE q.quiz_id = '".$quizId."'
            ORDER BY q.order_index";
    $quiz = $connection->query($sql);
    $result = $quiz->fetch_all(MYSQLI_ASSOC);
    return $result;
}

function update_attempts($attemptId, $score, $start, $end) {
    $connection = get_database_connection();
    $sql = "UPDATE attempts
    SET score = '".$score."',
    started_at = '".$start."',
    completed_at = '".$end."'
    WHERE id ='".$attemptId."'";
    $result = $connection->query($sql);
    return $result;
}

function insert_answers($attemptId, $quesID, $selectedID) {
    $connection = get_database_connection();
    $sql = "INSERT INTO answers (attempt_id, question_id, selected_option_id)VALUES ('".$attemptId."', '".$quesID."', '".$selectedID."')";
    $result = $connection->query($sql);
    return $result;
}

function getCorrectAnswerID($quesID) {
    $connection = get_database_connection();
    $sql = "SELECT id FROM options WHERE question_id = '".$quesID."' AND is_correct = 1";
}

?>