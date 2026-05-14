<?php require_once __DIR__ . "/../../controller/AnalyticsController.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Analytics</title>
    <link rel="stylesheet" href="style/analytics.css">
</head>
<body>

<nav>
    <a href="analytics.php">Analytics</a>
    <a href="leaderboard.php">Leaderboard</a>
</nav>

<div class="container">
    <h2>Instructor Analytics</h2>

    <?php if ($show_error): ?>
        <p><?php echo htmlspecialchars($analytics_error); ?></p>
    <?php endif; ?>

    <!-- Quiz selector — submits to same page via GET -->
    <form method="GET" action="">
        <div class="form-row">
            <select name="quiz_id">
                <option value="">-- Select a Quiz --</option>
                <?php foreach ($quiz_options as $option): ?>
                <option value="<?php echo $option['value']; ?>" <?php echo $option['selected'] ? 'selected' : ''; ?>>
                    <?php echo $option['label']; ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View</button>
        </div>
    </form>

    <?php if ($show_no_quizzes): ?>
        <p>No quizzes found for this instructor.</p>
    <?php endif; ?>

    <?php if ($show_attempts_table): ?>
    <table>
        <thead>
            <tr>
                <th>Serial</th>
                <th>Student</th>
                <th>Score</th>
                <th>Duration</th>
                <th>Date</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($table_rows as $row): ?>
            <tr>
                <td><?php echo $row['row_number']; ?></td>
                <td><?php echo $row['student_name']; ?></td>
                <td><?php echo $row['score_display']; ?></td>
                <td><?php echo $row['duration_display']; ?></td>
                <td><?php echo $row['completed_at_display']; ?></td>
                <td>
                    <span class="badge <?php echo $row['status_class']; ?>">
                        <?php echo $row['status_label']; ?>
                    </span>
                </td>
                <td>
                    <a href="result.php?attempt_id=<?php echo $row['attempt_id']; ?>">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <?php if ($show_summary): ?>
            <tr>
                <td colspan="2">Class Summary</td>
                <td>
                    Avg: <?php echo $summary_data['average']; ?> |
                    High: <?php echo $summary_data['highest']; ?> |
                    Low: <?php echo $summary_data['lowest']; ?>
                </td>
                <td colspan="2">Pass Rate: <?php echo $summary_data['pass_rate']; ?>%</td>
                <td colspan="2"></td>
            </tr>
            <?php endif; ?>
        </tfoot>
    </table>

    <?php elseif ($show_no_attempts): ?>
        <p>No completed attempts found for this quiz yet. Choose a quiz with an attempts count above 0.</p>
    <?php endif; ?>
</div>

</body>
</html>