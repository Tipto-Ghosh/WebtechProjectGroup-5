<?php 
require_once __DIR__ . "/../../controller/LeaderboardController.php";
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    
    <title>Leaderboard</title>
    <link rel="stylesheet" href="style/leaderboard.css">
</head>
<body>

<nav>
    
    <?php if ($user_role === "student"): ?>
        <a href="my_results.php">My Results</a>
        <a href="../student/dashboard.php">Dashboard</a>
    <?php elseif ($user_role === "instructor"): ?>
        <a href="analytics.php">Analytics</a>
        <a href="../quiz_builder/dashboard.php">Dashboard</a>
    <?php endif; ?>
</nav>

<div class="container">

    <div class="refresh-row">
        <h2>Leaderboard - Top 10</h2>
        <span id="refresh-countdown">Refreshing in 30s</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Rank</th><th>Student</th><th>Total Score</th><th>Quizzes Taken</th>
            </tr>
        </thead>
        <tbody id="leaderboard-body">
            <?php if (empty($leaders)): ?>
                <tr><td colspan="4" style="text-align:center;">No data yet.</td></tr>
            <?php else: ?>
                <?php foreach ($leaders as $leader): ?>
                <tr>
                    <td><?php echo (int)$leader["rank"]; ?></td>
                    <td><?php echo htmlspecialchars($leader["name"]); ?></td>
                    <td><?php echo htmlspecialchars($leader["total_score"]); ?></td>
                    <td><?php echo htmlspecialchars($leader["total_attempts"]); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<script src="../../controller/js/ajax.js"></script>

</body>
</html>