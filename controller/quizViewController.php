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

    $quizzes = get_quizzes_with_attempts((int)$userId);

    echo json_encode(["quizzes" => $quizzes]);
}
?>
