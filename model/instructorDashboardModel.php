<?php
include_once __DIR__ . "/../config/dbConnection.php";

function getInstructorStats(int $instructorId){
    $conn = get_database_connection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM quizzes WHERE instructor_id = ?");
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['total'];
    // Published
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM quizzes WHERE instructor_id = ? AND status = 'published'");
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $published = (int)$stmt->get_result()->fetch_assoc()['total'];
    // Draft
    $draft = $total - $published;
    // Total attempts
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM attempts a JOIN quizzes q ON a.quiz_id = q.id WHERE q.instructor_id = ? AND a.score IS NOT NULL");
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $attempts = (int)$stmt->get_result()->fetch_assoc()['total'];
    // Average score (across completed attempts)
    $average = 0;
    $stmt = $conn->prepare("SELECT AVG(a.score / q.total_marks * 100) AS avg_score FROM attempts a JOIN quizzes q ON a.quiz_id = q.id WHERE q.instructor_id = ? AND a.score IS NOT NULL AND q.total_marks > 0");
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && $row['avg_score'] !== null) {
        $average = round((float)$row['avg_score'], 1);
    }

    return [
        'total_quizzes' => $total,
        'published_quizzes'=> $published,
        'draft_quizzes' => $draft,
        'total_attempts' => $attempts,
        'average_score' => $average,
    ];
}

function getInstructorQuizzes(int $instructorId){
    $conn = get_database_connection();
    $sql = "SELECT q.id, q.title, q.total_marks, q.time_limit_minutes, q.status, q.created_at, COUNT(ques.id) AS question_count FROM quizzes q LEFT JOIN questions ques ON q.id = ques.quiz_id WHERE q.instructor_id = ? GROUP BY q.id ORDER BY q.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
    return $quizzes;
}
?>