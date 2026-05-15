<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../model/quizTakeModel.php";

$start="";
$end= "";
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $attemptId = intval($_GET['attempt']);

    $db = new quizTakeModel();

    if ($db->is_attempt_completed($attemptId)) {  
        $this->LOG_ALERT('Already attempted this quiz, Go back!');
        header("Location: /WebtechProjectGroup-5/view/Results_Leaderboard/result.php");
        exit;
    }

    $quiz = $db->get_quiz_info($attemptId);
    $quizId = (int) $quiz['quizId'];
    $questions = $db->get_all_questions($quizId);

    $quizData = [
        "quizTitle"  => $quiz['title'],
        "instructor" => $quiz['instructor'],
        "totalMarks" => $quiz['totalMarks'],
        "questions"  => $questions
    ];

        
    $_SESSION['user_id'] = (int) $db->get_student_id($attemptId);
    $_SESSION['role'] = "student";

    $start = $db->NOW();

    header('Content-Type: application/json');
    echo json_encode($quizData);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $attemptId = intval($data['attempt_id']);


    $conn = new quizTakeModel();

    if ($conn->is_attempt_completed($attemptId)) {  
        $this->LOG_ALERT('Already attempted this quiz, Go back!');
        header("Location: /WebtechProjectGroup-5/view/Results_Leaderboard/result.php?attempt=$attemptId");
        exit;
    }
    $answers   = $data['answers'];

    $totalScore = 0;
    foreach ($answers as $questionId => $optionId) {
        $marks = 0;
        if ($optionId !== null) {
            $optRow = $conn->get_option_with_marks($optionId);
            if ($optRow && $optRow['is_correct']) {
                $marks = intval($optRow['marks']);
                $totalScore += $marks;
            }
        }
        $conn->set_answers($attemptId, $questionId, $optionId);
    }

    $end = $conn->NOW();
    
    $conn->update_attempts($attemptId, $totalScore, $start, $end); 

    header("Location: ../view/Results_Leaderboard/result.php");
    exit;
}
?>
