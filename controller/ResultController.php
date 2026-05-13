<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../Model/db.php";

$user_id   = filter_var($_SESSION["user_id"] ?? null, FILTER_VALIDATE_INT);
$user_role = $_SESSION["user_role"] ?? "";

if ($user_id === false || $user_id <= 0 || !in_array($user_role, ["student", "instructor"], true)) {
    header("Location: ../view/auth/login.php");
    exit;
}

$attempt      = [];
$breakdown    = [];
$result_error = "";

$database   = new db();
$connection = $database->connection();

$attempt_id = filter_var($_GET["attempt_id"] ?? ($_GET["id"] ?? 0), FILTER_VALIDATE_INT);
if ($attempt_id === false || $attempt_id < 0) {
    $attempt_id = 0;
}

if ($user_role === "student" && $attempt_id <= 0) {
    $attempt_id = (int)$database->getLatestStudentAttemptId($connection, $user_id);
} elseif ($attempt_id <= 0) {
    $result_error = "Please open a saved attempt from Analytics.";
}

if ($attempt_id > 0) {
    $attempt = $database->getAttempt($connection, $attempt_id);

    if (!empty($attempt)) {
        if ($user_role === "student" && isset($attempt["student_id"]) && (int)$attempt["student_id"] !== $user_id) {
            $attempt      = [];
            $result_error = "You can only view your own quiz results.";
        } else {
            $breakdown = $database->getAnswerBreakdown($connection, $attempt_id);
        }
    }
}

if (empty($attempt)) {
    if ($result_error === "") {
        $result_error = "Attempt not found. Please open a saved attempt from My Results or Analytics.";
    }
} else {
    $score   = $attempt["score"];
    $total   = $attempt["total_marks"];
    $percent = $total > 0 ? ($score / $total) * 100 : 0;
    $pass    = $percent >= 60;
}
?>
