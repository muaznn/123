<?php
include 'db_connection.php';
header('Content-Type: application/json');

$sql = "SELECT COUNT(*) AS newCount FROM maintenance_request WHERE status IN ('New') AND isRead = 0";
$result = $conn->query($sql);

$newCount = 0;
if ($result && $row = $result->fetch_assoc()) {
    $newCount = $row['newCount'];
}

echo json_encode(['newCount' => $newCount]);
$conn->close();
?>
