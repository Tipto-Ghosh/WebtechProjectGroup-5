<?php
/**
 * adminManageUserValidation.php
 * ─────────────────────────────────────────────
 * Server-side validation for the Admin User Management page.
 * All functions return an array: ['valid' => bool, 'error' => string|null]
 */

// ── Validate toggle AJAX request ──────────────────────────────────────────────
/**
 * Validates the incoming POST payload for /api/users/toggle.
 *
 * Expected POST fields:
 *   user_id  — integer, > 0
 *
 * @param  array $post  Typically $_POST
 * @return array        ['valid' => bool, 'error' => string|null]
 */
function validateToggleRequest(array $post): array
{
    // Must have user_id
    if (!isset($post['user_id']) || $post['user_id'] === '') {
        return ['valid' => false, 'error' => 'Missing required field: user_id.'];
    }

    // Must be numeric
    if (!is_numeric($post['user_id'])) {
        return ['valid' => false, 'error' => 'user_id must be a numeric value.'];
    }

    $id = (int) $post['user_id'];

    // Must be a positive integer
    if ($id <= 0) {
        return ['valid' => false, 'error' => 'user_id must be a positive integer.'];
    }

    return ['valid' => true, 'error' => null];
}

// ── Validate that the target user is not an admin ─────────────────────────────
/**
 * Prevents toggling admin accounts.
 * Call this AFTER fetching the user row from the DB.
 *
 * @param  array  $user   Row returned by getUserById()
 * @param  int    $admin_session_id   $_SESSION['user_id'] of the acting admin
 * @return array  ['valid' => bool, 'error' => string|null]
 */
function validateToggleTarget(array $user, int $admin_session_id): array
{
    // Cannot toggle another admin
    if ($user['role'] === 'admin') {
        return ['valid' => false, 'error' => 'Admin accounts cannot be suspended via this panel.'];
    }

    // Cannot toggle yourself
    if ((int) $user['id'] === $admin_session_id) {
        return ['valid' => false, 'error' => 'You cannot change the status of your own account.'];
    }

    return ['valid' => true, 'error' => null];
}

// ── Validate role filter query param ─────────────────────────────────────────
/**
 * Validates the optional ?role= query param on page load.
 *
 * @param  mixed $value  Typically $_GET['role']
 * @return string|null   Sanitised role string, or null if absent/invalid
 */
function validateRoleFilter(mixed $value): ?string
{
    $allowed = ['student', 'instructor'];
    if (isset($value) && in_array($value, $allowed, true)) {
        return $value;
    }
    return null;
}

// ── Validate & sanitise the search query param ───────────────────────────────
/**
 * Returns a safe search string or null.
 * Strips HTML tags and limits length to 100 chars.
 *
 * @param  mixed $value  Typically $_GET['search']
 * @return string|null
 */
function validateSearchQuery(mixed $value): ?string
{
    if (!isset($value) || trim($value) === '') {
        return null;
    }
    $clean = strip_tags(trim((string) $value));
    return mb_substr($clean, 0, 100);
}