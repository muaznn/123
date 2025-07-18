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

$userID = $_SESSION['userID'];

// Fetch item details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fetch') {
    $itemID = $_POST['itemID'] ?? null;
    if (!$itemID) {
        echo json_encode(["error" => "Missing itemID"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT i.itemID, i.itemName, i.description, img.imagePath
            FROM item_equipment_info i
            LEFT JOIN item_images img ON i.itemID = img.itemID
            WHERE i.itemID = ?
        ");
        $stmt->bind_param("i", $itemID);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(["error" => "Item not found"]);
            exit;
        }

        $item = $result->fetch_assoc();
        echo json_encode($item);
    } catch (Exception $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

// Submit new usage record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    $itemID = $_POST['itemID'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $dateSubmitted = $_POST['date-submitted'] ?? date('Y-m-d');

    if (!$itemID || !$quantity) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    try {
        $conn->begin_transaction();

        // Insert into usage_record
        $stmt1 = $conn->prepare("INSERT INTO usage_record (usedBy, usageDate) VALUES (?, ?)");
        $stmt1->bind_param("is", $userID, $dateSubmitted);
        $stmt1->execute();
        $usageID = $stmt1->insert_id;

        // Store usageID in session
        $_SESSION['usageID'] = $usageID;

        // Insert into item_usage
        $stmt2 = $conn->prepare("INSERT INTO item_usage (usageID, itemID, quantityUsed) VALUES (?, ?, ?)");
        $stmt2->bind_param("iii", $usageID, $itemID, $quantity);
        $stmt2->execute();

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Usage record added successfully", "usageID" => $usageID]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

echo json_encode(["error" => "Invalid request"]);
?>