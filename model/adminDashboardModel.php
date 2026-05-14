<?php
include_once(__DIR__ . '/../config/dbConnection.php');

function getTotalStudents() {
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM users WHERE role = 'student'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getTotalInstructors() {
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM users WHERE role = 'instructor'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getTotalAdmins() {
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM users WHERE role = 'admin'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getActiveAccounts() {
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM users WHERE is_active = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getSuspendedAccounts() {
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM users WHERE is_active = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getLiveQuizzes() {
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM quizzes WHERE status = 'published'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getDraftQuizzes() {
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM quizzes WHERE status = 'draft'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getTotalAttempts() {
    $conn = get_database_connection();
    $sql = "SELECT COUNT(*) AS count FROM attempts WHERE score IS NOT NULL";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function getAverageScorePercent() {
    $conn = get_database_connection();
    $sql = "SELECT AVG(a.score / q.total_marks * 100) AS avg_score FROM attempts a JOIN quizzes q ON a.quiz_id = q.id WHERE a.score IS NOT NULL";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return round($row['avg_score'] ?? 0, 1);
}

function getScoreStats() {
    $conn = get_database_connection();
    $sql = "SELECT (a.score / q.total_marks * 100) AS percentage FROM attempts a JOIN quizzes q ON a.quiz_id = q.id 
            WHERE a.score IS NOT NULL ORDER BY percentage";
            
    $result = $conn->query($sql);
    $percentages = [];
    
    while ($row = $result->fetch_assoc()) {
        $percentages[] = (float) $row['percentage']; 
    }

    if (empty($percentages)) {
        return ['high' => 0, 'low' => 0, 'median' => 0];
    }

    $high = max($percentages);
    $low = min($percentages);
    $count = count($percentages);
    $mid = (int) floor($count / 2);  

    if ($count % 2 == 0) {
        $median = ($percentages[$mid - 1] + $percentages[$mid]) / 2;
    } else {
        $median = $percentages[$mid];
    }

    return [
        'high' => round($high, 1),
        'low'  => round($low, 1),
        'median' => round($median, 1)
    ];
}

function getRecentUsers($limit = 5) {
    $conn = get_database_connection();
    $sql = "SELECT id, name, role, created_at, is_active 
            FROM users ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}
?>