<?php
session_start();

// redirect back with errors and old input of user 
function redirectWithError($errors , $oldInput){
    $_SESSION['errors'] = $errors;
    $_SESSION['old_input'] = $oldInput;
    header("Location: ../view/auth/registration.php");
    exit;
}

$role = "";
$full_name = "";
$email = "";
$password = "";
$confirm_password = "";
$terms = "";

if($_SERVER['REQUEST_METHOD'] == "POST") {
    $role = $_POST['role'] ?? "";
    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";
    $terms = isset($_POST['terms']);

    // store old inputs
    $oldInput = ["role" => $role, "full_name" => $full_name, "email" => $email, "terms"  => $terms];
    $errors = [];

    if(!in_array($role , ["student", "instructor"])) {
       $errors['role'] = "Please select a valid role (Student or Instructor).";
    }
    
    if(empty($full_name)) {
       $errors['full_name'] = 'Full name is required.';
    } 
    elseif(strlen($full_name) < 2 || strlen($full_name) > 100) {
       $errors['full_name'] = 'Full name must be between 2 and 100 characters.';
    }

    if(empty($email)) {
       $errors['email'] = 'Email address is required.';
    } 
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       $errors['email'] = 'Please enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } 
    elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } 
    else {
        if(!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter.';
        } 
        elseif(!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must contain at least one number.';
        } 
        elseif(!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors['password'] = 'Password must contain at least one special character (e.g., !@#$%^&*).';
        }
    }
    
    if(empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } 
    elseif($password !== $confirm_password) {
       $errors['confirm_password'] = 'Passwords do not match.';
    }

    if(!$terms) {
        $errors['terms'] = 'You must agree to the Terms of Service and Privacy Policy.';
    }

    // hash the passowrd 
    $hashed_password = password_hash($password , PASSWORD_DEFAULT);

    // make an array for database
    $user_data = [
        "role"=>$role,"full_name"=>$full_name,"email"=>$email,"passoword_hash"=>$hashed_password,"created_at"=>date("Y-m-d H:i:s")
    ];

    $_SESSION['valid_user_data'] = $user_data;
    unset($_SESSION['errors'], $_SESSION['old_input']);
    $_SESSION['reg_success'] = 'Account created successfully! Please log in.';
    header('Location: ../auth/login.php');
    exit;
}
?>