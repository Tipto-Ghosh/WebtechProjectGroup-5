<?php
include_once __DIR__ . '/../../controller/instructorDashboardController.php';

function formatDateLabel($dateTime){
    if (!$dateTime){
       return "-";
    } 
    $ts = strtotime($dateTime);
    return $ts !== false ? date("M j, Y", $ts) : "-";
}

function getQuizMetaText($quiz){
    $qCount = (int)($quiz['question_count'] ?? 0);
    $time   = (int)($quiz['time_limit_minutes'] ?? 0);
    if ($qCount == 0) return "No questions added yet";
    return $time . " min · " . $qCount . " question" . ($qCount == 1 ? "" : "s");
}

function getStatusBadgeClass($status){
    return $status == "published" ? "badge-published" : "badge-draft";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz Forge - Instructor Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/instructorDashboard.css">
</head>
<body>
<div class="app">
    <aside class="sidebar" aria-label="Sidebar navigation">
        <section class="sidebar-brand">
            <div class="brand-title">QuizMaker</div>
            <div class="brand-subtitle">Instructor Panel</div>
        </section>

        <nav class="nav_section" aria-label="Workspace">
            <div class="nav-label">Workspace</div>
            <a class="nav-item active" href="dashboard.php" aria-current="page">Dashboard</a>
            <a class="nav-item" href="../Results_Leaderboard/analytics.php">Analytics</a>
        </nav>

        <nav class="nav_section" aria-label="Account">
            <!--<div class="nav-label">Account</div>-->
            <!--<a class="nav_item" href="#">Profile</a>-->
            <a class="nav_item nav_item_danger" href="../auth/login.php">Log Out</a>
        </nav>

        <section class="sidebar_user" aria-label="Current user">
            <div class="avatar"><?= htmlspecialchars(strtoupper(substr($instructor_name, 0, 1))) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($instructor_name) ?></div>
                <div class="user-role"><?= htmlspecialchars($instructor_role) ?></div>
            </div>
        </section>
    </aside>

    <!-- main content -->
    <div class="main">
        <header>
            <div class="bread_crumb">
                Dashboard <span class="breadcrumb_separator">></span> <strong>My Quizzes</strong>
            </div>
            <div class="header_actions">
                <a class="btn btn_primary" href="quiz_form.php?quiz_id=0">Create Quiz</a>
            </div>
        </header>

        <main class="content">
            <section class="page_head">
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
                <div class="page_subtitle"><?= htmlspecialchars($pageSubtitle) ?></div>
            </section>

            <!-- statistics -->
            <section>
                <article class="stat_card">
                    <div class="stat_label">Total Quizzes</div>
                    <div class="stat_value"><?= (int)$stats['total_quizzes'] ?></div>
                </article>
                <article class="stat_card">
                    <div class="stat_label">Published Quizzes</div>
                    <div class="stat_value"><?= (int)$stats['published_quizzes'] ?></div>
                </article>
                <article class="stat_card">
                    <div class="stat_label">Draft Quizzes</div>
                    <div class="stat_value"><?= (int)$stats['draft_quizzes'] ?></div>
                </article>
                <article class="stat_card">
                    <div class="stat_label">Total Attempts</div>
                    <div class="stat_value"><?= (int)$stats['total_attempts'] ?></div>
                </article>
                <article class="stat_card">
                    <div class="stat_label">Average Score</div>
                    <div class="stat_value"><?= $stats['average_score'] ?><span class="stat_percentage_symbol">%</span></div>
                </article>
            </section>

            <!-- Quiz list -->
            <section>
                <h2>My Quizzes</h2>
                <p>Manage your quizzes, view details, and monitor performance.</p>

                <div class="toolbar">
                    <label>
                        <span class="sr_only">Search quizzes</span>
                        <input class="search" type="search" id="quizSearch" placeholder="Search quizzes...">
                    </label>
                    <div class="filter_group">
                        <span class="table-meta">Filter:</span>
                        <span class="tag all active">All</span>
                        <span class="tag draft">Draft</span>
                        <span class="tag published">Published</span>
                    </div>
                </div>

                <div class="table_head" role="row">
                    <div>Quiz Title</div>
                    <div>Questions</div>
                    <div>Total Marks</div>
                    <div>Time Limit</div>
                    <div>Status</div>
                    <div>Created</div>
                    <div class="table_head_actions">Actions</div>
                </div>

                <?php if (empty($quizzes)): ?>
                    <div class="empty_state">
                        <h2 class="empty_title">No quizzes found</h2>
                        <p>Start by creating your first quiz from the button above.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <article class="table_row"data-title="<?= strtolower(htmlspecialchars($quiz['title'])) ?>"data-status="<?= htmlspecialchars($quiz['status']) ?>">
                            <div>
                                <div class="quiz_title"><?= htmlspecialchars($quiz['title']) ?></div>
                                <div class="quiz_meta"><?= htmlspecialchars(getQuizMetaText($quiz)) ?></div>
                            </div>
                            <div><?= (int)$quiz['question_count'] ?></div>
                            <div><?= (int)$quiz['total_marks'] ?></div>
                            <div><?= (int)$quiz['time_limit_minutes'] ?> min</div>
                            <div>
                                <span class="badge <?= getStatusBadgeClass($quiz['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($quiz['status'])) ?>
                                </span>
                            </div>
                            <div><?= htmlspecialchars(formatDateLabel($quiz['created_at'] ?? null)) ?></div>
                            <div class="actions">
                                <a href="quiz_form.php?quiz_id=<?= (int)$quiz['id'] ?>" class="btn_secondary">Edit</a>
                                <?php $isPublished = (($quiz['status'] ?? 'draft') === 'published'); ?>
                                <button class="btn btn_toggle" type="button" data-quiz-id="<?= (int)$quiz['id'] ?>" data-quiz-status="<?= $isPublished ? 'published' : 'draft' ?>" onclick="toggleQuiz(this)"><?= $isPublished ? 'Unpublish' : 'Publish' ?></button>
                                <button class="btn btn-delete" type="button" data-quiz-id="<?= (int)$quiz['id'] ?>" onclick="deleteQuiz(this)">Delete</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
<script src="../../controller/js/quiz_builder.js"></script>
<script src="../../controller/js/instructorDashboardSearchAjax.js"></script>
</body>
</html>