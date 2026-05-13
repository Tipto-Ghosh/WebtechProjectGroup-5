<?php
session_start();

include "../Model/db.php";

// Leaderboard is public — no auth required

$database   = new db();
$connection = $database->connection();

$leaders = $database->getLeaderboard($connection);
?>