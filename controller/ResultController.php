<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/resultModel.php";

// $user_id = filter_var($_SESSION["user_id"] ?? null, FILTER_VALIDATE_INT);
// $user_role = $_SESSION["user_role"] ?? "";
$user_id = 3;
$user_role = "student" ;

if ($user_id === false || $user_id <= 0 || !in_array($user_role, ["student", "instructor"], true)) {
    header("Location: ../auth/login.php");
    exit;
}

$attempt = [];
$breakdown = [];
$result_error = "";
$score = 0;
$total = 0;
$percent = 0;
$pass = false;

$attempt_id = filter_var($_GET["attempt_id"] ?? ($_GET["id"] ?? 0), FILTER_VALIDATE_INT);
if ($attempt_id === false || $attempt_id < 0) {
    $attempt_id = 0;
}

if ($user_role === "student" && $attempt_id <= 0) {
    $attempt_id = getLatestStudentAttemptId($user_id);
} elseif ($attempt_id <= 0) {
    $result_error = "Please open a saved attempt from Analytics.";
}

if ($attempt_id > 0) {
    $attempt = getAttemptById($attempt_id);

    if (!empty($attempt)) {
        $is_student_owner = $user_role === "student" && (int)$attempt["student_id"] === $user_id;
        $is_instructor_owner = $user_role === "instructor" && (int)$attempt["instructor_id"] === $user_id;

        if (!$is_student_owner && !$is_instructor_owner) {
            $attempt = [];
            $result_error = "You do not have permission to view this result.";
        } else {
            $score = (int)$attempt["score"];
            $total = (int)$attempt["total_marks"];
            $percent = $total > 0 ? ($score / $total) * 100 : 0;
            $pass = $percent >= 60;

            $attempt["score_display"] = $score . " / " . $total;
            $attempt["percent_display"] = round($percent, 1) . "%";
            $attempt["status_label"] = $pass ? "PASS" : "FAIL";
            $attempt["status_message"] = $pass ? "PASS - Well done!" : "FAIL - Better luck next time!";
            $attempt["status_class"] = $pass ? "pass" : "fail";
            $attempt["completed_at_display"] = !empty($attempt["completed_at"]) ? date("d M Y", strtotime($attempt["completed_at"])) : "-";
            $attempt["duration_display"] = $attempt["duration"] !== null ? $attempt["duration"] . " min" : "-";

            $breakdown = getAnswerBreakdown($attempt_id);

            foreach ($breakdown as $index => $row) {
                $is_correct = (bool)$row["is_correct"];

                $breakdown[$index]["row_number"] = $index + 1;
                $breakdown[$index]["selected_class"] = $is_correct ? "answer-correct" : "answer-wrong";
                $breakdown[$index]["selected_status"] = $is_correct ? "Correct" : "Wrong";
            }
        }
    }
}

if (empty($attempt) && $result_error === "") {
    $result_error = "Attempt not found. Please open a saved attempt from My Results or Analytics.";
}
