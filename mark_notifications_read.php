<?php
include 'db_connection.php';

$sql = "UPDATE maintenance_request SET isRead = 1 WHERE status IN ('New') AND isRead = 0";
$conn->query($sql);
$conn->close();
?>