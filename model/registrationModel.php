<?php
include "../config/dbConnection.php";

function registrationUser(array $user_data) {
    $connection = get_database_connection();
    $sql = "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)";
    $statement = $connection->prepare($sql);

    $statement->bind_param("ssss",$user_data['full_name'],$user_data['email'],$user_data['password_hash'],$user_data['role']);
    $result = $statement->execute();
    return $result;
}
?>