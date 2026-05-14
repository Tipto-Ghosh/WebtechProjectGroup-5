<?php
include_once __DIR__ . "/../config/dbConnection.php";

// Total published quizzes on the platform.
function getTotalPublishedQuizzes(){
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM quizzes WHERE status = 'published'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getTotalTakenQuizzes(int $studentId){
    // Total quizzes the student has completed.
    $conn = get_database_connection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM attempts WHERE student_id = ? AND score IS NOT NULL");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getAvailableQuizzes(int $studentId){
    // Published quizzes the student has not attempted at all (can still start).
    $conn = get_database_connection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM quizzes q WHERE q.status = 'published' AND q.id NOT IN (SELECT quiz_id FROM attempts WHERE student_id = ?)");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getTotalAttempts(int $studentId){
    // Total attempts (including incomplete ones) by the student.
    $conn = get_database_connection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM attempts WHERE student_id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getStudentScoreStats(int $studentId){
    // Performance stats for completed attempts: average , highest, lowest, median.
    $conn = get_database_connection();

    // Get percentages for completed attempts
    $stmt = $conn->prepare("SELECT (a.score / q.total_marks * 100) AS percentage
        FROM attempts a JOIN quizzes q ON a.quiz_id = q.id
        WHERE a.student_id = ? AND a.score IS NOT NULL AND q.total_marks > 0 ORDER BY percentage");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $percentages = [];
    while ($row = $result->fetch_assoc()) {
        $percentages[] = (float)$row['percentage'];
    }

    if (empty($percentages)) {
        return ['average'=>0,'high'=> 0,'low'=> 0,'median'=>0];
    }
    $count = count($percentages);
    $average = round(array_sum($percentages) / $count, 1);
    $high = round(max($percentages), 1);
    $low = round(min($percentages), 1);

    // Median
    $mid = (int) floor($count / 2);
    if ($count % 2 == 0) {
        $median = round(($percentages[$mid - 1] + $percentages[$mid]) / 2, 1);
    } else {
        $median = round($percentages[$mid], 1);
    }

    return ['average'=>$average,'high'=>$high,'low'=>$low,'median'=>$median];
}