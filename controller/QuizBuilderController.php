<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . "/../model/quiz_builder_db.php";

class QuizBuilderController
{
    private function currentInstructorId(): int
    {
        return quizBuilderCurrentInstructorId();
    }

    private function defaultQuiz(array $quiz = array()): array
    {
        return array_merge(array(
            "id" => 0,
            "title" => "Web Technologies Final Exam",
            "description" => "Covers HTML5, CSS3, JavaScript fundamentals, PHP basics, and database concepts covered in the semester.",
            "total_marks" => 0,
            "time_limit_minutes" => 60,
            "status" => "draft",
            "question_count" => 0,
        ), $quiz);
    }

    private function resolveQuizId(): int
    {
        if (isset($_GET["quiz_id"])) {
            return (int) $_GET["quiz_id"];
        }

        if (isset($_SESSION["current_quiz_id"])) {
            return (int) $_SESSION["current_quiz_id"]; // return the quiz ID stored in the session
        }

        return 0;
    }

    private function requestData(): array
    {
        $data = array_merge($_GET, $_POST); // start with GET and POST data combined, so we can support both query parameters and form data for API requests
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET"; // determine the HTTP method of the request, defaulting to GET if not set

        if (in_array($method, array("PATCH", "DELETE"), true) || empty($_POST)) { // for PATCH and DELETE requests, or if POST data is empty (which can happen with some clients), we should also check the raw input stream for data, since these methods often send data in the request body rather than as form data
            $raw = file_get_contents("php://input");  // read the raw input stream, which can contain JSON or URL-encoded data for API requests that don't use traditional form submissions
            if (is_string($raw) && trim($raw) !== "") {
                parse_str($raw, $parsed);
                if (is_array($parsed)) {
                    $data = array_merge($data, $parsed);
                }
            }
        }

        return $data; // return the combined data from GET, POST, and raw input as an associative array for easier handling in API methods
    }

    private function quizFromDatabase(int $quizId): array
    {
        if ($quizId <= 0) {
            return array();
        }

        $connection = getQuizBuilderConnection();
        $quiz = getQuizById($connection, $quizId, $this->currentInstructorId());
        return is_array($quiz) ? $quiz : array();
    }

    public function processQuizFormSubmission(): array
    {
        if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
            return array("success" => false, "message" => "", "redirect" => ""); // if this method is called without POST data, just return an empty response
        }

        $quizIdFromPost = (int) ($_POST["quiz_id"] ?? 0); // if quiz_id is present in POST data, it indicates an update operation, otherwise it's a create operation. 
        $modeFromPost = strtolower(trim((string) ($_POST["mode"] ?? ""))); // mode can be "edit" or "create", but if it's not provided, we can infer it from the presence of quiz_id

        if (!isset($_POST["action"]) || trim((string) $_POST["action"]) === "") { // if action is not explicitly provided in POST data, we can determine it based on quiz_id and mode
            $_POST["action"] = ($quizIdFromPost > 0 || $modeFromPost === "edit") ? "update_quiz" : "create_quiz"; // if quiz_id is present or mode is edit, we treat it as an update operation, otherwise it's a create operation
        }

        $response = $this->handleApiRequest(); // we can reuse the existing API handling logic to process the form submission, since the form data structure aligns with the expected API request format for create and update operations

        if (!empty($response["success"])) {
            $savedQuizId = (int) ($response["quiz"]["id"] ?? 0);
            if ($savedQuizId > 0) {
                return array(
                    "success" => true,
                    "message" => (string) ($response["message"] ?? "Quiz saved"),
                    "redirect" => "question_builder.php?quiz_id=" . $savedQuizId,
                );
            }
        }

