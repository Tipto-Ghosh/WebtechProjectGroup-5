<?php
require_once __DIR__ . "/../config/dbConnection.php";

function getLeaderboard(): array
{
    $connection = get_database_connection();

    $sql = "SELECT u.id, u.name, SUM(a.score) AS total_score, COUNT(a.id) AS total_attempts " .
           "FROM attempts a " .
           "JOIN users u ON u.id = a.student_id " .
           "WHERE a.completed_at IS NOT NULL " .
           "AND a.score IS NOT NULL " .
           "GROUP BY u.id, u.name " .
           "ORDER BY total_score DESC, total_attempts DESC, u.name ASC " .
           "LIMIT 10";

    $result = $connection->query($sql);
    if (!$result) {
        $connection->close();
        return [];
    }

    $leaders = $result->fetch_all(MYSQLI_ASSOC);
    $connection->close();

    return $leaders;
}
