<?php
// AJAX endpoint — called by Controller/ajax/ajax.js every 30 seconds
// Returns top 10 leaderboard as JSON

include "../Model/db.php";

header("Content-Type: application/json");

$database   = new db();
$connection = $database->connection();

$leaders = $database->getLeaderboard($connection);

echo json_encode(["success" => true, "data" => $leaders]);
?>