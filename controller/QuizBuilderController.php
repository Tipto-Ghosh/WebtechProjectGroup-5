<?php

//declare(strict_types=1);

// include "../model/db.php";

class QuizBuilderController
{
    public function getQuizListData(): array
    {
        $quizzes = array();
        $stats = array(
            "total_quizzes" => 0,
            "published_quizzes" => 0,
            "draft_quizzes" => 0,
            "total_attempts" => 0,
            "average_score" => 0,
        );

        return array(
            "quizzes" => $quizzes,
            "stats" => $stats,
            "page_title" => "My Quizzes",
            "page_subtitle" => "Manage, publish, and monitor your quiz library",
        );
    }
}