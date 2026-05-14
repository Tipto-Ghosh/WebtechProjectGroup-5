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

// Functions for view rendering
function getLeaderboardTableData($leaders) {
    $table_data = [];
    foreach ($leaders as $leader) {
        $table_data[] = [
            'rank' => (int)$leader["rank"],
            'name' => htmlspecialchars($leader["name"]),
            'total_score' => htmlspecialchars($leader["total_score"]),
            'total_attempts' => htmlspecialchars($leader["total_attempts"])
        ];
    }
    return $table_data;
}

function shouldShowLeaderboardData($leaders) {
    return !empty($leaders);
}

function getNavLinks($user_role) {
    if ($user_role === "student") {
        return ['show_results' => true, 'show_analytics' => false];
    } elseif ($user_role === "instructor") {
        return ['show_results' => false, 'show_analytics' => true];
    } else {
        return ['show_results' => true, 'show_analytics' => true];
    }
}

function prepareViewData($leaders, $user_role) {
    return [
        'show_data' => shouldShowLeaderboardData($leaders),
        'table_rows' => getLeaderboardTableData($leaders),
        'nav_links' => getNavLinks($user_role)
    ];
}

// Prepare view variables
$view_data = prepareViewData($leaders, $user_role);
extract($view_data);
