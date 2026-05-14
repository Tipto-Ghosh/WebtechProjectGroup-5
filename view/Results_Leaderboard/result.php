<?php require_once __DIR__ . "/../../controller/ResultController.php"; ?>
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

    <?php if ($show_error): ?>
        <h2>Result</h2>
        <p><?php echo htmlspecialchars($result_error); ?></p>
        <p>
            <a href="my_results.php">Back to My Results</a>
            |
            <a href="analytics.php">Back to Analytics</a>
        </p>
    <?php else: ?>
        <h2>Result - <?php echo $attempt_data['title']; ?></h2>

        <div class="score-box">
            Score: <?php echo $attempt_data['score_display']; ?>
            &nbsp;(<?php echo $attempt_data['percent_display']; ?>)
        </div>

        <div class="banner <?php echo $attempt_data['status_class']; ?>">
            <?php echo $attempt_data['status_message']; ?>
        </div>

        <h3>Question Breakdown</h3>

        <table>
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($table_rows as $row): ?>
                <tr>
                    <td><?php echo $row['row_number']; ?></td>
                    <td><?php echo $row['question_text']; ?></td>
                    <td class="<?php echo $row['selected_class']; ?>">
                        <?php echo $row['selected_answer']; ?>
                        <?php echo $row['selected_status']; ?>
                    </td>
                    <td class="answer-correct">
                        <?php echo $row['correct_answer']; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="back-button-container">
            <a class="back-button" href="<?php echo $button['href']; ?>"><?php echo $button['text']; ?></a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
