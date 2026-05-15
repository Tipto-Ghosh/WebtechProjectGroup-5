<?php
/**
 * adminManageUser.php
 * ────────────────────────────────────────────────
 * Admin panel — User Management page.
 *
 * Also handles the AJAX toggle endpoint when:
 *   POST /api/users/toggle  (X-Requested-With: XMLHttpRequest)
 * The toggle request is intercepted at the very top,
 * processed, and a JSON response is sent — the rest of the
 * page never renders for AJAX calls.
 *
 * File dependencies (include before using):
 *   config/db.php                  → provides $pdo (PDO instance)
 *   adminManageUserModel.php       → DB query functions
 *   adminManageUserValidation.php  → validation helpers
 */

session_start();

// ── Auth guard: admins only ──────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';
require_once 'adminManageUserModel.php';
require_once 'adminManageUserValidation.php';

$admin_id   = (int) $_SESSION['user_id'];
$admin_name = $_SESSION['name'] ?? 'Admin';

/* ══════════════════════════════════════════════════════════
   AJAX TOGGLE ENDPOINT  —  POST /api/users/toggle
   Intercept before any HTML output.
══════════════════════════════════════════════════════════ */
$is_ajax = (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

if ($is_ajax) {
    header('Content-Type: application/json');

    // 1. Validate the POST payload
    $v = validateToggleRequest($_POST);
    if (!$v['valid']) {
        echo json_encode(['success' => false, 'message' => $v['error']]);
        exit;
    }

    $target_id = (int) $_POST['user_id'];

    // 2. Fetch the target user
    $target = getUserById($pdo, $target_id);
    if (!$target) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // 3. Validate the target (not admin, not self)
    $vt = validateToggleTarget($target, $admin_id);
    if (!$vt['valid']) {
        echo json_encode(['success' => false, 'message' => $vt['error']]);
        exit;
    }

    // 4. Perform the toggle
    $new_state = toggleUserActive($pdo, $target_id);
    if ($new_state === false) {
        echo json_encode(['success' => false, 'message' => 'Database update failed. Please try again.']);
        exit;
    }

    echo json_encode([
        'success'   => true,
        'new_state' => $new_state,
        'user_name' => $target['name'],
        'message'   => 'Status updated successfully.'
    ]);
    exit;
}

/* ══════════════════════════════════════════════════════════
   PAGE LOAD  —  fetch users with optional filter
══════════════════════════════════════════════════════════ */
$role_filter = validateRoleFilter($_GET['role'] ?? null);
$search_q    = validateSearchQuery($_GET['search'] ?? null);

$users = getAllManagedUsers($pdo, $role_filter, $search_q);

/* ── Helpers ── */
function initials(string $name): string {
    $parts = explode(' ', trim($name));
    $init  = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) $init .= strtoupper(substr(end($parts), 0, 1));
    return $init;
}

