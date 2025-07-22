<?php
include 'db_connection.php';

if (isset($_GET['requestID'])) {
    $requestID = intval($_GET['requestID']);

    // Delete from item_maintenance
    $conn->query("DELETE FROM item_maintenance WHERE requestID = $requestID");

    // Delete from maintenance_request_images
    $conn->query("DELETE FROM maintenance_request_images WHERE requestID = $requestID");

    // Delete from maintenance_request
    $sql = "DELETE FROM maintenance_request WHERE requestID = $requestID";
    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "fail: " . $conn->error;
    }

} else {
    echo "no-id";
}

$conn->close();
?>
