<?php include "../Controller/MyResultsController.php"; ?>
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

    <?php if (empty($results)): ?>
        <p>No attempts yet.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Quiz Title</th>
                <th>Score</th>
                <th>Duration</th>
                <th>Date Taken</th>
                <th>Result</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $i => $r):
                $pct  = $r["total_marks"] > 0 ? ($r["score"] / $r["total_marks"]) * 100 : 0;
                $pass = $pct >= 60;
            ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($r["title"]); ?></td>
                <td><?php echo $r["score"]; ?> / <?php echo $r["total_marks"]; ?></td>
                <td><?php echo $r["duration"] ?? "—"; ?></td>
                <td><?php echo date("d M Y", strtotime($r["completed_at"])); ?></td>
                <td>
                    <span class="badge <?php echo $pass ? "pass" : "fail"; ?>">
                        <?php echo $pass ? "PASS" : "FAIL"; ?>
                    </span>
                </td>
                <td>
                    <a href="result.php?attempt_id=<?php echo $r["id"]; ?>">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

</body>
</html>