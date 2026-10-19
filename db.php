
<?php

$host = "localhost";
$username = "root";
$password = "";
$dbname = "studenthub";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Database connection failed.");
}

mysqli_set_charset($conn, "utf8mb4");

?>
