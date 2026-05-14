<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . "/../model/quizListModel.php";

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {

//$userId = $_SESSION['user_id'] ?? null;
    $userId =1;
    if ($userId === null) {
        echo json_encode(["error" => "User not logged in"]);
        exit;
    }

    if (isset($_POST['fetchQuizzes'])) {
        $quizzes = get_quizzes_with_attempts((int)$userId);
        if (count($quizzes) === 0) {
            echo json_encode(["error" => "No quizzes available"]);
        } else {
            echo json_encode(["quizzes" => $quizzes]);
        }
        exit;
    }
    if (isset($_POST['startQuiz'])) {
        $quizId = (int)$_POST['quizId'];

        if (has_attempted_quiz($userId, $quizId)) {
            die("Quiz already attempted");
        }

        $attemptId = create_attempt($userId, $quizId);
        $_SESSION["attemptID"] = $attemptId;
        $url = "../view/student/take_quiz.php?attempt=" . $attemptId;

        header("Location: " . $url);
        exit;
    }
    if (isset($_POST['goDashboard'])) {
        $url = "../view/student/dashboard.php";
        header("Location: " . $url);
        exit;
    }
}
?>
