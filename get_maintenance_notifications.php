<?php
include 'db_connection.php';

$sql = "SELECT id, equipment_name, therapist_name, request_date FROM maintenance_request WHERE status = 'new' ORDER BY request_date DESC";
$result = $conn->query($sql);

$notifications = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'equipment' => $row['equipment_name'],
            'therapist' => $row['therapist_name'],
            'date' => $row['request_date']
        ];
    }
}

echo json_encode($notifications);
$conn->close();
?>
