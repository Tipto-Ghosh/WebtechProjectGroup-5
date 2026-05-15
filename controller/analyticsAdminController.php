<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . "/../model/analyticsAdminModel.php";
require_once __DIR__ . "/analyticsAdminValidation.php";

$admin_id = (int)$_SESSION["user_id"];
$admin_name = $_SESSION["user_name"] ?? "Admin";

function formatAnalyticsAdminScoreValue($value)
{
    return number_format((float)$value, ((float)$value == (int)$value) ? 0 : 1);
}

function formatAnalyticsAdminScoreDisplay($score, $total)
{
    $score_text = formatAnalyticsAdminScoreValue($score);
    return $total > 0 ? $score_text . " / " . (int)$total : $score_text;
}

function formatAnalyticsAdminAttempts(array $attempts)
{
    $scores = [];
    $passed_count = 0;
    $total_marks = 0;

    foreach ($attempts as $index => $attempt) {
        $score = (int)$attempt["score"];
        $total = (int)$attempt["total_marks"];
        $percent = $total > 0 ? ($score / $total) * 100 : 0;
        $passed = $percent >= 60;

        $scores[] = $score;
        if ($total > 0) {
            $total_marks = $total;
        }

        if ($passed) {
            $passed_count++;
        }

        $attempts[$index]["row_number"] = $index + 1;
        $attempts[$index]["score_display"] = $score . " / " . $total;
        $attempts[$index]["percent_display"] = round($percent, 1) . "%";
        $attempts[$index]["duration_display"] = $attempt["duration"] !== null ? $attempt["duration"] . " min" : "-";
        $attempts[$index]["completed_at_display"] = !empty($attempt["completed_at"]) ? date("M j, Y", strtotime($attempt["completed_at"])) : "-";
        $attempts[$index]["status_label"] = $passed ? "PASS" : "FAIL";
        $attempts[$index]["status_class"] = $passed ? "pass" : "fail";
    }

    $summary = [
        "attempt_count" => count($attempts),
        "average" => "-",
        "highest" => "-",
        "lowest" => "-",
        "pass_rate" => "0",
    ];

    if (!empty($attempts)) {
        $average_score = array_sum($scores) / count($scores);

        $summary = [
            "attempt_count" => count($attempts),
            "average" => formatAnalyticsAdminScoreDisplay($average_score, $total_marks),
            "highest" => formatAnalyticsAdminScoreDisplay(max($scores), $total_marks),
            "lowest" => formatAnalyticsAdminScoreDisplay(min($scores), $total_marks),
            "pass_rate" => formatAnalyticsAdminScoreValue(($passed_count / count($attempts)) * 100),
        ];
    }

    return [
        "attempts" => $attempts,
        "summary" => $summary,
    ];
}

function prepareAnalyticsAdminQuiz(array $quiz)
{
    $attempt_count = (int)($quiz["attempt_count"] ?? 0);
    $pass_count = (int)($quiz["pass_count"] ?? 0);

    $quiz["attempt_label"] = $attempt_count === 1 ? "1 attempt" : $attempt_count . " attempts";
    $quiz["average_display"] = $quiz["average_percent"] !== null ? round((float)$quiz["average_percent"], 1) . "%" : "-";
    $quiz["pass_rate_display"] = $attempt_count > 0 ? round(($pass_count / $attempt_count) * 100, 1) . "%" : "-";
    $quiz["created_at_display"] = !empty($quiz["created_at"]) ? date("M j, Y", strtotime($quiz["created_at"])) : "-";

    return $quiz;
}

$is_ajax = (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
    strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest"
);

if ($is_ajax) {
    header("Content-Type: application/json");

    $action = validateAnalyticsAdminAction($_POST["action"] ?? "");
    if ($action !== "load_quiz") {
        echo json_encode(["success" => false, "message" => "Invalid analytics request."]);
        exit;
    }

    $quiz_id = validateAnalyticsAdminQuizId($_POST["quiz_id"] ?? null);
    if ($quiz_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select a valid quiz."]);
        exit;
    }

    $quiz = getAnalyticsAdminQuizById($quiz_id);
    if (!$quiz) {
        echo json_encode(["success" => false, "message" => "Quiz not found."]);
        exit;
    }

    $attempt_data = formatAnalyticsAdminAttempts(getAnalyticsAdminAttemptsByQuiz($quiz_id));

    echo json_encode([
        "success" => true,
        "quiz" => prepareAnalyticsAdminQuiz($quiz),
        "attempts" => $attempt_data["attempts"],
        "summary" => $attempt_data["summary"],
    ]);
    exit;
}

$selected_instructor = validateAnalyticsAdminInstructorId($_GET["instructor_id"] ?? null);
$search_q = validateAnalyticsAdminSearch($_GET["search"] ?? null);
$selected_quiz = validateAnalyticsAdminQuizId($_GET["quiz_id"] ?? null);

$overview = getAnalyticsAdminOverview();
$instructors = getAnalyticsAdminInstructors();
$quizzes = getAnalyticsAdminQuizzes($selected_instructor, $search_q);
$analytics_error = "";
$selected_quiz_data = null;
$attempts = [];
$analytics_summary = null;

foreach ($quizzes as $index => $quiz) {
    $quizzes[$index] = prepareAnalyticsAdminQuiz($quiz);
}

if ($selected_quiz > 0) {
    $selected_quiz_data = getAnalyticsAdminQuizById($selected_quiz);

    if ($selected_quiz_data) {
        $selected_quiz_data = prepareAnalyticsAdminQuiz($selected_quiz_data);
        $attempt_data = formatAnalyticsAdminAttempts(getAnalyticsAdminAttemptsByQuiz($selected_quiz));
        $attempts = $attempt_data["attempts"];
        $analytics_summary = $attempt_data["summary"];
    } else {
        $selected_quiz = 0;
        $analytics_error = "Selected quiz was not found.";
    }
}
