<?php
include_once __DIR__ . '/../config/dbConnection.php';

function getAllManagedUsers($role_filter = null, $search = null){
    $conn = get_database_connection();
    $sql = "SELECT id, name, email, role, is_active, created_at FROM users";
    $params = [];
    $types  = "";

    if($role_filter !== null && in_array($role_filter, ['student', 'instructor', 'admin'], true)){
        $sql .= " AND role = ?";
        $params[] = $role_filter;
        $types .= "s";
    }
    if($search !== null && $search !== ''){
        $sql .= " AND name LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= "s";
    }
    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if($types){
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $users;
}


function getUserById($id){
    $conn = get_database_connection();
    $stmt = $conn->prepare("SELECT id, name, email, role, is_active, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

function toggleUserActive($id){
    $user = getUserById($id);
    if ($user === false){
        return false;
    }
    $new_state = $user['is_active'] ? 0 : 1;

    $conn = get_database_connection();
    $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_state, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success ? $new_state : false;
}