<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../model/quizTakeModel.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $attemptId = intval($_GET['attempt']);

    $db = new quizTakeModel();
    $quiz = $db->get_quiz_info($attemptId);
    $quizId = (int) $quiz['quizId'];
    $questions = $db->get_all_questions($quizId);

    $quizData = [
        "quizTitle"  => $quiz['title'],
        "instructor" => $quiz['instructor'],
        "totalMarks" => $quiz['totalMarks'],
        "questions"  => $questions
    ];

    header('Content-Type: application/json');
    echo json_encode($quizData);
}
?>
