<?php
include 'db.php';

header('Content-Type: application/json');

$result = $conn->query("
    SELECT mr.requestID, iei.itemID, iei.itemName AS product_name, iei.itemName AS equipment, mr.dateSubmitted, mr.status
    FROM maintenance_request mr
    LEFT JOIN item_maintenance im ON mr.requestID = im.requestID
    LEFT JOIN item_equipment_info iei ON im.itemID = iei.itemID
    ORDER BY mr.requestID DESC
    LIMIT 10
");

$data = [];

while($row = $result->fetch_assoc()) {
    $dateOnly = date('d/m/Y', strtotime($row['dateSubmitted']));
    $status = $row['status'];
    $data[] = [
        'requestID' => $row['requestID'],
        'product_code' => $row['itemID'],
        'product_name' => $row['product_name'],
        'date' => $dateOnly,
        'status' => $status
    ];
}

echo json_encode($data);
