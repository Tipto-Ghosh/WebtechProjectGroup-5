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

    public function getQuestionBuilderData(): array
    {
        $quiz_id = isset($_GET["quiz_id"]) ? (int) $_GET["quiz_id"] : 0;

        $quiz = array(
            "id" => $quiz_id,
            "title" => $_GET["title"] ?? "Web Technologies Final Exam",
            "time_limit_minutes" => isset($_GET["time_limit_minutes"]) ? (int) $_GET["time_limit_minutes"] : 60,
            "status" => $_GET["status"] ?? "draft",
            "question_count" => isset($_GET["question_count"]) ? (int) $_GET["question_count"] : 3,
            "total_marks" => isset($_GET["total_marks"]) ? (int) $_GET["total_marks"] : 7,
        );

        // Sample questions data (will be replaced with DB queries later)
        $questions = array(
            array(
                "id" => 1,
                "question_text" => "What does HTML stand for?",
                "marks" => 2,
                "correct_option" => "C",
                "options" => array(
                    "A" => "HyperText Markup Language",
                    "B" => "High Tech Modern Language",
                    "C" => "HyperText Markup Language",
                    "D" => "Hyperlinks and Text Markup Language",
                ),
            ),
            array(
                "id" => 2,
                "question_text" => "Which CSS property controls text size?",
                "marks" => 3,
                "correct_option" => "A",
                "options" => array(
                    "A" => "font-size",
                    "B" => "text-size",
                    "C" => "font-weight",
                    "D" => "line-height",
                ),
            ),
            array(
                "id" => 3,
                "question_text" => "Which HTTP method is idempotent AND safe?",
                "marks" => 2,
                "correct_option" => "B",
                "options" => array(
                    "A" => "POST",
                    "B" => "GET",
                    "C" => "PUT",
                    "D" => "DELETE",
                ),
            ),
        );

        return array(
            "quiz" => $quiz,
            "questions" => $questions,
            "page_title" => "Question Builder",
            "page_subtitle" => "Add, edit, and manage MCQ questions for your quiz",
            "breadcrumbs" => array("My Quizzes", $quiz["title"], "Question Builder"),
        );
    }
}