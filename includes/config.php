<?php


$db_host = "localhost";
$db_username = "root";
$db_passwd = "";
$db_name = "db_roast_and_grind";


$conn = mysqli_connect($db_host, $db_username, $db_passwd, $db_name);


if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
