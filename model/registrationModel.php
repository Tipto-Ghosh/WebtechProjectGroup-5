<?php
include "../config/dbConnection.php";

function registrationUser(array $user_data) {
    $connection = get_database_connection();
    $sql = "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)";
    $statement = $connection->prepare($sql);

    $statement->bind_param("ssss",$user_data['full_name'],$user_data['email'],$user_data['password_hash'],$user_data['role']);
    $result = $statement->execute();
    $statement->close();
    return $result;
}

function emailExists(string $email){
    $connection = get_database_connection();
    $stmt = $connection->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}
?>