<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/analyticsModel.php";

// $instructor_id = filter_var($_SESSION["user_id"] ?? null, FILTER_VALIDATE_INT);
$instructor_id = 4;
//$user_role = $_SESSION["user_role"] ?? "";
$user_role = "instructor" ;
if ($instructor_id === false || $instructor_id <= 0 || $user_role !== "instructor") {
    header("Location: ../auth/login.php");
    exit;
}

$quizzes = getInstructorQuizzes($instructor_id);
$attempts = [];
$selected_quiz = "";
$analytics_summary = null;
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
    }
}

// Functions for view rendering
function getQuizOptions($quizzes, $selected_quiz) {
    $options = [];
    foreach ($quizzes as $quiz) {
        $options[] = [
            'value' => (int)$quiz["id"],
            'label' => htmlspecialchars($quiz["option_label"]),
            'selected' => (int)$selected_quiz === (int)$quiz["id"]
        ];
    }
    return $options;
}

function getTableData($attempts) {
    $table_data = [];
    foreach ($attempts as $attempt) {
        $table_data[] = [
            'row_number' => (int)$attempt["row_number"],
            'student_name' => htmlspecialchars($attempt["student_name"]),
            'score_display' => htmlspecialchars($attempt["score_display"]),
            'duration_display' => htmlspecialchars($attempt["duration_display"]),
            'completed_at_display' => htmlspecialchars($attempt["completed_at_display"]),
            'status_label' => htmlspecialchars($attempt["status_label"]),
            'status_class' => htmlspecialchars($attempt["status_class"]),
            'attempt_id' => (int)$attempt["attempt_id"]
        ];
    }
    return $table_data;
}

function getSummaryData($analytics_summary) {
    if (!$analytics_summary) return null;

    return [
        'average' => htmlspecialchars($analytics_summary["average"]),
        'highest' => htmlspecialchars($analytics_summary["highest"]),
        'lowest' => htmlspecialchars($analytics_summary["lowest"]),
        'pass_rate' => htmlspecialchars($analytics_summary["pass_rate"])
    ];
}

function shouldShowQuizSelector($quizzes) {
    return !empty($quizzes);
}

function shouldShowNoQuizzesMessage($quizzes) {
    return empty($quizzes);
}

function shouldShowAttemptsTable($selected_quiz, $attempts) {
    return $selected_quiz && !empty($attempts);
}

function shouldShowNoAttemptsMessage($selected_quiz, $attempts) {
    return $selected_quiz && empty($attempts);
}

function shouldShowSummaryRow($analytics_summary) {
    return !empty($analytics_summary);
}

function shouldShowErrorMessage($analytics_error) {
    return !empty($analytics_error);
}

function prepareViewData($quizzes, $selected_quiz, $attempts, $analytics_summary, $analytics_error) {
    return [
        'show_error' => shouldShowErrorMessage($analytics_error),
        'quiz_options' => getQuizOptions($quizzes, $selected_quiz),
        'show_no_quizzes' => shouldShowNoQuizzesMessage($quizzes),
        'show_attempts_table' => shouldShowAttemptsTable($selected_quiz, $attempts),
        'table_rows' => shouldShowAttemptsTable($selected_quiz, $attempts) ? getTableData($attempts) : [],
        'show_summary' => shouldShowSummaryRow($analytics_summary),
        'summary_data' => shouldShowSummaryRow($analytics_summary) ? getSummaryData($analytics_summary) : null,
        'show_no_attempts' => shouldShowNoAttemptsMessage($selected_quiz, $attempts)
    ];
}

// Prepare view variables
$view_data = prepareViewData($quizzes, $selected_quiz, $attempts, $analytics_summary, $analytics_error);
extract($view_data);
