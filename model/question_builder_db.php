<?php

function getQuizQuestionSummary($connection, $quizId)
{
	$sql = "SELECT COUNT(*) AS question_count, COALESCE(SUM(marks), 0) AS total_marks FROM questions WHERE quiz_id = '" . (int) $quizId . "'";
	$result = $connection->query($sql);

	if ($result && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return array("question_count" => 0, "total_marks" => 0);
}

function canPublishQuiz($connection, $quizId)
{
	$summary = getQuizQuestionSummary($connection, $quizId);
	return (int) ($summary["question_count"] ?? 0) > 0;
}

function recomputeQuizTotalMarks($connection, $quizId)
{
	$summary = getQuizQuestionSummary($connection, $quizId);
	$totalMarks = (int) ($summary["total_marks"] ?? 0);

	$sql = "UPDATE quizzes SET total_marks = '" . $totalMarks . "' WHERE id = '" . (int) $quizId . "'";
	$connection->query($sql);

	return $summary;
}

function getQuestionById($connection, $questionId, $quizId = null)
{
	$questionId = (int) $questionId;
	$sql = "SELECT * FROM questions WHERE id = '" . $questionId . "'";

	if ($quizId !== null) {
		$sql .= " AND quiz_id = '" . (int) $quizId . "'";
	}

	$sql .= " LIMIT 1";
	$result = $connection->query($sql);

	if ($result && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return array();
}

function getQuestionsByQuiz($connection, $quizId)
{
	$sql = "SELECT questions.id AS question_id, questions.quiz_id, questions.question_text, questions.marks, questions.order_index, options.id AS option_id, options.option_text, options.is_correct FROM questions LEFT JOIN options ON options.question_id = questions.id WHERE questions.quiz_id = '" . (int) $quizId . "' ORDER BY questions.order_index ASC, options.id ASC";
	$result = $connection->query($sql);
	$questions = array();

	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$questionId = (int) $row["question_id"];

			if (!isset($questions[$questionId])) {
				$questions[$questionId] = array(
					"id" => $questionId,
					"quiz_id" => (int) $row["quiz_id"],
					"question_text" => $row["question_text"],
					"marks" => (int) $row["marks"],
					"order_index" => (int) $row["order_index"],
					"correct_option" => "",
					"options" => array(),
				);
			}

			if ($row["option_id"] !== null) {
				$optionIndex = count($questions[$questionId]["options"]); //get current index for the current question
				$optionKey = array("A", "B", "C", "D")[$optionIndex] ?? chr(65 + $optionIndex);
				$questions[$questionId]["options"][$optionKey] = $row["option_text"];

				if ((int) $row["is_correct"] === 1) {
					$questions[$questionId]["correct_option"] = $optionKey;
				}
			}
		}
	}

	return array_values($questions);
}

function replaceQuestionOptions($connection, $questionId, array $options, $correctOption)
{
	$questionId = (int) $questionId;
	$connection->query("DELETE FROM options WHERE question_id = '" . $questionId . "'");

	$labels = array("A", "B", "C", "D");
	foreach ($labels as $label) {
		$optionText = quizBuilderEscape($connection, $options[$label] ?? "");
		$isCorrect = $label === $correctOption ? 1 : 0;
		$sql = "INSERT INTO options (question_id, option_text, is_correct) VALUES ('" . $questionId . "', '" . $optionText . "', '" . $isCorrect . "')";
		$connection->query($sql);
	}

	return true;
}

function createQuestion($connection, $quizId, $questionText, $marks, $correctOption, array $options)
{
	$quizId = (int) $quizId;
	$questionText = quizBuilderEscape($connection, $questionText);
	$marks = max(1, (int) $marks);
	$correctOption = strtoupper(trim((string) $correctOption));

	$maxOrderResult = $connection->query("SELECT COALESCE(MAX(order_index), 0) AS max_order FROM questions WHERE quiz_id = '" . $quizId . "'");
	$orderIndex = 1;
	if ($maxOrderResult && ($maxOrderRow = $maxOrderResult->fetch_assoc())) {
		$orderIndex = ((int) $maxOrderRow["max_order"]) + 1;
	}

	$sql = "INSERT INTO questions (quiz_id, question_text, marks, order_index) VALUES ('" . $quizId . "', '" . $questionText . "', '" . $marks . "', '" . $orderIndex . "')";
	$result = $connection->query($sql);

	if (!$result) {
		return 0;
	}

	$questionId = (int) $connection->insert_id;
	replaceQuestionOptions($connection, $questionId, $options, $correctOption);
	recomputeQuizTotalMarks($connection, $quizId);

	return $questionId;
}

function updateQuestion($connection, $questionId, $quizId, $questionText, $marks, $correctOption, array $options)
{
	$questionId = (int) $questionId;
	$quizId = (int) $quizId;
	$questionText = quizBuilderEscape($connection, $questionText);
	$marks = max(1, (int) $marks);
	$correctOption = strtoupper(trim((string) $correctOption));

	$sql = "UPDATE questions SET question_text = '" . $questionText . "', marks = '" . $marks . "' WHERE id = '" . $questionId . "' AND quiz_id = '" . $quizId . "'";
	$result = $connection->query($sql);

	if (!$result) {
		return false;
	}

	replaceQuestionOptions($connection, $questionId, $options, $correctOption);
	recomputeQuizTotalMarks($connection, $quizId);

	return true;
}

function deleteQuestion($connection, $questionId, $quizId)
{
	$questionId = (int) $questionId;
	$quizId = (int) $quizId;
	$sql = "DELETE FROM questions WHERE id = '" . $questionId . "' AND quiz_id = '" . $quizId . "'";
	$result = $connection->query($sql);

	if ($result) {
		recomputeQuizTotalMarks($connection, $quizId);
	}

	return $result;
}

function toggleQuizStatus($connection, $quizId, $instructorId)
{
	$quiz = getQuizById($connection, $quizId, $instructorId);

	if (empty($quiz)) {
		return array("success" => false, "message" => "Quiz not found");
	}

	$newStatus = (isset($quiz["status"]) && $quiz["status"] === "published") ? "draft" : "published";

	if ($newStatus === "published" && !canPublishQuiz($connection, $quizId)) {
		return array("success" => false, "message" => "Add at least one question before publishing");
	}

	$sql = "UPDATE quizzes SET status = '" . quizBuilderEscape($connection, $newStatus) . "' WHERE id = '" . (int) $quizId . "' AND instructor_id = '" . (int) $instructorId . "'";
	$result = $connection->query($sql);

	if (!$result) {
		return array("success" => false, "message" => "Failed to update quiz status");
	}

	$updatedQuiz = getQuizById($connection, $quizId, $instructorId);
	return array("success" => true, "quiz" => $updatedQuiz, "message" => "Quiz status updated");
}