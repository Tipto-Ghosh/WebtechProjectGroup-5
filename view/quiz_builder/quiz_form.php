<?php
include "../../controller/QuizBuilderController.php";

$controller = new QuizBuilderController();
$pageData = $controller->getQuizFormData();
$quiz = isset($pageData["quiz"]) && is_array($pageData["quiz"]) ? $pageData["quiz"] : array();
$pageTitle = isset($pageData["page_title"]) ? $pageData["page_title"] : "Create New Quiz";
$pageSubtitle = isset($pageData["page_subtitle"]) ? $pageData["page_subtitle"] : "Fill in the details - you'll add questions in the next step";
$mode = isset($pageData["mode"]) ? $pageData["mode"] : "create";
$primaryActionLabel = isset($pageData["primary_action_label"]) ? $pageData["primary_action_label"] : "Save & Add Questions";
$breadcrumbs = isset($pageData["breadcrumbs"]) && is_array($pageData["breadcrumbs"]) ? $pageData["breadcrumbs"] : array("My Quizzes", $pageTitle);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
		<title>QuizMaker</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="../style/quiz_style_quiz_form_page.css">
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
                    <a class="nav-item active" href="quiz_list.php">My Quizzes <span class="nav_badge">7</span></a>
                    <a class="nav-item" href="#">Analytics</a>
                </nav>

                <section class="sidebar_user" aria-label="Current user">
                    <div class="user_name">Prottoy Roy</div>
                    <div class="user_role">Instructor</div>
                </section>
            </aside>
        </div>    
    </body>
</html>