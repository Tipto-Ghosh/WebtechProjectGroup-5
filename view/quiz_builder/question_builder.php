<?php
include "../../controller/QuizBuilderController.php";

$controller = new QuizBuilderController();
$pageData = $controller->getQuestionBuilderData();
$quiz = isset($pageData["quiz"]) && is_array($pageData["quiz"]) ? $pageData["quiz"] : array();
$questions = isset($pageData["questions"]) && is_array($pageData["questions"]) ? $pageData["questions"] : array();
$pageTitle = isset($pageData["page_title"]) ? $pageData["page_title"] : "Question Builder";
$pageSubtitle = isset($pageData["page_subtitle"]) ? $pageData["page_subtitle"] : "Add, edit, and manage MCQ questions for your quiz";
$breadcrumbs = isset($pageData["breadcrumbs"]) && is_array($pageData["breadcrumbs"]) ? $pageData["breadcrumbs"] : array("My Quizzes", "Question Builder");

?>
<!DOCTYPE html>
<html lang="eng">
    <head>
        <title>QuizMaker</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../style/quiz_style_question_builder_page.css">
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
                    <div class="avatar">PR</div>
                    <div>
                        <div class="user_name">Prottoy Roy</div>
                        <div class="user_role">Instructor</div>
                    </div>
                </section>
            </aside>
            <div class="main">
                <header>
                    <div class="bread_crumb">
                        <?php echo htmlspecialchars($breadcrumbs[0] ?? "My Quizzes"); ?> <span class="breadcrumb_separator">&gt;</span> <strong><?php echo htmlspecialchars($breadcrumbs[1] ?? "Question Builder"); ?></strong>
                    </div>
                    <div class="header_actions">
                        <a class="btn btn_secondary" href="quiz_list.php">Back</a>
                        <button class="btn btn_primary" type="button"> Publish Quiz</button>
                    </div>
                </header>
                <main class="content question_builder_page">

                </main>
            </div>        
        </div>
    </body>
</html>