<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/leaderboardModel.php";

$user_role = $_SESSION["user_role"] ?? "";
$leaders = getLeaderboard();

foreach ($leaders as $index => $leader) {
    $leaders[$index]["rank"] = $index + 1;
}