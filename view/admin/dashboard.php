<?php
include_once __DIR__ . '/../../controller/adminDashboardController.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>QuizForge — Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../style/adminDashboard.css" />
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar__logo">
            <span class="sidebar__logo-icon">&#9670;</span>
            <span class="sidebar__logo-text">QuizForge</span>
        </div>

        <nav class="sidebar__nav">
            <p class="sidebar__nav-label">Main</p>
            <a href="dashboard.php" class="sidebar__nav-item sidebar__nav-item--active" data-page="dashboard">
                <span class="sidebar__nav-icon">&#9632;</span>
                <span>Dashboard</span>
            </a>
            
            <a href="#" class="sidebar__nav-item" data-page="quizzes">
                <span class="sidebar__nav-icon">&#9670;</span>
                <span>Quizzes</span>
            </a>
            <a href="#" class="sidebar__nav-item" data-page="attempts">
                <span class="sidebar__nav-icon">&#9679;</span>
                <span>Attempts</span>
            </a>
            <p class="sidebar__nav-label">System</p>
            <a href="../admin/analyticsAdmin.php" class="sidebar__nav-item" data-page="analytics">
                <span class="sidebar__nav-icon">&#9651;</span>
                <span>Analytics</span>
            </a>
        </nav>

        <div class="sidebar__profile">
            <div class="sidebar__avatar" id="sidebar-avatar"><?= htmlspecialchars(substr($admin_name, 0, 1)) ?></div>
            <div class="sidebar__profile-info">
                <p class="sidebar__profile-name" id="admin-name"><?= htmlspecialchars($admin_name) ?></p>
                <p class="sidebar__profile-role">Admin</p>
            </div>
            <a href="../auth/login.php" class="sidebar__logout" title="Logout">&#10148;</a>
        </div>
    </aside>

    <!-- main contents -->
    <main class="main" id="main-content">
        <header class="topbar">
            <button class="topbar__hamburger" id="btn-hamburger" aria-label="Toggle sidebar">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar__heading">
                <h1 class="topbar__title">Command Center</h1>
                <p class="topbar__subtitle">Platform overview &amp; live stats</p>
            </div>
            <div class="topbar__actions">
                <span class="topbar__date" id="topbar-date"></span>
                <button class="topbar__notif" id="btn-notif" aria-label="Notifications">
                    &#9741;
                    <span class="topbar__notif-dot" id="notif-dot" style="display:none;"></span>
                </button>
            </div>
        </header>

        <!-- summary widgets -->
        <section class="widgets" id="section-widgets">
            <!-- students -->
            <article class="widget widget--orange" data-metric="students">
                <div class="widget__top">
                    <span class="widget__icon">&#127891;</span>
                    <span class="widget__trend" id="trend-students">—</span>
                </div>
                <p class="widget__value" id="count-students"><?= number_format($total_students) ?></p>
                <p class="widget__label">Students</p>
            </article>

            <!-- Instructors -->
            <article class="widget widget--dark" data-metric="instructors">
                <div class="widget__top">
                    <span class="widget__icon">&#9998;</span>
                    <span class="widget__trend" id="trend-instructors">—</span>
                </div>
                <p class="widget__value" id="count-instructors"><?= number_format($total_instructors) ?></p>
                <p class="widget__label">Instructors</p>
            </article>

            <!-- Live Quizzes -->
            <article class="widget widget--light" data-metric="live-quizzes">
                <div class="widget__top">
                    <span class="widget__icon">&#9632;</span>
                    <span class="widget__trend widget__trend--live" id="trend-quizzes">● LIVE</span>
                </div>
                <p class="widget__value" id="count-quizzes"><?= number_format($live_quizzes) ?></p>
                <p class="widget__label">Live Quizzes</p>
            </article>

            <!-- Total Attempts -->
            <article class="widget widget--orange-outline" data-metric="attempts">
                <div class="widget__top">
                    <span class="widget__icon">&#9650;</span>
                    <span class="widget__trend" id="trend-attempts">—</span>
                </div>
                <p class="widget__value" id="count-attempts"><?= number_format($total_attempts) ?></p>
                <p class="widget__label">Total Attempts</p>
            </article>
        </section>

        <!-- performance and system row -->
        <section class="perf-row" id="section-perf">
            <!-- Platform Performance -->
            <article class="perf-card" id="card-performance">
                <h2 class="perf-card__title">Platform Performance</h2>
                <div class="gauge" id="gauge-score" style="--pct: <?= htmlspecialchars($avg_score_percent) ?>;">
                    <svg class="gauge__svg" viewBox="0 0 120 120" aria-hidden="true">
                        <circle class="gauge__track" cx="60" cy="60" r="50"/>
                        <circle class="gauge__fill"  cx="60" cy="60" r="50" id="gauge-circle"/>
                    </svg>
                    <div class="gauge__label">
                        <span class="gauge__value" id="avg-score"><?= htmlspecialchars($avg_score_percent) ?></span>
                        <span class="gauge__unit">%</span>
                    </div>
                </div>
                <p class="perf-card__desc">Average score across all completed attempts</p>
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

            <!-- System Status -->
            <article class="status-card" id="card-status">
                <h2 class="status-card__title">System Status</h2>
                <ul class="status-list">
                    <li class="status-list__item status-list__item--ok">
                        <span class="status-list__dot"></span>
                        <span class="status-list__key">Active Accounts</span>
                        <span class="status-list__val" id="count-active"><?= number_format($active_accounts) ?></span>
                    </li>
                    <li class="status-list__item status-list__item--warn">
                        <span class="status-list__dot"></span>
                        <span class="status-list__key">Suspended</span>
                        <span class="status-list__val" id="count-suspended"><?= number_format($suspended_accounts) ?></span>
                    </li>
                    <li class="status-list__item status-list__item--info">
                        <span class="status-list__dot"></span>
                        <span class="status-list__key">Admins</span>
                        <span class="status-list__val" id="count-admins"><?= number_format($total_admins) ?></span>
                    </li>
                    <li class="status-list__item status-list__item--info">
                        <span class="status-list__dot"></span>
                        <span class="status-list__key">Published Quizzes</span>
                        <span class="status-list__val" id="count-published"><?= number_format($live_quizzes) ?></span>
                    </li>
                    <li class="status-list__item status-list__item--muted">
                        <span class="status-list__dot"></span>
                        <span class="status-list__key">Draft Quizzes</span>
                        <span class="status-list__val" id="count-drafts"><?= number_format($draft_quizzes) ?></span>
                    </li>
                </ul>
                <a href="adminManageUser.php" class="status-card__cta" id="btn-goto-users">Manage Users &nbsp;&#10148;</a>
            </article>

            <!-- Recent Registrations -->
            <article class="recent-card" id="card-recent-users">
                <h2 class="recent-card__title">Recent Registrations</h2>
                <table class="recent-table" id="table-recent-users">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-recent-users">
                        <?= $recent_users_rows ?>
                    </tbody>
                </table>
                <a href="adminManageUser.php" class="recent-card__more" id="btn-all-users">View all users &#10148;</a>
            </article>
        </section>
    </main>
    <script src="../../controller/js/adminDashboard.js"></script>
</body>
</html>