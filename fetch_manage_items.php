<?php
// Disable error display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

include 'db_connection.php';

function log_error($message) {
    error_log($message, 3, 'error_log.txt');
}

if ($conn->connect_error) {
    log_error("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch all items with category from the inventory table
$sql = "SELECT itemID, itemName, category FROM item_equipment_info ORDER BY itemID ASC";

$result = $conn->query($sql);

if (!$result) {
    log_error("Database query failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

$items = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($items, JSON_PRETTY_PRINT);

$conn->close();
?>
