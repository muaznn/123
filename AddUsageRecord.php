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
            SELECT i.itemID, i.itemName, i.descr, i.quantity AS availableQuantity, img.imagePath
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

// Submit usage record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    $itemID = $_POST['itemID'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 0);
    $dateSubmitted = $_POST['date-submitted'] ?? date('Y-m-d');

    if (!$itemID || $quantity <= 0) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    try {
        // Check available quantity
        $checkStmt = $conn->prepare("SELECT quantity FROM item_equipment_info WHERE itemID = ?");
        $checkStmt->bind_param("i", $itemID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(["success" => false, "message" => "Item not found"]);
            exit;
        }

        $item = $result->fetch_assoc();
        $availableQty = (int)$item['quantity'];

        if ($quantity > $availableQty) {
            echo json_encode([
                "success" => false,
                "message" => "Not enough stock! Only $availableQty items available"
            ]);
            exit;
        }

        $conn->begin_transaction();

        // === Handle usageID ===
        $usageID = null;
        if (isset($_SESSION['usageID'])) {
            $checkUsage = $conn->prepare("SELECT usageID FROM usage_record WHERE usageID = ?");
            $checkUsage->bind_param("i", $_SESSION['usageID']);
            $checkUsage->execute();
            $resultUsage = $checkUsage->get_result();

            if ($resultUsage->num_rows > 0) {
                $usageID = $_SESSION['usageID'];
            }
        }

        if (!$usageID) {
            $stmt1 = $conn->prepare("INSERT INTO usage_record (usedBy, usageDate) VALUES (?, ?)");
            $stmt1->bind_param("is", $userID, $dateSubmitted);
            $stmt1->execute();
            $usageID = $stmt1->insert_id;
            $_SESSION['usageID'] = $usageID;
        }

        // Insert item usage
        $stmt2 = $conn->prepare("
            INSERT INTO item_usage (usageID, itemID, quantityUsed)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantityUsed = quantityUsed + VALUES(quantityUsed)
        ");
        $stmt2->bind_param("iii", $usageID, $itemID, $quantity);
        $stmt2->execute();



        // Deduct item quantity
        // $stmt3 = $conn->prepare("UPDATE item_equipment_info SET quantity = quantity - ? WHERE itemID = ?");
        // $stmt3->bind_param("ii", $quantity, $itemID);
        // $stmt3->execute();

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Stock updated successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

echo json_encode(["error" => "Invalid request"]);
?>
