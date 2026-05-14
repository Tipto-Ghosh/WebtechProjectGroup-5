<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/my_resultsModel.php";

// $student_id = filter_var($_SESSION["user_id"] ?? null, FILTER_VALIDATE_INT);
// $user_role  = $_SESSION["user_role"] ?? "";
$student_id = 3;
$user_role = "student" ;

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
    $results[$index]["duration_display"] = $result["duration"] !== null ? $result["duration"] . " min" : "-";
    $results[$index]["completed_at_display"] = !empty($result["completed_at"]) ? date("d M Y", strtotime($result["completed_at"])) : "-";
    $results[$index]["status_label"] = $passed ? "PASS" : "FAIL";
    $results[$index]["status_class"] = $passed ? "pass" : "fail";
}

// Functions for view rendering
function getMyResultsTableData($results) {
    $table_data = [];
    foreach ($results as $result) {
        $table_data[] = [
            'row_number' => (int)$result["row_number"],
            'title' => htmlspecialchars($result["title"]),
            'score_display' => htmlspecialchars($result["score_display"]),
            'duration_display' => htmlspecialchars($result["duration_display"]),
            'completed_at_display' => htmlspecialchars($result["completed_at_display"]),
            'status_label' => htmlspecialchars($result["status_label"]),
            'status_class' => htmlspecialchars($result["status_class"]),
            'id' => (int)$result["id"]
        ];
    }
    return $table_data;
}

function shouldShowMyResultsData($results) {
    return !empty($results);
}

function prepareViewData($results) {
    return [
        'show_data' => shouldShowMyResultsData($results),
        'table_rows' => getMyResultsTableData($results)
    ];
}

// Prepare view variables
$view_data = prepareViewData($results);
extract($view_data);
