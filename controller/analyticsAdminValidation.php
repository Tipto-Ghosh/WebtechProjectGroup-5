<?php

function validateAnalyticsAdminAction($value)
{
    $allowed = ["load_quiz"];
    return in_array($value, $allowed, true) ? $value : "";
}

function validateAnalyticsAdminQuizId($value)
{
    if (!isset($value) || $value === "") {
        return 0;
    }

    $quiz_id = filter_var($value, FILTER_VALIDATE_INT);
    return $quiz_id !== false && $quiz_id > 0 ? (int)$quiz_id : 0;
}

function validateAnalyticsAdminInstructorId($value)
{
    if (!isset($value) || $value === "" || $value === "all") {
        return null;
    }

    $instructor_id = filter_var($value, FILTER_VALIDATE_INT);
    return $instructor_id !== false && $instructor_id > 0 ? (int)$instructor_id : null;
}

function validateAnalyticsAdminSearch($value)
{
    if (!isset($value) || trim((string)$value) === "") {
        return null;
    }

    $clean = strip_tags(trim((string)$value));
    return substr($clean, 0, 100);
}
