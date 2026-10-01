<?php
// includes/config.php

$db_host = "localhost";
$db_username = "root";
$db_passwd = "";
$db_name = "db_roast_and_grind";

// Establish database connection using mysqli
$conn = mysqli_connect($db_host, $db_username, $db_passwd, $db_name);

// Check if connection was successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
