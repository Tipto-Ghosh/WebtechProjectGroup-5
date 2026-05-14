<?php
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

include __DIR__ . "/../model/quizTakeModel.php";

$attemptId= "";
$quizId = "";
$totalMarks = "";
$score = "";
$title = "";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $attemptId = intval($_GET['attempt']);

    $db = new quizTakeDb();
    $quiz = $db->get_quiz_info($attemptId);
    $quizId = $quiz['quizId'];
    $questions = $db->get_quiz_questions($quizId);
    $quizData = [
        "quizTitle" => $quiz['title'],
        "instructor"=> $quiz['instructor'],
        "totalMarks"=> $quiz['totalMarks'],
        "questions" => $questions
    ];

    header('Content-Type: application/json');
    echo json_encode($quizData);
}

?>