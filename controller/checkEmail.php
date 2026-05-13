<?php
include "../model/registrationModel.php";

$email = trim($_POST['email'] ?? '');
if(empty($email)){
    echo 'Email required';
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Invalid email format';
    exit;
}
$exists = emailExists($email);
if ($exists) {
    echo 'Email already taken';
} else {
    echo 'Email available';
}
?>