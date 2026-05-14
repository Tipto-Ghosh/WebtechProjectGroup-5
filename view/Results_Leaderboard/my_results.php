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
    <a href="my_results.php">My Results</a>
</nav>

<div class="container">
    <h2>My Results</h2>

    <?php if (!$show_data): ?>
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
            <?php foreach ($table_rows as $row): ?>
            <tr>
                <td><?php echo $row['row_number']; ?></td>
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['score_display']; ?></td>
                <td><?php echo $row['duration_display']; ?></td>
                <td><?php echo $row['completed_at_display']; ?></td>
                <td>
                    <span class="badge <?php echo $row['status_class']; ?>">
                        <?php echo $row['status_label']; ?>
                    </span>
                </td>
                <td>
                    <a href="result.php?attempt_id=<?php echo $row['id']; ?>">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

</body>
</html>
