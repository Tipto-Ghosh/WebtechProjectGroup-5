<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../view/auth/login.php');
    exit;
}
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ../view/student/dashboard.php');
    exit;
}

include_once __DIR__ . '/../model/adminDashboardModel.php';

// Fetch all required data
$total_students = getTotalStudents();
$total_instructors = getTotalInstructors();
$total_admins = getTotalAdmins();
$active_accounts = getActiveAccounts();
$suspended_accounts = getSuspendedAccounts();
$live_quizzes = getLiveQuizzes();
$draft_quizzes = getDraftQuizzes();
$total_attempts = getTotalAttempts();
$avg_score_percent = getAverageScorePercent();
$scoreStats = getScoreStats();
$score_high = $scoreStats['high'];
$score_low = $scoreStats['low'];
$score_median = $scoreStats['median'];
$recent_users = getRecentUsers(5);

$recent_users_rows = '';

if (!empty($recent_users)) {
    foreach ($recent_users as $u) {
        $recent_users_rows .= '<tr data-user-id="' . htmlspecialchars($u['id']) . '">
            <td>' . htmlspecialchars($u['name']) . '</td>
            <td><span class="role-badge role-badge--' . htmlspecialchars($u['role']) . '">' . htmlspecialchars($u['role']) . '</span></td>
            <td>' . htmlspecialchars(date('M d, Y', strtotime($u['created_at']))) . '</td>
            <td><span class="status-dot ' . ($u['is_active'] ? 'status-dot--active' : 'status-dot--suspended') . '">' . ($u['is_active'] ? 'Active' : 'Suspended') . '</span></td>
        </tr>';
    }
} else {
    $recent_users_rows = '<tr class="recent-table__empty"><td colspan="4">No users yet.</td></tr>';
}

// 4. Admin name from session
$admin_name = $_SESSION['user_name'] ?? 'Admin';

// 5. If AJAX request, return JSON and exit; otherwise, the view will include this file and use the variables
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    $response = [
        'total_students' => $total_students,
        'total_instructors'=> $total_instructors,
        'total_admins'=> $total_admins,
        'active_accounts' => $active_accounts,
        'suspended_accounts' => $suspended_accounts,
        'live_quizzes' => $live_quizzes,
        'draft_quizzes' => $draft_quizzes,
        'total_attempts' => $total_attempts,
        'avg_score_percent' => $avg_score_percent,
        'score_high' => $score_high,
        'score_low' => $score_low,
        'score_median' => $score_median,
        'recent_users'=> $recent_users,
        'admin_name' => $admin_name
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>