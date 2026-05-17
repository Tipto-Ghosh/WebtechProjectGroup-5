<?php
function validateToggleRequest($post){
    if(!isset($post["user_id"]) || $post["user_id"] === ""){
        return ["valid" => false, "error" => "Missing required field: user_id."];
    }
    elseif(!is_numeric($post["user_id"])){
        return ["valid" => false, "error" => "user_id must be a numeric value."];
    }
    // get the user id
    $id = (int) $post["user_id"];
    if ($id <= 0) {
        return ["valid" => false, "error" => "user_id must be a positive integer."];
    }
    return ["valid" => true, "error" => null];
}

function validateToggleTarget($user, $admin_session_id){
    if($user["role"] === "admin"){
        return ["valid" => false, "error" => "Admin accounts cannot be suspended via this panel."];
    }
    if((int) $user["id"] === $admin_session_id) {
        return ["valid" => false, "error" => "You cannot change the status of your own account."];
    }
    return ["valid" => true, "error" => null];
}

function validateRoleFilter($value){
    $allowed = ['student', 'instructor', 'admin'];
    if (isset($value) && in_array($value, $allowed, true)) {
        return $value;
    }
    return null;
}

function validateSearchQuery($value){
    if (!isset($value) || trim($value) === "") return null;
    $clean = strip_tags(trim((string) $value));
    return mb_substr($clean, 0, 100);
}