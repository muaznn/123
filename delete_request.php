<?php
// delete_request.php
// This script handles the deletion of an item request based on requestID passed via GET

header('Content-Type: text/plain');

if (!isset($_GET['itemID'])) {
    echo "error: missing requestID";
    exit;
}

$requestID = $_GET['itemID'];

// Include database connection
include 'db_connection.php'; // Adjust the path if needed

 // Assuming OpenCon() is the function to open DB connection

if (!$conn) {
    echo "error: database connection failed";
    exit;
}

// Prepare and execute delete query
$sql = "DELETE FROM item_equipment_info WHERE itemID = ?"; // Adjust table and column names as per your DB schema

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "error: failed to prepare statement";
    $conn->close();
    exit;
}

$stmt->bind_param("i", $requestID);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "success";
    } else {
        echo "error: no record deleted";
    }
} else {
    echo "error: failed to execute delete";
}

$stmt->close();
$conn->close();
?>
