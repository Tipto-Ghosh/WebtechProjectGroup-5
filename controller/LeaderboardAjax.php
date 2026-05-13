<?php
require_once __DIR__ . "/../model/leaderboardModel.php";

header("Content-Type: application/json");

$leaders = getLeaderboard();

foreach ($leaders as $index => $leader) {
    $leaders[$index]["rank"] = $index + 1;
}

echo json_encode(["success" => true, "data" => $leaders]);
