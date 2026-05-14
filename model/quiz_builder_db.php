<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/dbConnection.php";

function getQuizBuilderConnection()
{
	return get_database_connection();
}

function quizBuilderCurrentInstructorId()
{
	return isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : 3;
}

function quizBuilderEscape($connection, $value)
{
	return $connection->real_escape_string((string) $value);
}

function getQuizById($connection, $quizId, $instructorId = null)
{
	$quizId = (int) $quizId;
	$sql = "SELECT quizzes.*, (SELECT COUNT(*) FROM questions WHERE quiz_id = quizzes.id) AS question_count FROM quizzes WHERE quizzes.id = '" . $quizId . "'";

	if ($instructorId !== null) {
		$sql .= " AND quizzes.instructor_id = '" . (int) $instructorId . "'";
	}

	$sql .= " LIMIT 1";
	$result = $connection->query($sql);

	if ($result && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return array();
}

function getLatestQuizForInstructor($connection, $instructorId)
{
	$sql = "SELECT quizzes.*, (SELECT COUNT(*) FROM questions WHERE quiz_id = quizzes.id) AS question_count FROM quizzes WHERE instructor_id = '" . (int) $instructorId . "' ORDER BY created_at DESC LIMIT 1";
	$result = $connection->query($sql);

	if ($result && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return array();
}

function getQuizzesByInstructor($connection, $instructorId)
{
	$sql = "SELECT quizzes.*, (SELECT COUNT(*) FROM questions WHERE quiz_id = quizzes.id) AS question_count FROM quizzes WHERE instructor_id = '" . (int) $instructorId . "' ORDER BY created_at DESC";
	$result = $connection->query($sql);
	$quizzes = array();

	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$quizzes[] = $row;
		}
	}

	return $quizzes;
}

function createQuiz($connection, $instructorId, $title, $description, $timeLimitMinutes, $status = "draft")
{
	$title = quizBuilderEscape($connection, $title);
	$description = quizBuilderEscape($connection, $description);
	$status = quizBuilderEscape($connection, $status);
	$timeLimitMinutes = max(1, (int) $timeLimitMinutes);

	$sql = "INSERT INTO quizzes (instructor_id, title, description, total_marks, time_limit_minutes, status) VALUES ('" . (int) $instructorId . "', '" . $title . "', '" . $description . "', '0', '" . $timeLimitMinutes . "', '" . $status . "')";
	$result = $connection->query($sql);

	if ($result) {
		return (int) $connection->insert_id;
	}
	unset($_SESSION["current_quiz_id"]);

	return 0;
}

function updateQuiz($connection, $quizId, $instructorId, $title, $description, $timeLimitMinutes)
{
	$quizId = (int) $quizId;
	$title = quizBuilderEscape($connection, $title);
	$description = quizBuilderEscape($connection, $description);
	$timeLimitMinutes = max(1, (int) $timeLimitMinutes);

	$sql = "UPDATE quizzes SET title = '" . $title . "', description = '" . $description . "', time_limit_minutes = '" . $timeLimitMinutes . "' WHERE id = '" . $quizId . "' AND instructor_id = '" . (int) $instructorId . "'";
	unset($_SESSION["current_quiz_id"]);
	return $connection->query($sql);
}

function deleteQuiz($connection, $quizId, $instructorId)
{
	$sql = "DELETE FROM quizzes WHERE id = '" . (int) $quizId . "' AND instructor_id = '" . (int) $instructorId . "'";
	$result = $connection->query($sql);

	if (!$result) {
		return false;
	}

	return $connection->affected_rows > 0;
}

require_once __DIR__ . "/question_builder_db.php";
?>
