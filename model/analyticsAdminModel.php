<?php
require_once __DIR__ . "/../config/dbConnection.php";

function getAnalyticsAdminOverview()
{
    $connection = get_database_connection();

    $overview = [
        "total_instructors" => 0,
        "total_quizzes" => 0,
        "total_attempts" => 0,
        "average_percent" => 0,
    ];

    $queries = [
        "total_instructors" => "SELECT COUNT(*) AS value FROM users WHERE role = 'instructor'",
        "total_quizzes" => "SELECT COUNT(*) AS value FROM quizzes",
        "total_attempts" => "SELECT COUNT(*) AS value FROM attempts WHERE completed_at IS NOT NULL AND score IS NOT NULL",
        "average_percent" => "SELECT AVG((a.score / NULLIF(q.total_marks, 0)) * 100) AS value FROM attempts a JOIN quizzes q ON q.id = a.quiz_id WHERE a.completed_at IS NOT NULL AND a.score IS NOT NULL",
    ];

    foreach ($queries as $key => $sql) {
        $result = $connection->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        $overview[$key] = $row && $row["value"] !== null ? (float)$row["value"] : 0;
    }

    $connection->close();
    return $overview;
}

function getAnalyticsAdminInstructors()
{
    $connection = get_database_connection();

    $sql = "SELECT u.id, u.name, u.email, COUNT(DISTINCT q.id) AS quiz_count
            FROM users u
            LEFT JOIN quizzes q ON q.instructor_id = u.id
            WHERE u.role = 'instructor'
            GROUP BY u.id, u.name, u.email
            ORDER BY u.name ASC";

    $result = $connection->query($sql);
    $instructors = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $connection->close();
    return $instructors;
}

function getAnalyticsAdminQuizzes($instructor_id = null, $search = null)
{
    $connection = get_database_connection();

    $sql = "SELECT q.id, q.title, q.description, q.total_marks, q.status, q.created_at,
                   u.id AS instructor_id, u.name AS instructor_name, u.email AS instructor_email,
                   COUNT(DISTINCT qu.id) AS question_count,
                   COUNT(DISTINCT a.id) AS attempt_count,
                   ROUND(AVG((a.score / NULLIF(q.total_marks, 0)) * 100), 1) AS average_percent,
                   COUNT(DISTINCT CASE WHEN q.total_marks > 0 AND (a.score / q.total_marks) * 100 >= 60 THEN a.id END) AS pass_count
            FROM quizzes q
            JOIN users u ON u.id = q.instructor_id
            LEFT JOIN questions qu ON qu.quiz_id = q.id
            LEFT JOIN attempts a ON a.quiz_id = q.id
                AND a.completed_at IS NOT NULL
                AND a.score IS NOT NULL
            WHERE 1 = 1";

    $params = [];
    $types = "";

    if ($instructor_id !== null && (int)$instructor_id > 0) {
        $sql .= " AND q.instructor_id = ?";
        $params[] = (int)$instructor_id;
        $types .= "i";
    }

    if ($search !== null && $search !== "") {
        $sql .= " AND (q.title LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
        $like = "%" . $search . "%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= "sss";
    }

    $sql .= " GROUP BY q.id, q.title, q.description, q.total_marks, q.status, q.created_at, u.id, u.name, u.email
              ORDER BY u.name ASC, q.created_at DESC";

    $statement = $connection->prepare($sql);
    if (!$statement) {
        $connection->close();
        return [];
    }

    if ($types !== "") {
        $statement->bind_param($types, ...$params);
    }
    $statement->execute();

    $result = $statement->get_result();
    $quizzes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $statement->close();
    $connection->close();
    return $quizzes;
}

function getAnalyticsAdminQuizById(int $quiz_id)
{
    $connection = get_database_connection();

    $sql = "SELECT q.id, q.title, q.description, q.total_marks, q.status, q.created_at,
                   u.id AS instructor_id, u.name AS instructor_name, u.email AS instructor_email,
                   COUNT(DISTINCT qu.id) AS question_count,
                   COUNT(DISTINCT a.id) AS attempt_count,
                   ROUND(AVG((a.score / NULLIF(q.total_marks, 0)) * 100), 1) AS average_percent,
                   COUNT(DISTINCT CASE WHEN q.total_marks > 0 AND (a.score / q.total_marks) * 100 >= 60 THEN a.id END) AS pass_count
            FROM quizzes q
            JOIN users u ON u.id = q.instructor_id
            LEFT JOIN questions qu ON qu.quiz_id = q.id
            LEFT JOIN attempts a ON a.quiz_id = q.id
                AND a.completed_at IS NOT NULL
                AND a.score IS NOT NULL
            WHERE q.id = ?
            GROUP BY q.id, q.title, q.description, q.total_marks, q.status, q.created_at, u.id, u.name, u.email
            LIMIT 1";

    $statement = $connection->prepare($sql);
    if (!$statement) {
        $connection->close();
        return null;
    }

    $statement->bind_param("i", $quiz_id);
    $statement->execute();

    $result = $statement->get_result();
    $quiz = $result ? $result->fetch_assoc() : null;

    $statement->close();
    $connection->close();
    return $quiz ?: null;
}

function getAnalyticsAdminAttemptsByQuiz(int $quiz_id)
{
    $connection = get_database_connection();

    $sql = "SELECT a.id AS attempt_id, a.student_id, u.name AS student_name, u.email AS student_email,
                   a.score, q.total_marks,
                   TIMESTAMPDIFF(MINUTE, a.started_at, a.completed_at) AS duration,
                   a.started_at, a.completed_at
            FROM attempts a
            JOIN users u ON u.id = a.student_id
            JOIN quizzes q ON q.id = a.quiz_id
            WHERE a.quiz_id = ?
              AND a.completed_at IS NOT NULL
              AND a.score IS NOT NULL
            ORDER BY a.completed_at DESC";

    $statement = $connection->prepare($sql);
    if (!$statement) {
        $connection->close();
        return [];
    }

    $statement->bind_param("i", $quiz_id);
    $statement->execute();

    $result = $statement->get_result();
    $attempts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $statement->close();
    $connection->close();
    return $attempts;
}
