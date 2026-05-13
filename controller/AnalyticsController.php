<?php
session_start();

include "../Model/db.php";

// ── Auth check — instructor only (Task 1 sets $_SESSION — uncomment after merging) ──
// if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "instructor") {
//     Header("Location: ../View/leaderboard.php");
//     exit;
// }

$database   = new db();
$connection = $database->connection();

// Use the logged-in instructor when available; otherwise keep the standalone demo user.
$instructor_id = 4;
if (isset($_SESSION["user_id"]) && (!isset($_SESSION["role"]) || $_SESSION["role"] === "instructor")) {
    $instructor_id = (int)$_SESSION["user_id"];
}

$quizzes       = $database->getInstructorQuizzes($connection, $instructor_id);
$attempts      = [];
$selected_quiz = "";

if (isset($_GET["quiz_id"]) && $_GET["quiz_id"] !== "") {
    $selected_quiz = (int)$_GET["quiz_id"];
    $attempts      = $database->getQuizAttempts($connection, $selected_quiz);
}
?>