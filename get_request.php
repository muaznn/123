<?php
include 'db_connection.php';

// fetch semua maintenance request
$sql = "
SELECT 
    mr.requestID,
    u.userID,
    u.name AS userName,
    mr.status,
    ie.itemName
FROM maintenance_request mr
JOIN user u ON mr.submittedBy = u.userID
LEFT JOIN item_maintenance im ON mr.requestID = im.requestID
LEFT JOIN item_equipment_info ie ON im.itemID = ie.itemID
ORDER BY mr.requestID DESC;
";

$result = $conn->query($sql);

$rows = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($rows, JSON_PRETTY_PRINT);

$conn->close();
?>