        return array(
            "success" => false,
            "message" => (string) ($response["message"] ?? "Failed to save quiz"),
            "redirect" => "",
        );
    }

    public function getQuizFormData(): array
    {
        $mode = isset($_GET["mode"]) && $_GET["mode"] === "edit" ? "edit" : "create";
        $quizId = $this->resolveQuizId();
        $quiz = $this->quizFromDatabase($quizId);

        if (empty($quiz)) {
            $quiz = $this->defaultQuiz(array(
                "id" => $quizId,
                "title" => $_GET["title"] ?? "",
                "description" => $_GET["description"] ?? "",
                "time_limit_minutes" => isset($_GET["time_limit_minutes"]) ? (int) $_GET["time_limit_minutes"] : 60,
                "status" => $_GET["status"] ?? "draft",
            ));
        } else {
            $mode = "edit";
            $_SESSION["current_quiz_id"] = (int) $quiz["id"];
        }

        if (($quiz["title"] ?? "") === "") {
            $quiz["title"] = "Web Technologies Final Exam";
        }

        if (($quiz["description"] ?? "") === "") {
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
        $connection = getQuizBuilderConnection();
        $quizzes = getQuizzesByInstructor($connection, $this->currentInstructorId());
        $publishedCount = 0;
        $draftCount = 0;

        foreach ($quizzes as $quiz) {
            if (($quiz["status"] ?? "draft") === "published") {
                $publishedCount++;
            } else {
                $draftCount++;
            }
        }

        $stats = array(
            "total_quizzes" => count($quizzes),
            "published_quizzes" => $publishedCount,
            "draft_quizzes" => $draftCount,
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
        $connection = getQuizBuilderConnection();
        $quizId = $this->resolveQuizId();
        $quiz = array();

        if ($quizId > 0) {
            $quiz = getQuizById($connection, $quizId, $this->currentInstructorId());
        }

        if (empty($quiz)) {
            $latestQuiz = getLatestQuizForInstructor($connection, $this->currentInstructorId());
            if (!empty($latestQuiz)) {
                $quiz = $latestQuiz;
            }
        }

        if (empty($quiz)) {
            $quiz = $this->defaultQuiz();
        } else {
            $_SESSION["current_quiz_id"] = (int) $quiz["id"];
        }

        $questions = array();
        if ((int) ($quiz["id"] ?? 0) > 0) {
            $questions = getQuestionsByQuiz($connection, (int) $quiz["id"]);
        }

        $quiz["question_count"] = (int) ($quiz["question_count"] ?? count($questions));
        $quiz["total_marks"] = (int) ($quiz["total_marks"] ?? 0);

        return array(
            "quiz" => $quiz,
            "questions" => $questions,
            "page_title" => "Question Builder",
            "page_subtitle" => "Add, edit, and manage MCQ questions for your quiz",
            "breadcrumbs" => array("My Quizzes", $quiz["title"], "Question Builder"),
            "api_endpoint" => "../../controller/QuizBuilderController.php",
            "publish_button_label" => (($quiz["status"] ?? "draft") === "published") ? "Unpublish Quiz" : "Publish Quiz",
            "can_publish" => (int) ($quiz["question_count"] ?? 0) > 0,
        );
    }

    private function quizResponse(int $quizId): array
    {
        $connection = getQuizBuilderConnection();
        $quiz = getQuizById($connection, $quizId, $this->currentInstructorId());
        $questions = array();

        if (!empty($quiz)) {
            $questions = getQuestionsByQuiz($connection, (int) $quiz["id"]);
            $quiz["question_count"] = count($questions);
            $_SESSION["current_quiz_id"] = (int) $quiz["id"];
        }

        return array(
            "quiz" => $quiz,
            "questions" => $questions,
        );
    }

    private function actionCreateQuiz(array $data): array
    {
        $title = trim((string) ($data["title"] ?? ""));
        $description = trim((string) ($data["description"] ?? ""));
        $timeLimit = (int) ($data["time_limit_minutes"] ?? 0);

        if ($title === "") {
            return array("success" => false, "message" => "Quiz title is required");
        }

        if ($timeLimit <= 0) {
            return array("success" => false, "message" => "Time limit must be a positive integer");
        }

        $connection = getQuizBuilderConnection();
        $quizId = createQuiz($connection, $this->currentInstructorId(), $title, $description, $timeLimit, "draft");

        if ($quizId <= 0) {
            return array("success" => false, "message" => "Failed to create quiz");
        }

        $quiz = getQuizById($connection, $quizId, $this->currentInstructorId());
        $_SESSION["current_quiz_id"] = $quizId;

        return array("success" => true, "message" => "Quiz created", "quiz" => $quiz);
    }

    private function actionUpdateQuiz(array $data): array
    {
        $quizId = (int) ($data["quiz_id"] ?? 0);
        $title = trim((string) ($data["title"] ?? ""));
        $description = trim((string) ($data["description"] ?? ""));
        $timeLimit = (int) ($data["time_limit_minutes"] ?? 0);

        if ($quizId <= 0) {
            return array("success" => false, "message" => "Quiz id is required");
        }

        if ($title === "") {
            return array("success" => false, "message" => "Quiz title is required");
        }

        if ($timeLimit <= 0) {
            return array("success" => false, "message" => "Time limit must be a positive integer");
        }

        $connection = getQuizBuilderConnection();
        $result = updateQuiz($connection, $quizId, $this->currentInstructorId(), $title, $description, $timeLimit);

        if (!$result) {
            return array("success" => false, "message" => "Failed to update quiz");
        }

        return array("success" => true, "message" => "Quiz updated", "quiz" => getQuizById($connection, $quizId, $this->currentInstructorId()));
    }

    private function actionCreateQuestion(array $data): array
    {
        $quizId = (int) ($data["quiz_id"] ?? 0);
        $questionText = trim((string) ($data["question_text"] ?? ""));
        $marks = (int) ($data["marks"] ?? 0);
        $correctOption = strtoupper(trim((string) ($data["correct_option"] ?? "")));
        $options = array(
            "A" => trim((string) ($data["option_a"] ?? "")),
            "B" => trim((string) ($data["option_b"] ?? "")),
            "C" => trim((string) ($data["option_c"] ?? "")),
            "D" => trim((string) ($data["option_d"] ?? "")),
        );

        if ($quizId <= 0) {
            return array("success" => false, "message" => "Quiz id is required");
        }

        if ($questionText === "") {
            return array("success" => false, "message" => "Question text is required");
        }

        if ($marks <= 0) {
            return array("success" => false, "message" => "Marks must be a positive integer");
        }

        if (!in_array($correctOption, array("A", "B", "C", "D"), true)) {
            return array("success" => false, "message" => "Correct answer is required");
        }

        foreach ($options as $label => $optionText) {
            if ($optionText === "") {
                return array("success" => false, "message" => "All four options are required");
            }
        }

        $connection = getQuizBuilderConnection();
        $questionId = createQuestion($connection, $quizId, $questionText, $marks, $correctOption, $options);

        if ($questionId <= 0) {
            return array("success" => false, "message" => "Failed to add question");
        }

        $question = array(
            "id" => $questionId,
            "question_text" => $questionText,
            "marks" => $marks,
            "correct_option" => $correctOption,
            "options" => $options,
        );

        return array("success" => true, "message" => "Question added", "question" => $question, "quiz" => getQuizById($connection, $quizId, $this->currentInstructorId()));
    }

    private function actionUpdateQuestion(array $data): array
    {
        $quizId = (int) ($data["quiz_id"] ?? 0);
        $questionId = (int) ($data["question_id"] ?? 0);
        $questionText = trim((string) ($data["question_text"] ?? ""));
        $marks = (int) ($data["marks"] ?? 0);
        $correctOption = strtoupper(trim((string) ($data["correct_option"] ?? "")));
        $options = array(
            "A" => trim((string) ($data["option_a"] ?? "")),
            "B" => trim((string) ($data["option_b"] ?? "")),
            "C" => trim((string) ($data["option_c"] ?? "")),
            "D" => trim((string) ($data["option_d"] ?? "")),
        );

        if ($quizId <= 0 || $questionId <= 0) {
            return array("success" => false, "message" => "Question id and quiz id are required");
        }

        if ($questionText === "") {
            return array("success" => false, "message" => "Question text is required");
        }

        if ($marks <= 0) {
            return array("success" => false, "message" => "Marks must be a positive integer");
        }

        if (!in_array($correctOption, array("A", "B", "C", "D"), true)) {
            return array("success" => false, "message" => "Correct answer is required");
        }

        foreach ($options as $label => $optionText) {
            if ($optionText === "") {
                return array("success" => false, "message" => "All four options are required");
            }
        }

        $connection = getQuizBuilderConnection();
        $result = updateQuestion($connection, $questionId, $quizId, $questionText, $marks, $correctOption, $options);

        if (!$result) {
            return array("success" => false, "message" => "Failed to update question");
        }

        $question = array(
            "id" => $questionId,
            "question_text" => $questionText,
            "marks" => $marks,
            "correct_option" => $correctOption,
            "options" => $options,
        );

        return array("success" => true, "message" => "Question updated", "question" => $question, "quiz" => getQuizById($connection, $quizId, $this->currentInstructorId()));
    }

    private function actionDeleteQuestion(array $data): array
    {
        $quizId = (int) ($data["quiz_id"] ?? 0);
        $questionId = (int) ($data["question_id"] ?? 0);

        if ($quizId <= 0 || $questionId <= 0) {
            return array("success" => false, "message" => "Question id and quiz id are required");
        }

        $connection = getQuizBuilderConnection();
        $result = deleteQuestion($connection, $questionId, $quizId);

        if (!$result) {
            return array("success" => false, "message" => "Failed to delete question");
        }

        return array("success" => true, "message" => "Question deleted", "question_id" => $questionId, "quiz" => getQuizById($connection, $quizId, $this->currentInstructorId()));
    }

    private function actionToggleQuiz(array $data): array
    {
        $quizId = (int) ($data["quiz_id"] ?? 0);

        if ($quizId <= 0) {
            return array("success" => false, "message" => "Quiz id is required");
        }

        $connection = getQuizBuilderConnection();
        $response = toggleQuizStatus($connection, $quizId, $this->currentInstructorId());

        if (empty($response["success"])) {
            return $response;
        }

        return array("success" => true, "message" => $response["message"] ?? "Quiz status updated", "quiz" => $response["quiz"] ?? array());
    }

    public function handleApiRequest(): array
    {
        $data = $this->requestData();// get the combined request data from GET, POST, and raw input
        $action = strtolower(trim((string) ($data["action"] ?? ""))); // check the action data which is coming from the form the previous file.

        if ($action === "") {
            return array("success" => false, "message" => "Action is required");
        }

        if ($action === "create_quiz") {
            return $this->actionCreateQuiz($data);
        }

        if ($action === "update_quiz") {
            return $this->actionUpdateQuiz($data);
        }

        if ($action === "create_question") {
            return $this->actionCreateQuestion($data);
        }

        if ($action === "update_question") {
            return $this->actionUpdateQuestion($data);
        }

        if ($action === "delete_question") {
            return $this->actionDeleteQuestion($data);
        }

        if ($action === "toggle_quiz") {
            return $this->actionToggleQuiz($data);
        }

        return array("success" => false, "message" => "Unsupported action");
    }
}

if (realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"] ?? "")) {
    header("Content-Type: application/json");

    $controller = new QuizBuilderController();
    $response = $controller->handleApiRequest();

    echo json_encode($response);
    exit;
}