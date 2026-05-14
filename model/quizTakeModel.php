<?php

include "../config/dbConnection.php";

class quizTakeModel{
    
function get_quiz_info($attemptId) {
    $connection = get_database_connection();

    $quizIdRes = $connection->query("SELECT quiz_id FROM attempts WHERE id = '".$attemptId."'");
    $quizIdRow = $quizIdRes->fetch_assoc();
    $quizId = $quizIdRow['quiz_id'];

    $titleRes = $connection->query("SELECT title, instructor_id, total_marks FROM quizzes WHERE id = '".$quizId."'");
    $titleRow = $titleRes->fetch_assoc();
    $title = $titleRow['title'];
    $instructorId = $titleRow['instructor_id'];
    $marks = $titleRow['total_marks'];

    $instrRes = $connection->query("SELECT name FROM users WHERE id = '".$instructorId."'");
    $instrRow = $instrRes->fetch_assoc();
    $instructorName = $instrRow['name'];

    return [
        "quizId"     => $quizId,
        "title"      => $title,
        "instructor" => $instructorName,
        "totalMarks" => $marks
    ];
}

function get_question_with_options($questionId) {
    $conn = get_database_connection();

    $sql = "SELECT q.id AS question_id, q.question_text, q.marks,
                   o.id AS option_id, o.option_text, o.is_correct
            FROM questions q
            JOIN options o ON q.id = o.question_id
            WHERE q.id = ?
            ORDER BY o.id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();

    $question = null;

    while ($row = $result->fetch_assoc()) {
        if ($question === null) {
            $question = [
                "question_id"   => $row['question_id'],
                "question_text" => $row['question_text'],
                "marks"         => $row['marks'],
                "options"       => []
            ];
        }

        $question["options"][] = [
            "option_id"   => $row['option_id'],
            "option_text" => $row['option_text'],
            "is_correct"  => $row['is_correct']
        ];
    }

    return $question;
}


function get_all_questions($quizId) {
    $conn = get_database_connection();

    $sql = "SELECT id FROM questions WHERE quiz_id = ? ORDER BY order_index";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];

    while ($row = $result->fetch_assoc()) {
        $questionId = $row['id'];
        $questions[] = $this->get_question_with_options($questionId);
    }

    return $questions;
}

function get_student_id($attemptId) {
    $conn = get_database_connection();
    $sql = "SELECT * FROM attempts WHERE student_id = '".$attemptId."'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        return null;
    }

    return $row['student_id'];
}

function get_quiz($quizId) {
    $connection = get_database_connection();
    $sql = "SELECT * FROM quizzes WHERE id = '".$quizId."'";
    $result = $connection->query($sql);
    return $result->fetch_assoc();
}

function get_published_quizzes() {
    $connection = get_database_connection();
    $sql = "SELECT * FROM quizzes WHERE status='published'";
    $result = $connection->query($sql);
    $quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
    return $quizzes;
}
function get_questions_by_quiz($quizId) {
    $connection = get_database_connection();
    $sql = "SELECT * FROM questions WHERE quiz_id = '".$quizId."'";
    $result = $connection->query($sql);
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    return $questions;
}
function get_options_by_question($questionId) {
    $connection = get_database_connection();
    $sql = "SELECT * FROM options WHERE question_id = '".$questionId."'";
    $result = $connection->query($sql);
    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = $row;
    }
    return $options;
}

function get_option_with_marks($optionId) {
    $connection = get_database_connection();
    $sql = "SELECT o.is_correct, q.marks 
            FROM options o 
            JOIN questions q ON o.question_id = q.id 
            WHERE o.id = '".$optionId."'";
    $result = $connection->query($sql);
    return $result->fetch_assoc();
}

function get_attempt($attemptId) {
    $connection = get_database_connection();
    $sql = "SELECT * FROM attempts WHERE id = '".$attemptId."'";
    $result = $connection->query($sql);
    return $result->fetch_assoc();
}

function is_attempt_completed($attemptId) {
    $attempt = $this->get_attempt($attemptId);
    return ($attempt && $attempt['completed_at'] !== null);
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

function set_answers($attemptId, $questionId, $optionId)
{
    $connection = get_database_connection();
    $sql = "INSERT INTO answers (attempt_id, question_id, option_id) 
            VALUES ('".$attemptId."', '".$questionId."', '".$optionId."')";
    $result = $connection->query($sql);
    return $result;
}

function NOW()
{
    return date("Y-m-d H:i:s");
}

}
?>