<?php include "../Controller/ResultController.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Result</title>
    <link rel="stylesheet" href="style/result.css">
</head>
<body>

<nav>
    <a href="leaderboard.php">🏆 Leaderboard</a>
    <a href="my_results.php">📋 My Results</a>
</nav>

<div class="container">

    <?php if (!empty($result_error)): ?>
        <h2>Result</h2>
        <p><?php echo htmlspecialchars($result_error); ?></p>
        <p>
            <a href="my_results.php">Back to My Results</a>
            |
            <a href="analytics.php">Back to Analytics</a>
        </p>
    <?php else: ?>
 <h2>📝 Result — <?php echo htmlspecialchars($attempt["title"]); ?></h2>

    <!-- Total Score -->
    <div class="score-box">
        Score: <?php echo $score; ?> / <?php echo $total; ?>
        &nbsp;(<?php echo round($percent, 1); ?>%)
    </div>