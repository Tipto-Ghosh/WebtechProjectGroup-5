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