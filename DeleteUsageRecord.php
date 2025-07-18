<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check login
if (!isset($_SESSION['userID'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

// Only allow DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Get parameters
$usageID = $_GET['usageID'] ?? null;
$itemID = $_GET['itemID'] ?? null;

if (!$usageID || !$itemID) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Verify the record belongs to the current user
    $verifyStmt = $conn->prepare("
        SELECT 1 FROM item_usage u
        JOIN usage_record r ON u.usageID = r.usageID
        WHERE u.usageID = ? AND u.itemID = ?
        AND r.usedBy = ?
    ");
    $verifyStmt->bind_param("isi", $usageID, $itemID, $_SESSION['userID']);
    $verifyStmt->execute();
    
    if ($verifyStmt->get_result()->num_rows === 0) {
        throw new Exception("Record not found or unauthorized");
    }

    // Delete from item_usage first
    $deleteStmt = $conn->prepare("DELETE FROM item_usage WHERE usageID = ? AND itemID = ?");
    $deleteStmt->bind_param("is", $usageID, $itemID);
    $deleteStmt->execute();
    
    // Check if this was the last item in the usage record
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM item_usage WHERE usageID = ?");
    $checkStmt->bind_param("i", $usageID);
    $checkStmt->execute();
    $count = $checkStmt->get_result()->fetch_row()[0];
    
    // If no more items, delete the usage_record
    if ($count == 0) {
        $deleteRecordStmt = $conn->prepare("DELETE FROM usage_record WHERE usageID = ?");
        $deleteRecordStmt->bind_param("i", $usageID);
        $deleteRecordStmt->execute();
    }
    
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Record deleted successfully"]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}

$conn->close();
?>