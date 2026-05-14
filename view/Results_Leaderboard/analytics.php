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
   
    <a href="leaderboard.php">Leaderboard</a>
    <a href="../student/dashboard.php">Dashboard</a>
</nav>

<div class="container">
    <h2>Instructor Analytics</h2>

    <?php if (!empty($analytics_error)): ?>
        <p><?php echo htmlspecialchars($analytics_error); ?></p>
    <?php endif; ?>

    <form method="GET" action="">
        <div class="form-row">
            <select name  =    "quiz_id">
                <option value="">-- Select a Quiz --</option>
                <?php foreach ($quizzes   as $quiz):  ?>
                <option value="<?php echo (int)$quiz["id"]; ?>"
                    <?php echo (int)$selected_quiz === (int)$quiz["id"] ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($quiz["option_label"]); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View</button>
        </div>
    </form>

    <?php if (empty($quizzes)): ?>
        <p>No quizzes found for this instructor.</p>
    <?php endif; ?>

    <?php if ($selected_quiz &&   !empty($attempts)): ?>
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
            <?php foreach ($attempts as $attempt): ?>
            <tr>
                <td><?php echo (int)$attempt["row_number"]; ?></td>
                <td><?php echo htmlspecialchars($attempt["student_name"]); ?></td>
                <td><?php echo htmlspecialchars($attempt["score_display"]); ?></td>
                <td><?php echo htmlspecialchars($attempt["duration_display"]); ?></td>
                <td><?php echo htmlspecialchars($attempt["completed_at_display"]); ?></td>
                <td>
                    <span class="badge <?php echo htmlspecialchars($attempt["status_class"]); ?>">
                        <?php echo htmlspecialchars($attempt["status_label"]); ?>
                    </span>
                </td>
                <td>
                    <a href="result.php?attempt_id=<?php echo (int)$attempt["attempt_id"]; ?>">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Class Summary</td>
                <td>
                    Avg: <?php echo htmlspecialchars($analytics_summary["average"]); ?> |
                    High: <?php echo htmlspecialchars($analytics_summary["highest"]); ?> |
                    Low: <?php echo htmlspecialchars($analytics_summary["lowest"]); ?>
                </td>
                <td colspan="2">Pass Rate: <?php echo htmlspecialchars($analytics_summary["pass_rate"]); ?>%</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <?php elseif ($selected_quiz): ?>
        <p>No completed attempts found for this quiz yet. Choose a quiz with an attempts count above 0.</p>
    <?php endif; ?>
</div>

</body>
</html>