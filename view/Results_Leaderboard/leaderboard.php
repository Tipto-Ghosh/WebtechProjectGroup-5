<?php require_once __DIR__ . "/../../controller/LeaderboardController.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="style/leaderboard.css">
</head>
<body>

<nav>
    <a href="leaderboard.php">Leaderboard</a>
    <?php if ($nav_links['show_results']): ?>
        <a href="my_results.php">My Results</a>
    <?php endif; ?>
    <?php if ($nav_links['show_analytics']): ?>
        <a href="analytics.php">Analytics</a>
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
                <th>Rank</th>
                <th>Student</th>
                <th>Total Score</th>
                <th>Quizzes Taken</th>
            </tr>
        </thead>
        <tbody id="leaderboard-body">
            <?php if (!$show_data): ?>
                <tr><td colspan="4" style="text-align:center;">No data yet.</td></tr>
            <?php else: ?>
                <?php foreach ($table_rows as $row): ?>
                <tr>
                    <td><?php echo $row['rank']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['total_score']; ?></td>
                    <td><?php echo $row['total_attempts']; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<script src="../../controller/js/ajax.js"></script>

</body>
</html>
