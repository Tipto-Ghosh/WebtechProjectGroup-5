<?php
function get_database_connection(){
    $db_host = "localhost";
    $db_user = "root";
    $db_password = "";
    $db_database = "quiz_platform";

    $connection = new mysqli($db_host, $db_user, $db_password, $db_database);
    if($connection->connect_error){
        die("Database connection failed: ".$connection->connect_error);
    }
    return $connection;
}
?>