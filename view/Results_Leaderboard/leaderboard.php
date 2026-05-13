<?php include "../Controller/LeaderboardController.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="style/leaderboard.css">
</head>
<body>

<nav>
    <a href="leaderboard.php">🏆 Leaderboard</a>
    <?php if (isset($_SESSION["role"])): ?>
        <?php if ($_SESSION["role"] === "student"): ?>
            <a href="my_results.php"> My Results</a>
        <?php elseif ($_SESSION["role"] === "instructor"): ?>
            <a href="analytics.php">Analytics</a>
        <?php endif; ?>
    <?php else: ?>
        <!-- Task 1 handles login/logout links after merge -->
        <a href="my_results.php">My Results</a>
        <a href="analytics.php">Analytics</a>
    <?php endif; ?>
</nav>

<div class="container">

    <div class="refresh-row">
        <h2>Leaderboard — Top 10</h2>
        <span id="refresh-countdown">Refreshing in 30s</span>
    </div>

    <!-- Top 10 students by cumulative score (SUM of attempts.score GROUP BY student_id) -->
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
            <?php if (empty($leaders)): ?>
                <tr><td colspan="4" style="text-align:center;">No data yet.</td></tr>
            <?php else: ?>
                <?php foreach ($leaders as $i => $row): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo $row["total_score"]; ?></td>
                    <td><?php echo $row["total_attempts"]; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- AJAX auto-refresh every 30 seconds -->
<script src="../Controller/ajax/ajax.js"></script>

</body>
</html>