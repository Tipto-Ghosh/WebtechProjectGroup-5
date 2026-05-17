<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/my_resultsModel.php";

$student_id = filter_var($_SESSION["user_id"] ?? null, FILTER_VALIDATE_INT);
$user_role  = $_SESSION["user_role"] ?? "";

if ($student_id === false || $student_id <= 0 || $user_role !== "student") {
    header("Location: ../auth/login.php");
    exit;
}

$results = getStudentResults($student_id);

foreach ($results as $index => $result) {
    $score = (int)$result["score"];
    $total = (int)$result["total_marks"];
    $percent = $total > 0 ? ($score / $total) * 100 : 0;
    $passed = $percent >= 60;

    $results[$index]["row_number"] = $index + 1;
    $results[$index]["score_display"] = $score . " / " . $total;
    $results[$index]["duration_display"] = $result["duration"] !== null ? $result["duration"] . " min":;
    $results[$index]["completed_at_display"] = !empty($result["completed_at"]) ? date("d M Y", strtotime($result["completed_at"])) : "-";
    $results[$index]["status_label"] = $passed ? "PASS" : "FAIL";
    $results[$index]["status_class"] = $passed ? "pass" : "fail";
}
