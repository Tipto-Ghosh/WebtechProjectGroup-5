<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/analyticsModel.php";

$instructor_id = filter_var($_SESSION["user_id"] ?? null, FILTER_VALIDATE_INT);
$user_role = $_SESSION["user_role"] ?? "";

if ($instructor_id === false) {
    header("Location: ../auth/login.php");
    exit;
}

$quizzes = getInstructorQuizzes($instructor_id);
$attempts = [];
$selected_quiz = "";
$analytics_summary = [
    "average" => 0.0,
    "highest" => 0.0,
    "lowest" => 0.0,
    "pass_rate" => 0,
];
$analytics_error = "";

foreach ($quizzes as $index => $quiz) {
    $attempt_count = (int)$quiz["attempt_count"];
    $attempt_label = $attempt_count === 1 ? "1 attempt" : $attempt_count . " attempts";

    $quizzes[$index]["attempt_label"] = $attempt_label;
    $quizzes[$index]["option_label"] = $quiz["title"] . " (" . $attempt_label . ")";
}

$quiz_id = filter_var($_GET["quiz_id"] ?? null, FILTER_VALIDATE_INT);
if ($quiz_id !== false && $quiz_id > 0) {
    $selected_quiz = $quiz_id;
    $owns_selected_quiz = false;

    foreach ($quizzes as $quiz) {
        if ((int)$quiz["id"] === $selected_quiz) {
            $owns_selected_quiz = true;
            break;
        }
    }

    if ($owns_selected_quiz) {
        $attempts = getQuizAttemptsForInstructor($selected_quiz, $instructor_id);
        $scores = [];
        $passed_count = 0;

        foreach ($attempts as $index => $attempt) {
            $score = (int)$attempt["score"];
            $total = (int)$attempt["total_marks"];
            $percent = $total > 0 ? ($score / $total) * 100 : 0;
            $passed = $percent >= 60;

            $scores[] = $score;
            if ($passed) {
                $passed_count++;
            }

            $attempts[$index]["row_number"] = $index + 1;
            $attempts[$index]["score_display"] = $score . " / " . $total;
            $attempts[$index]["duration_display"] = $attempt["duration"] !== null ? $attempt["duration"] . " min" : "-";
            $attempts[$index]["completed_at_display"] = !empty($attempt["completed_at"]) ? date("d M Y", strtotime($attempt["completed_at"])) : "-";
            $attempts[$index]["status_label"] = $passed ? "PASS" : "FAIL";
            $attempts[$index]["status_class"] = $passed ? "pass" : "fail";
        }

        if (!empty($attempts)) {
            $analytics_summary = [
                "average" => round(array_sum($scores) / count($scores), 2),
                "highest" => max($scores),
                "lowest" => min($scores),
                "pass_rate" => round(($passed_count / count($attempts)) * 100, 1),
            ];
        }
        else {
            $analytics_summary = [
                "average" => 0.0,
                "highest" => 0.0,
                "lowest" => 0.0,
                "pass_rate" => 0,
            ];
        }
    } else {
        $selected_quiz = "";
        $analytics_error = "Selected quiz was not found for your account.";
    }
}
