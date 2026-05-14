<?php
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

include __DIR__ . "/../model/quizTakeModel.php";



?>