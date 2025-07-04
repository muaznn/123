<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requestID = $_POST["requestID"];
    $status = $_POST["status"];

    // Validate inputs
    if (empty($requestID) || empty($status)) {
        http_response_code(400);
        echo "Missing required fields.";
        exit;
    }

    $stmt = $conn->prepare("UPDATE maintenance_request SET status = ? WHERE requestID = ?");
    $stmt->bind_param("si", $status, $requestID);

    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        http_response_code(500);
        echo "Failed to update status.";
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo "Method not allowed";
}
?>
