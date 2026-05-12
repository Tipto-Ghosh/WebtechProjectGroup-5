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

					<!-- Quiz list table would go here -->
				</main>
		</div>
	</body>
</html>