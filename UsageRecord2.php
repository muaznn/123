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

// Handle POST (cancel action)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['usageID'])) {
        echo json_encode(["success" => false, "message" => "Usage ID not found"]);
        exit;
    }

    $usageID = $_SESSION['usageID'];
    $conn->begin_transaction();

    try {
        $stmt1 = $conn->prepare("DELETE FROM item_usage WHERE usageID = ?");
        $stmt1->bind_param("i", $usageID);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("DELETE FROM usage_record WHERE usageID = ?");
        $stmt2->bind_param("i", $usageID);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        unset($_SESSION['usageID']);
        echo json_encode(["success" => true, "message" => "Data cancelled successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Failed to cancel data: " . $e->getMessage()]);
    }

    $conn->close();
    exit;
}

// Handle GET reset
if (isset($_GET['resetUsageID']) && $_GET['resetUsageID'] === 'true') {
    unset($_SESSION['usageID']);
    echo json_encode(["status" => "success", "message" => "usageID unset"]);
    exit;
}

// Verify usageID exists
if (!isset($_SESSION['usageID'])) {
    echo json_encode(["success" => false, "message" => "Usage ID not set in session"]);
    exit;
}

// Fetch usage data
$usageID = $_SESSION['usageID'];
$data = [];

$itemStmt = $conn->prepare("SELECT itemID, quantityUsed FROM item_usage WHERE usageID = ?");
if (!$itemStmt) {
    echo json_encode(["success" => false, "message" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$itemStmt->bind_param("i", $usageID);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

while ($itemRow = $itemResult->fetch_assoc()) {
    $itemID = $itemRow['itemID'];
    $quantityUsed = $itemRow['quantityUsed'];
    
    $infoStmt = $conn->prepare("SELECT itemName FROM item_equipment_info WHERE itemID = ?");
    if ($infoStmt) {
        $infoStmt->bind_param("i", $itemID);
        $infoStmt->execute();
        $infoResult = $infoStmt->get_result();

        if ($infoRow = $infoResult->fetch_assoc()) {
            $data[] = [
                'code' => $itemID,
                'name' => $infoRow['itemName'],
                'quantityUsed' => $quantityUsed,
                'usageID' => $_SESSION['usageID'] // Add this line to include usageID
            ];
        }
        $infoStmt->close();
    }
}

$itemStmt->close();
$conn->close();

echo json_encode($data);