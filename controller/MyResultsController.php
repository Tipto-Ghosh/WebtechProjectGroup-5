<?php
session_start();

include "../Model/db.php";

// ── Auth check — student only (Task 1 sets $_SESSION — uncomment after merging) ──
// if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
//     Header("Location: ../View/leaderboard.php");
//     exit;
// }

$database   = new db();
$connection = $database->connection();

// Use the logged-in student when available; otherwise keep the standalone demo user.
$student_id = 1;
if (isset($_SESSION["user_id"]) && (!isset($_SESSION["role"]) || $_SESSION["role"] === "student")) {
    $student_id = (int)$_SESSION["user_id"];
}

$results = $database->getStudentResults($connection, $student_id);
?>