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
    <a href="leaderboard.php">Leaderboard</a>
    <a href="my_results.php">My Results</a>
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
 <h2>Result — <?php echo htmlspecialchars($attempt["title"]); ?></h2>

    <!-- Total Score -->
    <div class="score-box">
        Score: <?php echo $score; ?> / <?php echo $total; ?>
        &nbsp;(<?php echo round($percent, 1); ?>%)
    </div>
    <!-- Pass / Fail Banner (pass threshold = 60%) -->
    <div class="banner <?php echo $pass ? "pass" : "fail"; ?>">
        <?php echo $pass ? "PASS — Well done!" : "FAIL — Better luck next time!"; ?>
    </div>

    <!-- Question-by-question breakdown table -->
    <!-- Selected answer highlighted GREEN if correct, RED if wrong -->
    <!-- Correct answer always shown in last column -->
    <h3>Question Breakdown</h3>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Question</th>
                <th>Your Answer</th>
                <th>Correct Answer</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($breakdown as $i => $row): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($row["question_text"]); ?></td>

                <!-- Highlight selected answer: green = correct, red = wrong -->
                <td class="<?php echo $row["is_correct"] ? "answer-correct" : "answer-wrong"; ?>">
                    <?php echo htmlspecialchars($row["selected_answer"]); ?>
                    <?php echo $row["is_correct"] ? " Correct" : " Wrong "; ?>
                </td>

                <!-- Always show the correct answer -->
                <td class="answer-correct">
                    <?php echo htmlspecialchars($row["correct_answer"]); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>

</div>

</body>
</html>