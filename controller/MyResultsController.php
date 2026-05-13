<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../Model/db.php";

$student_id = filter_var($_SESSION["user_id"] ?? null, FILTER_VALIDATE_INT);
$user_role  = $_SESSION["user_role"] ?? "";

if ($student_id === false || $student_id <= 0 || $user_role !== "student") {
    header("Location: ../view/auth/login.php");
    exit;
}

$database   = new db();
$connection = $database->connection();

$results = $database->getStudentResults($connection, $student_id);
?>
