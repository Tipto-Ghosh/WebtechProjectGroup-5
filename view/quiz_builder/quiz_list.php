<?php

include "../../controller/QuizBuilderController.php";

$controller = new QuizBuilderController();
$pageData = $controller->getQuizListData();
$quizzes = isset($pageData['quizzes']) ? $pageData['quizzes'] : array();
$stats = isset($pageData['stats']) ? $pageData['stats'] : array(
	'total_quizzes' => 0,
	'published_quizzes' => 0,
	'draft_quizzes' => 0,
	'total_attempts' => 0,
	'average_score' => 0,
);
$pageTitle = isset($pageData['page_title']) ? $pageData['page_title'] : '';
$pageSubtitle = isset($pageData['page_subtitle']) ? $pageData['page_subtitle'] : '';
$errorMessage = isset($pageData['error']) ? $pageData['error'] : null;
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>
			Quiz Maker
		</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="../../controller/style/quiz_builder_quiz_list.css">
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
					<a class="nav-item" href="#">Dashboard</a>
					<a class="nav-item active" href="quiz_list.php" aria-current="page">My Quizzes <span class="nav_badge"><?php echo (int) $stats['total_quizzes']; ?></span></a>
					<a class="nav-item" href="#">Analytics</a>
				</nav>

				<nav class="nav_section" aria-label="Account">
					<div class="nav-label">Account</div>
					<a class="nav_item" href="#">Profile</a>
					<a class="nav_item" href="#">Settings</a>
					<a class="nav_item nav_item_danger" href="#">Sign Out</a>
				</nav>

				<section class="sidebar_user" aria-label="Current user">
					<div class="avatar">PR</div>
					<div>
						<div class="user-name">Prottoy Roy</div>
						<div class="user-role">Instructor</div>
					</div>
				</section>
			</aside>

			<div class="main">
				<header>
					<div class="bread_crumb">Dashboard <span class="breadcrumb_separator"></span> <strong>My Quizzes</strong></div>
					<div class="header_actions">
						<button class="btn btn_secondary" type="button">Export</button>
						<button class="btn btn_primary" type="button">Create Quiz</button>
					</div>
				</header>

				<main class="content">
					<section class="page_head">
						<h1><?php echo htmlspecialchars($pageTitle); ?></h1>
						<div class="page_subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></div>
					</section>

					<?php if ($errorMessage !== null): ?>
						<div class="error_message"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>

					<section>
						<article class="stat_card">
							<div class="stat_label">Total Quizzes</div>
							<div class="stat_value"><?php echo (int) $stats['total_quizzes']; ?></div>
						</article>
						<article class="stat_card">
							<div class="stat_label">Published Quizzes</div>
							<div class="stat_value"><?php echo (int) $stats['published_quizzes']; ?></div>
						</article>
						<article class="stat_card">
							<div class="stat_label">Draft Quizzes</div>
							<div class="stat_value"><?php echo (int) $stats['draft_quizzes']; ?></div>
						</article>
						<article class="stat_card">
							<div class="stat_label">Total Attempts</div>
							<div class="stat_value"><?php echo (int) $stats['total_attempts']; ?></div>
						</article>
						<article class="stat_card">
							<div class="stat_label">Average Score</div>
							<div class="stat_value"><?php echo (int) $stats['average_score']; ?><span class="stat_percentage_symbol">%</span></div>
						</article>
					</section>

					<section>
						<h2>My Quizzes</h2>
						<p>Manage your quizzes, view details, and monitor performance.</p>
						
						<div class="toolbar">
							<label>
								<span class="sr_only">Search quizzes</span>
								<input class="search" type="search" placeholder="Search quizzes...">
							</label>
							<div class="filter_group">
								<span class="table-meta">Filter:</span>
								<span class="tag all">All</span>
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

					</section>
					<!-- Quiz list table would go here -->
					<?php if (empty($quizzes)): ?>
						<div class="empty_state">
							<h2 class="empty_title">No quizzes found</h2>
							<p>Start by creating your first quiz from the button above.</p>
						</div>
					<?php else: ?>
					<?php foreach ($quizzes as $quiz): ?>
						<article class="table_row">
							<div>
								<div class="quiz_title"><?php echo htmlspecialchars($quiz['title']); ?></div>
								<div class="quiz_meta"><?php echo htmlspecialchars(getQuizMetaText($quiz)); ?></div>
							</div>
							<div><?php echo (int) $quiz['question_count']; ?></div>
							<div><?php echo (int) $quiz['total_marks']; ?></div>
							<div><?php echo (int) $quiz['time_limit_minutes']; ?> min</div>
							<div>
								<span class="badge <?php echo getStatusBadgeClass((string) $quiz['status']); ?>">
									<?php echo ucfirst(htmlspecialchars((string) $quiz['status'])); ?>
								</span>
							</div>
							<div><?php echo htmlspecialchars(formatDateLabel($quiz['created_at'] ?? null)); ?></div>
							<div class="actions">
								<button class="btn_secondary" type="button">Edit</button>
								<button class="btn_secondary" type="button">Questions</button>
								<button class="btn_status_toggle" type="button">
									<?php echo htmlspecialchars(getActionButtonLabel((string) $quiz['status'])); ?>
								</button>
								<button class="btn btn-delete" type="button">Delete</button>
							</div>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</main>
			<footer>
				Quiz list view built with a simple MVC structure, semantic HTML, and database-backed data.
			</footer>
		</div>
	</body>
</html>