<?php

//declare(strict_types=1);

// include "../model/db.php";

class QuizBuilderController
{
    public function getQuizFormData(): array
    {
        $mode = isset($_GET["mode"]) && $_GET["mode"] === "edit" ? "edit" : "create";

        $quiz = array(
            "id" => isset($_GET["quiz_id"]) ? (int) $_GET["quiz_id"] : 0,
            "title" => $_GET["title"] ?? "",
            "description" => $_GET["description"] ?? "",
            "total_marks" => isset($_GET["total_marks"]) ? (int) $_GET["total_marks"] : 0,
            "time_limit_minutes" => isset($_GET["time_limit_minutes"]) ? (int) $_GET["time_limit_minutes"] : 60,
            "status" => $_GET["status"] ?? "draft",
            "question_count" => isset($_GET["question_count"]) ? (int) $_GET["question_count"] : 0,
        );

        if ($quiz["title"] === "") {
            $quiz["title"] = "Web Technologies Final Exam";
        }

        if ($quiz["description"] === "") {
            $quiz["description"] = "Covers HTML5, CSS3, JavaScript fundamentals, PHP basics, and database concepts covered in the semester.";
        }

        return array(
            "mode" => $mode,
            "page_title" => $mode === "edit" ? "Edit Quiz" : "Create New Quiz",
            "page_subtitle" => $mode === "edit" ? "Update quiz details before managing questions" : "Fill in the details - you'll add questions in the next step",
            "quiz" => $quiz,
            "breadcrumbs" => array("My Quizzes", $mode === "edit" ? "Edit Quiz" : "Create New Quiz"),
            "primary_action_label" => $mode === "edit" ? "Save Changes" : "Save & Add Questions",
        );
    }

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