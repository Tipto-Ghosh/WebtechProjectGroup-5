<?php
include "../model/loginModel.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $_SESSION['old_email'] = $email;
    $_SESSION['old_password'] = $password;

    $errors = [];

    if(empty($email)){
        $errors["email"] = "Email can't be empty";
    } 
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please provide a valid email address.";
    }

    if (empty($password)) {
        $errors['password'] = "Please provide a password";
    }

    if (!empty($errors)) {
        $_SESSION['email_error'] = $errors['email'] ?? null;
        $_SESSION['password_error'] = $errors['password'] ?? null;
        header('Location: ../view/auth/login.php');
        exit;
    }

    
    $user = loginUser($email, $password);

    if ($user === false) {
        $_SESSION['login_alert'] = 'invalid';
        header('Location: ../view/auth/login.php');
        exit;
    }

    if ((int)($user['is_active'] ?? 1) === 0) {
        $_SESSION['login_alert'] = 'suspended';
        header('Location: ../view/auth/login.php');
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    unset($_SESSION['old_email'],$_SESSION['old_password'], $_SESSION['email_error'], $_SESSION['password_error'], $_SESSION['login_alert']);

    // Redirect based on role
    $destinations = [
        "student"=> "../view/student/dashboard.php",
        "instructor"=> "../view/instructor/dashboard.php",
        "admin"=> "../view/admin/dashboard.php",
    ];

    $target = $destinations[$user['role']] ?? "../view/student/dashboard.php";
    header("Location: $target");
    exit;

} else {
    header('Location: ../view/auth/login.php');
    exit;
}