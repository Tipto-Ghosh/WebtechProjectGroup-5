<?php include "../Controller/AnalyticsController.php"; ?>
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

    <!-- Quiz selector — submits to same page via GET -->
    <form method="GET" action="">
        <div class="form-row">
            <select name="quiz_id">
                <option value="">-- Select a Quiz --</option>
                <?php foreach ($quizzes as $quiz):
                    $attempt_count = isset($quiz["attempt_count"]) ? (int)$quiz["attempt_count"] : 0;
                    $attempt_label = $attempt_count === 1 ? "1 attempt" : $attempt_count . " attempts";
                ?>
                <option value="<?php echo $quiz["id"]; ?>"
                    <?php echo $selected_quiz == $quiz["id"] ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($quiz["title"] . " (" . $attempt_label . ")"); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View</button>
        </div>
    </form>

    <?php if (empty($quizzes)): ?>
        <p>No quizzes found for this instructor.</p>
    <?php endif; ?>

    <?php if ($selected_quiz && !empty($attempts)):
        $scores    = array_column($attempts, "score");
        $avg       = round(array_sum($scores) / count($scores), 2);
        $highest   = max($scores);
        $lowest    = min($scores);
        $passed    = count(array_filter($attempts, function($a) {
            return $a["total_marks"] > 0 && ($a["score"] / $a["total_marks"]) * 100 >= 60;
        }));
        $pass_rate = round(($passed / count($attempts)) * 100, 1);
    ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Score</th>
                <th>Duration</th>
                <th>Date</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attempts as $i => $a):
                $pct  = $a["total_marks"] > 0 ? ($a["score"] / $a["total_marks"]) * 100 : 0;
                $pass = $pct >= 60;
            ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($a["student_name"]); ?></td>
                <td><?php echo $a["score"]; ?> / <?php echo $a["total_marks"]; ?></td>
                <td><?php echo $a["duration"] ?? "—"; ?></td>
                <td><?php echo date("d M Y", strtotime($a["completed_at"])); ?></td>
                <td>
                    <span class="badge <?php echo $pass ? "pass" : "fail"; ?>">
                        <?php echo $pass ? "PASS" : "FAIL"; ?>
                    </span>
                </td>
                <td>
                    <a href="result.php?attempt_id=<?php echo $a["attempt_id"]; ?>">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Class Summary</td>
                <td>Avg: <?php echo $avg; ?> | High: <?php echo $highest; ?> | Low: <?php echo $lowest; ?></td>
                <td colspan="2">Pass Rate: <?php echo $pass_rate; ?>%</td>
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