<?php
include_once __DIR__ . '/../../controller/adminManageUserController.php';

function initials($name) {
    $parts = explode(' ', trim($name));
    $init = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $init .= strtoupper(substr(end($parts), 0, 1));
    }
    return $init;
}
function formatDate($datetime) {
    return date('M j, Y', strtotime($datetime));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users — Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/adminDashboard.css">
    <link rel="stylesheet" href="../style/adminManageUser.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar__logo">
            <span class="sidebar__logo-icon">&#9670;</span>
            <span class="sidebar__logo-text">QuizForge</span>
        </div>
        <nav class="sidebar__nav">
            <p class="sidebar__nav-label">Main</p>
            <a href="dashboard.php" class="sidebar__nav-item">Dashboard</a>
            <a href="adminManageUser.php" class="sidebar__nav-item sidebar__nav-item--active">User Management</a>
            <a href="#" class="sidebar__nav-item">Quizzes</a>
            <a href="../admin/analyticsAdmin.php" class="sidebar__nav-item">Analytics</a>
        </nav>
        <div class="sidebar__profile">
            <div class="sidebar__avatar"><?= htmlspecialchars(substr($admin_name, 0, 1)) ?></div>
            <div class="sidebar__profile-info">
                <p class="sidebar__profile-name"><?= htmlspecialchars($admin_name) ?></p>
                <p class="sidebar__profile-role">Admin</p>
            </div>
            <a href="../auth/login.php" class="sidebar__logout" title="Logout">&#10148;</a>
        </div>
    </aside>

    <main class="main" id="main-content">
        <header class="topbar">
            <button class="topbar__hamburger" id="btn-hamburger" aria-label="Toggle sidebar">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar__heading">
                <h1 class="topbar__title">User Management</h1>
                <p class="topbar__subtitle">Control access and account status for all platform members</p>
            </div>
        </header>

        <div class="content">
            <div class="toolbar">
                <div class="search-wrap">
                    <span class="search-icon">&#128269;</span>
                    <input type="text" id="userSearch" class="search-input" placeholder="Search by name…" autocomplete="off" value="<?= htmlspecialchars($search_q ?? '') ?>">
                </div>
                <div class="filter-tabs">
                    <button class="filter-tab <?= ($role_filter === null) ? 'active' : '' ?>" data-role="all">All</button>
                    <button class="filter-tab <?= ($role_filter === 'student')    ? 'active' : '' ?>" data-role="student">Students</button>
                    <button class="filter-tab <?= ($role_filter === 'instructor') ? 'active' : '' ?>" data-role="instructor">Instructors</button>
                    <button class="filter-tab <?= ($role_filter === 'admin')      ? 'active' : '' ?>" data-role="admin">Admins</button>
                </div>
                <div class="user-count" id="userCount">
                    Showing <span><?= count($users) ?></span> user<?= count($users) !== 1 ? 's' : '' ?>
                </div>
            </div>

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
                                $is_active = (int)$user['is_active'];
                                $role = $user['role'];
                                $initials = initials($user['name']);
                                ?>
                                <tr data-user-id="<?= $user['id'] ?>" data-name="<?= strtolower(htmlspecialchars($user['name'])) ?>" data-role="<?= htmlspecialchars($role) ?>" data-active="<?= $is_active ?>">
                                    <td class="td-id">#<?= $user['id'] ?></td>
                                    <td>
                                        <div class="td-name-wrap">
                                            <div class="row-avatar <?= htmlspecialchars($role) ?>"><?= htmlspecialchars($initials) ?></div>
                                            <div>
                                                <div class="td-name"><?= htmlspecialchars($user['name']) ?></div>
                                                <div class="td-email"><?= htmlspecialchars($user['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge-role <?= htmlspecialchars($role) ?>"><?= ucfirst($role) ?></span></td>
                                    <td class="td-date"><?= formatDate($user['created_at']) ?></td>
                                    <td>
                                        <?php if ($role !== 'admin'): ?>
                                            <span class="status-indicator <?= $is_active ? 'active-status' : 'suspended-status' ?>">
                                                <?= $is_active ? 'Active' : 'Suspended' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-indicator admin-status">
                                                Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($role !== 'admin'): ?>
                                            <button class="btn-toggle <?= $is_active ? 'can-suspend' : 'can-activate' ?>" data-user-id="<?= $user['id'] ?>" data-current-state="<?= $is_active ?>">
                                                <?= $is_active ? 'Suspend' : 'Activate' ?>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-toggle disabled" disabled style="opacity: 0.5; cursor: not-allowed;">
                                                N/A
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div id="emptyState" class="empty-state <?= empty($users) ? '' : 'hidden' ?>">
                    <div class="empty-icon">🔍</div>
                    <p>No users found matching your filters.</p>
                </div>
            </div>
        </div>
    </main>
    <div class="toast-container" id="toastContainer"></div>
    <script src="../../controller/js/adminDashboard.js"></script>
    <script src="../../controller/js/adminManageUserAjax.js"></script>
</body>
</html>