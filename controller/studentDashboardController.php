<?php
require_once __DIR__ . '/../model/studentDashboardModel.php';
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='student'){
    header('Location: ../auth/login.php');
    exit;
}

$student_id = (int)$_SESSION['user_id'];


// get all data from database
$total_published_quizzes = getTotalPublishedQuizzes();
$total_taken_quizzes = getTotalTakenQuizzes($student_id);
$available_quizzes = getAvailableQuizzes($student_id);
$total_attempts = getTotalAttempts($student_id);
$scoreStats = getStudentScoreStats($student_id);

$avg_score = $scoreStats['average'];
$score_high = $scoreStats['high'];
$score_low = $scoreStats['low'];
$score_median = $scoreStats['median'];

// student name from session to show on dashboard
$student_name = $_SESSION['user_name'] ?? 'Student';
$student_role = 'Student';
?>