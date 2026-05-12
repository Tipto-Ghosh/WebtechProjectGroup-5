<?php

declare(strict_types=1);

include "/../../controller/QuizBuilderController.php";

$controller = new QuizBuilderController();
$pageData = $controller->getQuizListData();
$quizzes = $pageData["quizzes"];
$stats = $pageData["stats"];
$pageTitle = $pageData["page_title"];
$pageSubtitle = $pageData["page_subtitle"];
$errorMessage = $pageData["error"] ?? null;

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>QuizForge | <?php echo htmlspecialchars($pageTitle); ?></title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="../../controller/style/quiz_builder_quiz_list.css">
	</head>
	<body>
		<div class="app">
			<aside class="sidebar" aria-label="Sidebar navigation">
				<section class="sidebar-brand">
					<div class="brand-title">QuizForge</div>
					<div class="brand-subtitle">Instructor Panel</div>
				</section>

				<nav class="nav-section" aria-label="Workspace">
					<div class="nav-label">Workspace</div>
					<a class="nav-item" href="#">Dashboard</a>
					<a class="nav-item active" href="quiz_list.php" aria-current="page">My Quizzes <span class="nav-badge"><?php echo (int) $stats['total_quizzes']; ?></span></a>
					<a class="nav-item" href="#">Analytics</a>
				</nav>

				<nav class="nav-section" aria-label="Account">
					<div class="nav-label">Account</div>
					<a class="nav-item" href="#">Profile</a>
					<a class="nav-item" href="#">Settings</a>
					<a class="nav-item nav-item-danger" href="#">Sign Out</a>
				</nav>

				<section class="sidebar-user" aria-label="Current user">
					<div class="avatar">SR</div>
					<div>
						<div class="brand-title user-name">Sarah Rahman</div>
						<div class="user-role">Instructor</div>
					</div>
				</section>
			</aside>
		</div>
	</body>
</html>