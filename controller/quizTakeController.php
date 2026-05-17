<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../model/quizTakeModel.php";

$db = new quizTakeModel();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header("Content-Type: application/json");

    $attemptId = 0;
    if (isset($_GET["attempt"])) {
        $attemptId = intval($_GET["attempt"]);
    }

    $attempt = $db->get_attempt($attemptId);

    if (!$attempt) {
        http_response_code(404);
        echo json_encode(["error" => "Attempt not found"]);
        exit;
    }

    $_SESSION["user_id"] = $attempt["student_id"];
    $_SESSION["user_role"] = "student";

    if ($db->has_completed_attempt_for_same_student_quiz($attemptId)) {
        http_response_code(403);
        echo json_encode(["error" => "Already attempted"]);
        exit;
    }

    $quiz = $db->get_quiz_info($attemptId);
    $questions = $db->get_all_questions($quiz["quizId"]);

    $db->start_attempt($attemptId);

    $data = [];
    $data["quizTitle"] = $quiz["title"];
    $data["instructor"] = $quiz["instructor"];
    $data["totalMarks"] = $quiz["totalMarks"];
    $data["timeLimitMinutes"] = $quiz["timeLimitMinutes"];
    $data["questions"] = $questions;

    echo json_encode($data);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header("Content-Type: application/json");

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "Bad data"]);
        exit;
    }

    $attemptId = intval($data["attempt_id"]);
    $attempt = $db->get_attempt($attemptId);

    if (!$attempt) {
        http_response_code(404);
        echo json_encode(["error" => "Attempt not found"]);
        exit;
    }

    if ($db->has_completed_attempt_for_same_student_quiz($attemptId)) {
        http_response_code(403);
        echo json_encode(["error" => "Attempt already completed"]);
        exit;
    }

    $answers = [];
    if (isset($data["answers"])) {
        $answers = $data["answers"];
    }

    $score = 0;
    $db->clear_answers($attemptId);

    foreach ($answers as $questionId => $optionId) {
        $questionId = intval($questionId);
        $optionId = intval($optionId);

        if ($questionId > 0 && $optionId > 0) {
            $option = $db->get_option_with_marks($optionId, $questionId);

            if ($option) {
                if ($option["is_correct"] == 1) {
                    $score = $score + intval($option["marks"]);
                }

                $db->set_answers($attemptId, $questionId, $optionId);
            }
        }
    }

    $db->complete_attempt($attemptId, $score, $db->NOW());

    $_SESSION["user_id"] = $attempt["student_id"];
    $_SESSION["user_role"] = "student";

    echo json_encode([
        "success" => true,
        "redirect" => "/WebtechProjectGroup-5/view/Results_Leaderboard/result.php?attempt_id=" . $attemptId
    ]);
    exit;
}
?>
