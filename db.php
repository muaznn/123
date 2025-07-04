<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "physiocare";
$port = 3307; // custom port if default doesn't work

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
