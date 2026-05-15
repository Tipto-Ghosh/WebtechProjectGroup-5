<?php
/**
 * adminManageUserModel.php
 * ─────────────────────────────────────────────
 * Data-access layer for Admin User Management.
 * All queries use PDO prepared statements.
 * Requires a $pdo instance — include db.php before this file.
 */

// ── Fetch all students and instructors ────────────────────────────────────────
/**
 * Returns every user whose role is 'student' or 'instructor'.
 * Supports optional role filter and name search.
 *
 * @param  PDO         $pdo
 * @param  string|null $role_filter  'student' | 'instructor' | null (both)
 * @param  string|null $search       Partial name match (LIKE %search%)
 * @return array
 */
function getAllManagedUsers(PDO $pdo, ?string $role_filter = null, ?string $search = null): array
{
    $conditions = ["role IN ('student', 'instructor')"];
    $params     = [];

    if ($role_filter !== null && in_array($role_filter, ['student', 'instructor'], true)) {
        $conditions[] = "role = :role";
        $params[':role'] = $role_filter;
    }

    if ($search !== null && $search !== '') {
        $conditions[] = "name LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    $where = implode(' AND ', $conditions);
    $sql   = "
        SELECT id, name, email, role, is_active, created_at
        FROM   users
        WHERE  {$where}
        ORDER  BY created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ── Fetch a single user by ID (admin/student/instructor) ──────────────────────
/**
 * Returns one user row or false if not found.
 *
 * @param  PDO $pdo
 * @param  int $id
 * @return array|false
 */
function getUserById(PDO $pdo, int $id): array|false
{
    $stmt = $pdo->prepare("
        SELECT id, name, email, role, is_active, created_at
        FROM   users
        WHERE  id = :id
        LIMIT  1
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ── Toggle is_active for a user ───────────────────────────────────────────────
/**
 * Flips the is_active boolean for the given user ID.
 * Returns the NEW is_active value (0 or 1), or false on failure.
 *
 * @param  PDO $pdo
 * @param  int $id
 * @return int|false
 */
function toggleUserActive(PDO $pdo, int $id): int|false
{
    // Fetch current state first
    $user = getUserById($pdo, $id);
    if ($user === false) {
        return false;
    }

    $new_state = $user['is_active'] ? 0 : 1;

    $stmt = $pdo->prepare("
        UPDATE users
        SET    is_active = :state
        WHERE  id        = :id
    ");
    $success = $stmt->execute([':state' => $new_state, ':id' => $id]);

    return $success ? $new_state : false;
}