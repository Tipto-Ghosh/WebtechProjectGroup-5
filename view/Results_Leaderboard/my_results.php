<?php require_once __DIR__ . "/../../controller/MyResultsController.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Results</title>
    <link rel="stylesheet" href="style/my_results.css">
</head>
<body>

<nav>
    <a href="leaderboard.php">Leaderboard</a>
    <a href="../student/dashboard.php">Dashboard</a>
</nav>

<div class="container">
    <h2>My Results</h2>

    <?php if (empty($results)): ?>
        <p>No attempts yet.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Serial</th>
                <th>Quiz Title</th>
                <th>Score</th>
                <th>Duration</th>
                <th>Date Taken</th>
                <th>Result</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result): ?>
            <tr>
                <td><?php echo (int)$result["row_number"]; ?></td>
                <td><?php echo htmlspecialchars($result["title"]); ?></td>
                <td><?php echo htmlspecialchars($result["score_display"]); ?></td>
                <td><?php echo htmlspecialchars($result["duration_display"]); ?></td>
                <td><?php echo htmlspecialchars($result["completed_at_display"]); ?></td>
                <td>
                    <span class="badge <?php echo htmlspecialchars($result["status_class"]); ?>">
                        <?php echo htmlspecialchars($result["status_label"]); ?>
                    </span>
                </td>
                <td>
                    <a href="result.php?attempt_id=<?php echo (int)$result["id"]; ?>">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

</body>
</html>
