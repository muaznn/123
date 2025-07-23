<?php
header('Content-Type: application/json');
include 'db_connection.php';

// Query to get the most used item from item_usage
$sqlMostUsed = "
SELECT iei.itemName, SUM(iu.quantityUsed) AS totalUsed
FROM item_usage iu
INNER JOIN item_equipment_info iei ON iu.itemID = iei.itemID
GROUP BY iu.itemID
ORDER BY totalUsed DESC
LIMIT 1
";

// Query to get the most serviced item from item_maintenance
$sqlMostServiced = "
SELECT iei.itemName, COUNT(im.requestID) AS totalServiced
FROM item_maintenance im
INNER JOIN item_equipment_info iei ON im.itemID = iei.itemID
GROUP BY im.itemID
ORDER BY totalServiced DESC
LIMIT 1
";

$response = [
    'mostUsed' => null,
    'mostServiced' => null
];

// Fetch most used item
$resultUsed = $conn->query($sqlMostUsed);
if ($resultUsed && $resultUsed->num_rows > 0) {
    $row = $resultUsed->fetch_assoc();
    $response['mostUsed'] = $row['itemName'];
}

// Fetch most serviced item
$resultServiced = $conn->query($sqlMostServiced);
if ($resultServiced && $resultServiced->num_rows > 0) {
    $row = $resultServiced->fetch_assoc();
    $response['mostServiced'] = $row['itemName'];
}

echo json_encode($response);
?>
