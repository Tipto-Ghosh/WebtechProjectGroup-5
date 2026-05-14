<?php

include "../config/dbConnection.php";

class quizTakeDb{
    
function get_quiz_info($attemptId) {
    $connection = get_database_connection();
    $quizId = $connection->query("SELECT quiz_id FROM attempts WHERE id = '".$attemptId."'");
    $title = $connection->query("SELECT title FROM quiz WHERE id = '".$quizId."'");
    $instructorId = $connection->query("SELECT instructor_id FROM quiz WHERE id = '".$quizId."'");
    $instructorName = $connection->query("SELECT name FROM users WHERE id = '".$instructorId."'");
    $marks = $connection->query("SELECT total_marks FROM quizzes WHERE id = '".$quizId."'");

    $result = ["quizId"=>$quizId,"title"=>$title, "instructor"=>$instructorName, "totalMarks"=>$marks];
    return $result;
}

function get_question_with_options($questionId) {
    $conn = get_database_connection();

    $sql = "SELECT q.id AS question_id, q.question_text, q.marks,
                   o.id AS option_id, o.option_text, o.is_correct
            FROM questions q
            JOIN options o ON q.id = o.question_id
            WHERE q.id = ?
            ORDER BY o.id";
    $result = $conn->query($sql);

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



/*function get_quiz_questions($quizId) {
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
*/
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
}
?>