<?php
session_start();
if(!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../view/auth/login.php");
    exit;
}
require_once __DIR__ . "/../model/adminManageUserModel.php";
require_once __DIR__ . "/adminManageUserValidation.php";

$admin_id   = (int) $_SESSION["user_id"];
$admin_name = $_SESSION["user_name"] ?? "Admin";

$is_ajax = (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
    strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest"
);

if($is_ajax){
    header("Content-Type: application/json");
    $v = validateToggleRequest($_POST);
    if (!$v["valid"]) {
        echo json_encode(["success" => false, "message" => $v["error"]]);
        exit;
    }
    $target_id = (int) $_POST["user_id"];
    $target = getUserById($target_id);
    if (!$target) {
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }
    $vt = validateToggleTarget($target, $admin_id);
    if (!$vt["valid"]) {
        echo json_encode(["success" => false, "message" => $vt["error"]]);
        exit;
    }
    $new_state = toggleUserActive($target_id);
    if ($new_state === false) {
        echo json_encode(["success" => false, "message" => "Database update failed."]);
        exit;
    }
    echo json_encode(["success"=>true,"new_state"=>$new_state,"user_name"=>$target["name"],"message"=>"Status updated successfully."]);
    exit;
}

$role_filter = validateRoleFilter($_GET["role"] ?? null);
$search_q = validateSearchQuery($_GET["search"] ?? null);
$users = getAllManagedUsers($role_filter, $search_q);