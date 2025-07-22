<?php
include 'db_connection.php';
header('Content-Type: application/json');

$sql = "
SELECT 
    mr.requestID,
    mr.dateSubmitted,
    mr.status,
    u.name AS submittedBy,
    COALESCE(i.itemName, 'No item') AS itemName
FROM maintenance_request mr
LEFT JOIN user u ON mr.submittedBy = u.userID
LEFT JOIN item_maintenance im ON mr.requestID = im.requestID
LEFT JOIN item_equipment_info i ON im.itemID = i.itemID
WHERE mr.status IN ('New')
ORDER BY mr.dateSubmitted DESC
";

$result = $conn->query($sql);
$requests = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = [
            'requestID' => $row['requestID'],
            'dateSubmitted' => $row['dateSubmitted'],
            'status' => $row['status'],
            'submittedBy' => $row['submittedBy'],
            'itemName' => $row['itemName']
        ];
    }
}

echo json_encode($requests);
$conn->close();
?>
