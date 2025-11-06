<?php
    $db_server = "localhost";
    $db_user   = "root";
    $db_pass   = "";
    $db_name   = "library";

    $connection = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

    mysqli_set_charset($connection, 'utf8mb4');

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    } 
?>
