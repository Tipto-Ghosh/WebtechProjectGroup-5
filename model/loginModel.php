<?php
include "../config/dbConnection.php";

function loginUser(string $email, string $password){
    $connection = get_database_connection();
    $sql = "SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ?";
    $statement = $connection->prepare($sql);
    $statement->bind_param("s", $email);
    $statement->execute();
    $result = $statement->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        return false;
    }
    // password check 
    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    unset($user['password_hash']);
    return $user;
}
?>