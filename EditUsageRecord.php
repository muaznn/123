<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check login
if (!isset($_SESSION['userID'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

// Handle GET request - Load usage record data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['usageID']) && isset($_GET['itemID'])) {
    $usageID = $_GET['usageID'];
    $itemID = $_GET['itemID'];

    try {
        // Get complete usage record data with item details
        $stmt = $conn->prepare("
            SELECT 
                u.usageID,
                u.itemID,
                u.quantityUsed,
                r.usageDate,
                i.itemName, 
                i.description,
                img.imagePath
            FROM item_usage u
            JOIN usage_record r ON u.usageID = r.usageID
            JOIN item_equipment_info i ON u.itemID = i.itemID
            LEFT JOIN item_images img ON u.itemID = img.itemID
            WHERE u.usageID = ? AND u.itemID = ?
            AND r.usedBy = ?
        ");
        $stmt->bind_param("isi", $usageID, $itemID, $_SESSION['userID']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(["error" => "Usage record not found"]);
            exit;
        }
        
        $usageData = $result->fetch_assoc();
        echo json_encode($usageData);
        
    } catch (Exception $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

// Handle POST request - Update usage record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usageID = $_POST['usageID'] ?? null;
    $itemID = $_POST['itemID'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $dateUsed = $_POST['date-submitted'] ?? date('Y-m-d');
    
    // Validate inputs
    if (!$usageID || !$itemID || !$quantity) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    try {
        $conn->begin_transaction();
        
        // Verify the record exists and belongs to current user
        $verifyStmt = $conn->prepare("
            SELECT 1 FROM item_usage u
            JOIN usage_record r ON u.usageID = r.usageID
            WHERE u.usageID = ? AND u.itemID = ?
            AND r.usedBy = ?
        ");
        $verifyStmt->bind_param("isi", $usageID, $itemID, $_SESSION['userID']);
        $verifyStmt->execute();
        
        if ($verifyStmt->get_result()->num_rows === 0) {
            throw new Exception("Invalid usage record");
        }

        // Update the quantity in item_usage
        $updateStmt = $conn->prepare("
            UPDATE item_usage 
            SET quantityUsed = ?
            WHERE usageID = ? AND itemID = ?
        ");
        $updateStmt->bind_param("iis", $quantity, $usageID, $itemID);
        $updateStmt->execute();
        
        // Update the date in usage_record
        $updateDateStmt = $conn->prepare("
            UPDATE usage_record
            SET usageDate = ?
            WHERE usageID = ?
        ");
        $updateDateStmt->bind_param("si", $dateUsed, $usageID);
        $updateDateStmt->execute();
        echo json_encode([
        'success' => true,
        'message' => 'Usage record updated successfully'
        ]);

        
        $conn->commit();
        
        
    } catch (Exception $e) {
        $conn->rollback();

    }
    exit;
}

echo json_encode(["error" => "Invalid request"]);
?>