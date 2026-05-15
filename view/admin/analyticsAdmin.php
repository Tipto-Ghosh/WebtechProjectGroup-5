<?php
include_once __DIR__ . '/../../controller/analyticsAdminController.php';

function analyticsAdminInitials($name)
{
    $parts = preg_split('/\s+/', trim((string)$name));
    $initials = strtoupper(substr($parts[0] ?: 'A', 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr(end($parts), 0, 1));
    }
    return $initials;
}

function analyticsAdminNumber($value)
{
    return number_format((float)$value, ((float)$value == (int)$value) ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics - Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/adminDashboard.css">
    <link rel="stylesheet" href="../style/analyticsAdmin.css">
    
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar__logo">
            <span class="sidebar__logo-icon">&#9670;</span>
            <span class="sidebar__logo-text">QuizForge</span>
        </div>

        <nav class="sidebar__nav">
            <p class="sidebar__nav-label">Main</p>
            <a href="dashboard.php" class="sidebar__nav-item">
                <span class="sidebar__nav-icon">&#9632;</span>
                <span>Dashboard</span>
            </a>
            <a href="adminManageUser.php" class="sidebar__nav-item">
                <span class="sidebar__nav-icon">&#9679;</span>
                <span>User Management</span>
            </a>
            <!-- <a href="#" class="sidebar__nav-item">
                <span class="sidebar__nav-icon">&#9670;</span>
                <span>Quizzes</span>
            </a> -->
        </nav>

        <div class="sidebar__profile">
            <div class="sidebar__avatar"><?= htmlspecialchars(analyticsAdminInitials($admin_name)) ?></div>
            <div class="sidebar__profile-info">
                <p class="sidebar__profile-name"><?= htmlspecialchars($admin_name) ?></p>
                <p class="sidebar__profile-role">Admin</p>
            </div>
            <a href="../auth/login.php" class="sidebar__logout" title="Logout">&#10148;</a>
        </div>
    </aside>

    <main class="main" id="main-content">
        <header class="topbar">
            <button class="topbar__hamburger" id="btn-hamburger" aria-label="Toggle sidebar">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar__heading">
                <h1 class="topbar__title">Analytics</h1>
                <p class="topbar__subtitle">Quiz performance by instructor</p>
            </div>
        </header>

        <div class="content">
            <?php if (!empty($analytics_error)): ?>
                <div class="alert"><?= htmlspecialchars($analytics_error) ?></div>
            <?php endif; ?>

            <section class="analytics-widgets" aria-label="Analytics summary">
                <article class="metric-card metric-card--orange">
                    <p class="metric-label">Instructors</p>
                    <span class="metric-value"><?= number_format((int)$overview["total_instructors"]) ?></span>
                    <p class="metric-note">active teaching accounts</p>
                </article>
                <article class="metric-card metric-card--dark">
                    <p class="metric-label">Quizzes</p>
                    <span class="metric-value"><?= number_format((int)$overview["total_quizzes"]) ?></span>
                    <p class="metric-note">created across platform</p>
                </article>
                <article class="metric-card">
                    <p class="metric-label">Attempts</p>
                    <span class="metric-value"><?= number_format((int)$overview["total_attempts"]) ?></span>
                    <p class="metric-note">completed submissions</p>
                </article>
                <article class="metric-card">
                    <p class="metric-label">Average Score</p>
                    <span class="metric-value"><?= analyticsAdminNumber($overview["average_percent"]) ?>%</span>
                    <p class="metric-note">all completed attempts</p>
                </article>
            </section>

            <section class="analytics-panel">
                <div class="panel-toolbar">
                    <div class="search-wrap">
                        <span class="search-icon">&#128269;</span>
                        <input type="search" id="quizSearch" class="search-input" placeholder="Search quiz or instructor" value="<?= htmlspecialchars($search_q ?? '') ?>" autocomplete="off">
                    </div>

                    <select id="instructorFilter" class="filter-select" aria-label="Filter by instructor">
                        <option value="all">All instructors</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?= (int)$instructor["id"] ?>" <?= ((int)($selected_instructor ?? 0) === (int)$instructor["id"]) ? "selected" : "" ?>>
                                <?= htmlspecialchars($instructor["name"]) ?> (<?= (int)$instructor["quiz_count"] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="status-tabs" aria-label="Filter by quiz status">
                        <button type="button" class="status-tab active" data-status="all">All</button>
                        <button type="button" class="status-tab" data-status="published">Published</button>
                        <button type="button" class="status-tab" data-status="draft">Draft</button>
                    </div>

                    <div class="quiz-count" id="quizCount">
                        Showing <span><?= count($quizzes) ?></span> quiz<?= count($quizzes) === 1 ? "" : "zes" ?>
                    </div>
                </div>

                <div class="quiz-table-wrap">
                    <table class="quiz-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Quiz</th>
                                <th>Instructor</th>
                                <th>Status</th>
                                <th>Questions</th>
                                <th>Attempts</th>
                                <th>Average</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="quizTableBody">
                            <?php foreach ($quizzes as $index => $quiz): ?>
                                <?php
                                    $searchText = strtolower($quiz["title"] . " " . $quiz["instructor_name"] . " " . $quiz["instructor_email"]);
                                ?>
                                <tr
                                    data-quiz-id="<?= (int)$quiz["id"] ?>"
                                    data-instructor-id="<?= (int)$quiz["instructor_id"] ?>"
                                    data-status="<?= htmlspecialchars($quiz["status"]) ?>"
                                    data-search="<?= htmlspecialchars($searchText) ?>"
                                >
                                    <td>#<?= $index + 1 ?></td>
                                    <td>
                                        <div class="quiz-title"><?= htmlspecialchars($quiz["title"]) ?></div>
                                        <div class="quiz-meta"><?= (int)$quiz["total_marks"] ?> marks</div>
                                    </td>
                                    <td>
                                        <div class="quiz-title"><?= htmlspecialchars($quiz["instructor_name"]) ?></div>
                                        <div class="instructor-email"><?= htmlspecialchars($quiz["instructor_email"]) ?></div>
                                    </td>
                                    <td><span class="quiz-status <?= htmlspecialchars($quiz["status"]) ?>"><?= htmlspecialchars($quiz["status"]) ?></span></td>
                                    <td><?= (int)$quiz["question_count"] ?></td>
                                    <td><?= htmlspecialchars($quiz["attempt_label"]) ?></td>
                                    <td><?= htmlspecialchars($quiz["average_display"]) ?></td>
                                    <td><?= htmlspecialchars($quiz["created_at_display"]) ?></td>
                                    <td>
                                        <button type="button" class="btn-view-analytics" data-quiz-id="<?= (int)$quiz["id"] ?>">View</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div id="quizEmptyState" class="empty-state <?= empty($quizzes) ? "" : "hidden" ?>">
                    No quizzes found.
                </div>
            </section>

            <section class="analytics-detail" id="analyticsDetail" data-selected-quiz="<?= $selected_quiz > 0 ? (int)$selected_quiz : "" ?>">
                <?php if ($selected_quiz_data && $analytics_summary): ?>
                    <div class="detail-header">
                        <div>
                            <p class="eyebrow">Selected Quiz</p>
                            <h2><?= htmlspecialchars($selected_quiz_data["title"]) ?></h2>
                            <p><?= htmlspecialchars($selected_quiz_data["instructor_name"]) ?> &bull; <?= htmlspecialchars($selected_quiz_data["instructor_email"]) ?></p>
                        </div>
                        <span class="quiz-status <?= htmlspecialchars($selected_quiz_data["status"]) ?>"><?= htmlspecialchars($selected_quiz_data["status"]) ?></span>
                    </div>

                    <div class="summary-strip">
                        <div class="summary-cell">
                            <span><?= (int)$analytics_summary["attempt_count"] ?></span>
                            <p>Attempts</p>
                        </div>
                        <div class="summary-cell">
                            <span><?= htmlspecialchars($analytics_summary["average"]) ?></span>
                            <p>Average</p>
                        </div>
                        <div class="summary-cell">
                            <span><?= htmlspecialchars($analytics_summary["highest"]) ?></span>
                            <p>Highest</p>
                        </div>
                        <div class="summary-cell">
                            <span><?= htmlspecialchars($analytics_summary["lowest"]) ?></span>
                            <p>Lowest</p>
                        </div>
                        <div class="summary-cell">
                            <span><?= htmlspecialchars($analytics_summary["pass_rate"]) ?>%</span>
                            <p>Pass Rate</p>
                        </div>
                    </div>

                    <div class="attempt-table-wrap">
                        <table class="attempt-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Score</th>
                                    <th>Percent</th>
                                    <th>Duration</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($attempts)): ?>
                                    <?php foreach ($attempts as $attempt): ?>
                                        <tr>
                                            <td>#<?= (int)$attempt["row_number"] ?></td>
                                            <td>
                                                <div class="student-name"><?= htmlspecialchars($attempt["student_name"]) ?></div>
                                                <div class="student-email"><?= htmlspecialchars($attempt["student_email"]) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($attempt["score_display"]) ?></td>
                                            <td><?= htmlspecialchars($attempt["percent_display"]) ?></td>
                                            <td><?= htmlspecialchars($attempt["duration_display"]) ?></td>
                                            <td><?= htmlspecialchars($attempt["completed_at_display"]) ?></td>
                                            <td><span class="result-badge <?= htmlspecialchars($attempt["status_class"]) ?>"><?= htmlspecialchars($attempt["status_label"]) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="empty-row">
                                        <td colspan="7">No completed attempts found for this quiz.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="detail-empty">Select a quiz to view analytics.</div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <div class="toast-container" id="toastContainer"></div>
    <script src="../../controller/js/analyticsAdminajax.js"></script>
</body>
</html>
