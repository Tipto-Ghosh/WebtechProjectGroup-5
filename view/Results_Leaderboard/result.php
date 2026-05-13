<?php
// ─────────────────────────────────────────────────────────────────────────────
// View/result.php
// Post-Attempt Result Page
// Shows: score, pass/fail banner, question-by-question breakdown table.
// ─────────────────────────────────────────────────────────────────────────────
include "../Controller/ResultController.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result — <?php echo htmlspecialchars($attempt['title']); ?></title>
    <style>
        /* ── Reset & Base ─────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            color: #2d3748;
            min-height: 100vh;
        }

        /* ── Top bar ─────────────────────────────────────────────── */
        .topbar {
            background: #1a202c;
            color: #e2e8f0;
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar .brand { font-size: 1.1rem; font-weight: 700; letter-spacing: .5px; }
        .topbar a { color: #63b3ed; text-decoration: none; font-size: .9rem; }
        .topbar a:hover { text-decoration: underline; }

        /* ── Page wrapper ────────────────────────────────────────── */
        .container { max-width: 860px; margin: 36px auto; padding: 0 20px 60px; }

        /* ── Score hero card ─────────────────────────────────────── */
        .hero-card {
            border-radius: 16px;
            padding: 40px 36px;
            text-align: center;
            margin-bottom: 28px;
            box-shadow: 0 4px 24px rgba(0,0,0,.10);
        }
        .hero-card.pass  { background: linear-gradient(135deg, #276749 0%, #38a169 100%); color: #fff; }
        .hero-card.fail  { background: linear-gradient(135deg, #9b2c2c 0%, #e53e3e 100%); color: #fff; }

        .hero-card .quiz-title {
            font-size: 1rem;
            font-weight: 500;
            opacity: .85;
            margin-bottom: 6px;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .hero-card .badge {
            display: inline-block;
            padding: 5px 20px;
            border-radius: 999px;
            font-size: .85rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 16px;
            background: rgba(255,255,255,.25);
        }
        .hero-card .score-big {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -2px;
        }
        .hero-card .score-sub {
            font-size: 1.05rem;
            opacity: .75;
            margin-top: 6px;
        }
        .hero-card .meta {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin-top: 22px;
            font-size: .9rem;
            opacity: .85;
        }
        .hero-card .meta span b { display: block; font-size: 1.1rem; font-weight: 700; }

        /* ── Breakdown table ─────────────────────────────────────── */
        h2.section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 14px;
            padding-left: 2px;
            border-left: 4px solid #4299e1;
            padding-left: 10px;
        }
        .table-wrap {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 14px rgba(0,0,0,.08);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .92rem;
        }
        thead tr {
            background: #2d3748;
            color: #e2e8f0;
        }
        thead th {
            padding: 13px 16px;
            text-align: left;
            font-weight: 600;
            font-size: .82rem;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        tbody tr { border-bottom: 1px solid #edf2f7; transition: background .15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f7fafc; }
        tbody td { padding: 14px 16px; vertical-align: top; }

        .q-num {
            font-weight: 700;
            color: #4299e1;
            white-space: nowrap;
        }
        .answer-cell { display: flex; flex-direction: column; gap: 6px; }

        /* Correct / Wrong chips */
        .chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: .8rem;
            font-weight: 600;
        }
        .chip.correct   { background: #c6f6d5; color: #276749; }
        .chip.wrong     { background: #fed7d7; color: #9b2c2c; }
        .chip.correct-ans { background: #bee3f8; color: #2b6cb0; }

        .marks-badge {
            background: #ebf8ff;
            color: #2b6cb0;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* ── Navigation links ────────────────────────────────────── */
        .nav-links {
            display: flex;
            gap: 14px;
            margin-top: 28px;
            justify-content: center;
        }
        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity .15s;
        }
        .btn:hover { opacity: .85; }
        .btn-primary { background: #4299e1; color: #fff; }
        .btn-ghost   { background: #edf2f7; color: #4a5568; }
    </style>
</head>
<body>

<!-- ── Top navigation bar ───────────────────────────────────────────────── -->
<div class="topbar">
    <span class="brand">📝 QuizPlatform</span>
    <div>
        Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
        &nbsp;|&nbsp;
        <a href="my_results.php">My Results</a>
        &nbsp;|&nbsp;
        <a href="leaderboard.php">Leaderboard</a>
    </div>
</div>

<div class="container">

    <!-- ── Score hero ─────────────────────────────────────────────────────── -->
    <div class="hero-card <?php echo $passed ? 'pass' : 'fail'; ?>">
        <div class="quiz-title"><?php echo htmlspecialchars($attempt['title']); ?></div>

        <div class="badge">
            <?php echo $passed ? '✅ PASSED' : '❌ FAILED'; ?>
        </div>

        <div class="score-big">
            <?php echo $score; ?><span style="font-size:2.5rem;opacity:.7;">/ <?php echo $total_marks; ?></span>
        </div>
        <div class="score-sub"><?php echo $percentage; ?>% — Pass mark is 60%</div>

        <div class="meta">
            <span><b><?php echo $percentage; ?>%</b>Score</span>
            <span><b><?php echo $duration; ?></b>Time Taken</span>
            <span><b><?php echo date('d M Y', strtotime($attempt['completed_at'])); ?></b>Date</span>
        </div>
    </div>

    <!-- ── Question-by-question breakdown ────────────────────────────────── -->
    <h2 class="section-title">Question Breakdown</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                    <th style="width:70px">Marks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($breakdown)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;color:#718096;padding:28px;">
                            No answers recorded for this attempt.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($breakdown as $i => $row): ?>
                        <tr>
                            <!-- Q number -->
                            <td><span class="q-num">Q<?php echo $i + 1; ?></span></td>

                            <!-- Question text -->
                            <td><?php echo htmlspecialchars($row['question_text']); ?></td>

                            <!-- Student's answer (green if correct, red if wrong) -->
                            <td class="answer-cell">
                                <?php if ($row['selected_text']): ?>
                                    <?php $isCorrect = (bool) $row['is_selected_correct']; ?>
                                    <span class="chip <?php echo $isCorrect ? 'correct' : 'wrong'; ?>">
                                        <?php echo $isCorrect ? '✓' : '✗'; ?>
                                        <?php echo htmlspecialchars($row['selected_text']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#a0aec0;font-style:italic;">Not answered</span>
                                <?php endif; ?>
                            </td>

                            <!-- Correct answer (always shown) -->
                            <td>
                                <span class="chip correct-ans">
                                    ✓ <?php echo htmlspecialchars($row['correct_text'] ?? '—'); ?>
                                </span>
                            </td>

                            <!-- Marks earned -->
                            <td>
                                <?php $earned = ($row['is_selected_correct'] ?? 0) ? $row['marks'] : 0; ?>
                                <span class="marks-badge">
                                    <?php echo $earned; ?>/<?php echo $row['marks']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Navigation ─────────────────────────────────────────────────────── -->
    <div class="nav-links">
        <a href="my_results.php" class="btn btn-ghost">← My Results</a>
        <a href="leaderboard.php" class="btn btn-primary">🏆 Leaderboard</a>
    </div>

</div><!-- /container -->
</body>
</html>