function formatDate(string $datetime): string {
    return date('M j, Y', strtotime($datetime));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Admin Panel</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="adminManageUser.css">
</head>
<body>

<div class="layout">

    <!-- ══════════════════════════════
         SIDEBAR
    ══════════════════════════════ -->
    <aside class="sidebar">

        <div class="sidebar-brand">
            <a href="#" class="brand-logo">
                <div class="brand-icon">📖</div>
                <span class="brand-name">Quiz<span>Vault</span></span>
            </a>
        </div>

        <div class="sidebar-user">
            <div class="user-info">
                <div class="user-avatar"><?= htmlspecialchars(initials($admin_name)) ?></div>
                <div>
                    <div class="user-name"><?= htmlspecialchars($admin_name) ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-section-label">Overview</div>
            <a href="admin_dashboard.php" class="nav-item">
                <span class="nav-icon">🏠</span> Dashboard
            </a>

            <div class="nav-section-label">Platform</div>
            <a href="adminManageUser.php" class="nav-item active">
                <span class="nav-icon">👥</span> Manage Users
            </a>
            <a href="admin_quizzes.php" class="nav-item">
                <span class="nav-icon">📝</span> All Quizzes
            </a>
            <a href="leaderboard.php" class="nav-item">
                <span class="nav-icon">🏆</span> Leaderboard
            </a>

            <div class="nav-section-label">Account</div>
            <a href="profile.php" class="nav-item">
                <span class="nav-icon">👤</span> Profile
            </a>

        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="nav-item">
                <span class="nav-icon">🚪</span> Log Out
            </a>
        </div>

    </aside>

    <!-- ══════════════════════════════
         MAIN CONTENT
    ══════════════════════════════ -->
    <main class="main">

        <!-- Top bar -->
        <header class="topbar">
            <div class="topbar-left">
                <h1>User Management</h1>
                <p>Control access and account status for all platform members</p>
            </div>
            <div>
                <span class="topbar-badge">Admin Panel</span>
            </div>
        </header>

        <div class="content">

            <div class="divider-label"><span>Platform Members</span></div>

            <!-- ── Toolbar: search + role filter ── -->
            <div class="toolbar">

                <!-- Search -->
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input
                        type="text"
                        id="userSearch"
                        class="search-input"
                        placeholder="Search by name…"
                        autocomplete="off"
                        value="<?= htmlspecialchars($search_q ?? '') ?>"
                    >
                </div>

                <!-- Role filter tabs -->
                <div class="filter-tabs">
                    <button class="filter-tab <?= ($role_filter === null)         ? 'active' : '' ?>" data-role="all">
                        All
                    </button>
                    <button class="filter-tab <?= ($role_filter === 'student')    ? 'active' : '' ?>" data-role="student">
                        Students
                    </button>
                    <button class="filter-tab <?= ($role_filter === 'instructor') ? 'active' : '' ?>" data-role="instructor">
                        Instructors
                    </button>
                </div>

                <!-- Live count -->
                <div class="user-count" id="userCount">
                    Showing <span><?= count($users) ?></span> user<?= count($users) !== 1 ? 's' : '' ?>
                </div>

            </div>
            <!-- /toolbar -->

            <!-- ── Users table ── -->
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">

                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <?php
                                    $is_active  = (int) $user['is_active'];
                                    $role       = $user['role'];
                                    $initials   = initials($user['name']);
                                ?>
                            <tr
                                data-user-id="<?= $user['id'] ?>"
                                data-name="<?= strtolower(htmlspecialchars($user['name'])) ?>"
                                data-role="<?= htmlspecialchars($role) ?>"
                                data-active="<?= $is_active ?>"
                            >
                                <!-- ID -->
                                <td class="td-id">#<?= $user['id'] ?></td>

                                <!-- Name + email -->
                                <td>
                                    <div class="td-name-wrap">
                                        <div class="row-avatar <?= htmlspecialchars($role) ?>">
                                            <?= htmlspecialchars($initials) ?>
                                        </div>
                                        <div>
                                            <div class="td-name"><?= htmlspecialchars($user['name']) ?></div>
                                            <div class="td-email"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role badge -->
                                <td>
                                    <span class="badge-role <?= htmlspecialchars($role) ?>">
                                        <?= $role === 'instructor' ? '🎓' : '🎒' ?>
                                        <?= ucfirst($role) ?>
                                    </span>
                                </td>

                                <!-- Joined date -->
                                <td class="td-date"><?= formatDate($user['created_at']) ?></td>

                                <!-- Status indicator -->
                                <td>
                                    <span class="status-indicator <?= $is_active ? 'active-status' : 'suspended-status' ?>">
                                        <span class="status-dot"></span>
                                        <?= $is_active ? 'Active' : 'Suspended' ?>
                                    </span>
                                </td>

                                <!-- Toggle button -->
                                <td>
                                    <button
                                        class="btn-toggle <?= $is_active ? 'can-suspend' : 'can-activate' ?>"
                                        data-user-id="<?= $user['id'] ?>"
                                        data-current-state="<?= $is_active ?>"
                                    >
                                        <?= $is_active ? '🔒 Suspend' : '✅ Activate' ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </tbody>
                </table>

                <!-- Empty state (shown by JS when no rows match, or here if DB is empty) -->
                <?php if (empty($users)): ?>
                <div id="emptyState" class="empty-state">
                    <div class="empty-icon">👤</div>
                    <p>No users found matching your current filters.</p>
                </div>
                <?php else: ?>
                <div id="emptyState" class="empty-state" style="display:none;">
                    <div class="empty-icon">🔍</div>
                    <p>No users match your search.</p>
                </div>
                <?php endif; ?>

            </div>
            <!-- /table-card -->

        </div>
        <!-- /content -->

    </main>

</div>
<!-- /layout -->

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<!-- AJAX + interaction script -->
<script src="adminManageUserAjax.js"></script>

</body>
</html>