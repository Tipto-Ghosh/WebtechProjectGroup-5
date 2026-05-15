<?php
require_once __DIR__ . '/../model/instructorDashboardModel.php';
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit;
}
$instructor_id = (int)$_SESSION['user_id'];
$stats = getInstructorStats($instructor_id);
$quizzes = getInstructorQuizzes($instructor_id);
$pageTitle = "My Quizzes";
$pageSubtitle = "Manage your quizzes and view their performance.";

$instructor_name = $_SESSION['user_name'] ?? 'Instructor';
$instructor_role = 'Instructor';  
?>