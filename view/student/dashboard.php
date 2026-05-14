<?php
include_once __DIR__ . '/../../controller/studentDashboardController.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>QuizForge — Student Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="../style/studentDashboard.css" />
</head>
<body>

    <!-- left side bar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar__logo">
            <span class="sidebar__logo-icon">&#9670;</span>
            <span class="sidebar__logo-text">Quiz-Forge</span>
        </div>

        <nav class="sidebar__nav">
            <p class="sidebar__nav-label">Navigation</p>
            <a href="dashboard.php" class="sidebar__nav-item sidebar__nav-item--active" data-page="dashboard">
                <span class="sidebar__nav-icon">&#9632;</span>
                <span>Dashboard</span>
            </a>
            <a href="../Results_Leaderboard/my_results.php" class="sidebar__nav-item" data-page="my-results">
                <span class="sidebar__nav-icon">&#9650;</span>
                <span>My Results</span>
            </a>
            <a href="../Results_Leaderboard/leaderboard.php" class="sidebar__nav-item" data-page="leaderboard">
                <span class="sidebar__nav-icon">&#9733;</span>
                <span>Leaderboard</span>
            </a>
            <a href="../student/quiz_list.php" class="sidebar__nav-item" data-page="view-quizzes">
                <span class="sidebar__nav-icon">&#9670;</span>
                <span>View Quizzes</span>
            </a>
            <!-- Profile part is not done(pore time paile) -->
            <a href="#" class="sidebar__nav-item" data-page="profile">
                <span class="sidebar__nav-icon">&#9787;</span>
                <span>Profile</span>
            </a>
        </nav>

        <div class="sidebar__profile">
            <div class="sidebar__avatar"><?= htmlspecialchars(strtoupper(substr($student_name, 0, 1))) ?></div>
            <div class="sidebar__profile-info">
                <p class="sidebar__profile-name"><?= htmlspecialchars($student_name) ?></p>
                <p class="sidebar__profile-role"><?= htmlspecialchars($student_role) ?></p>
            </div>
            <a href="../auth/login.php" class="sidebar__logout" title="Logout">&#10148;</a>
        </div>
    </aside>

    <!-- main page contents -->
    <main class="main" id="main-content">
        <header class="topbar">
            <button class="topbar__hamburger" id="btn-hamburger" aria-label="Toggle sidebar">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar__heading">
                <h1 class="topbar__title">Student Dashboard</h1>
                <p class="topbar__subtitle">Your learning at a glance</p>
            </div>
            <div class="topbar__actions">
                <span class="topbar__date" id="topbar-date"></span>
            </div>
        </header>

        <!-- summary widgets -->
        <section class="widgets" id="section-widgets">
            <!-- Published Quizzes -->
            <article class="widget widget--orange" data-metric="published">
                <div class="widget__top">
                    <span class="widget__icon">&#128218;</span>
                    <span class="widget__trend">—</span>
                </div>
                <p class="widget__value" id="count-published"><?= number_format($total_published_quizzes) ?></p>
                <p class="widget__label">Published Quizzes</p>
                <p class="widget__sub">Total available on platform</p>
            </article>

            <!-- Quizzes Taken -->
            <article class="widget widget--dark" data-metric="taken">
                <div class="widget__top">
                    <span class="widget__icon">&#10004;</span>
                    <span class="widget__trend">—</span>
                </div>
                <p class="widget__value" id="count-taken"><?= number_format($total_taken_quizzes) ?></p>
                <p class="widget__label">Quizzes Taken</p>
                <p class="widget__sub">Completed attempts</p>
            </article>

            <!-- Available Now -->
            <article class="widget widget--light" data-metric="available">
                <div class="widget__top">
                    <span class="widget__icon">&#9654;</span>
                    <span class="widget__trend widget__trend--live">&#9679; AVAILABLE</span>
                </div>
                <p class="widget__value" id="count-available"><?= number_format($available_quizzes) ?></p>
                <p class="widget__label">Quizzes You Can Take</p>
                <p class="widget__sub">Not yet attempted</p>
            </article>

            <!-- Total Attempts -->
            <article class="widget widget--orange-outline" data-metric="attempts">
                <div class="widget__top">
                    <span class="widget__icon">&#8635;</span>
                    <span class="widget__trend">—</span>
                </div>
                <p class="widget__value" id="count-attempts"><?= number_format($total_attempts) ?></p>
                <p class="widget__label">Total Attempts</p>
                <p class="widget__sub">All attempts (incl. incomplete)</p>
            </article>
        </section>

        <!-- performance -->
        <section class="perf-row" id="section-perf">
            <!-- student performance -->
            <article class="perf-card" id="card-performance">
                <h2 class="perf-card__title">Your Performance</h2>
                <div class="gauge" id="gauge-score" style="--pct: <?= htmlspecialchars($avg_score) ?>;">
                    <svg class="gauge__svg" viewBox="0 0 120 120" aria-hidden="true">
                        <circle class="gauge__track" cx="60" cy="60" r="50"/>
                        <circle class="gauge__fill"  cx="60" cy="60" r="50" id="gauge-circle"/>
                    </svg>
                    <div class="gauge__label">
                        <span class="gauge__value" id="avg-score"><?= htmlspecialchars($avg_score) ?></span>
                        <span class="gauge__unit">%</span>
                    </div>
                </div>
                <p class="perf-card__desc">Average score across all completed quizzes</p>
                <div class="perf-card__breakdown">
                    <div class="breakdown-cell">
                        <span class="breakdown-cell__val" id="score-high"><?= htmlspecialchars($score_high) ?></span>
                        <span class="breakdown-cell__key">Highest</span>
                    </div>
                    <div class="breakdown-cell">
                        <span class="breakdown-cell__val" id="score-low"><?= htmlspecialchars($score_low) ?></span>
                        <span class="breakdown-cell__key">Lowest</span>
                    </div>
                    <div class="breakdown-cell">
                        <span class="breakdown-cell__val" id="score-median"><?= htmlspecialchars($score_median) ?></span>
                        <span class="breakdown-cell__key">Median</span>
                    </div>
                </div>
            </article>
        </section>
    </main>

    <!-- for gauge and other animation -->
    <script src="../../controller/js/adminDashboard.js"></script>
</body>
</html>