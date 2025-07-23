<?php
include 'db.php';

if (!$conn) {
    die("Database connection failed.");
}

$equipment = isset($_POST['equipment']) ? $_POST['equipment'] : '';
$issue = isset($_POST['issue']) ? trim($_POST['issue']) : '';
$details = isset($_POST['details']) ? trim($_POST['details']) : '';
$submittedBy = 123; // Hardcoded userID for now, replace with session or auth system

$target_dir = __DIR__ . "/uploads/";
if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0755, true)) {
        die("Failed to create upload directory.");
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert into maintenance_request
    $stmt1 = $conn->prepare("INSERT INTO maintenance_request (dateSubmitted, status, submittedBy, isRead) VALUES (NOW(), 'New', ?, 0)");
    if (!$stmt1) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt1->bind_param("i", $submittedBy);
    if (!$stmt1->execute()) {
        throw new Exception("Execute failed: " . $stmt1->error);
    }
    $requestID = $stmt1->insert_id;
    $stmt1->close();

    // Get itemID from item_equipment_info
    $stmt2 = $conn->prepare("SELECT itemID FROM item_equipment_info WHERE itemName = ?");
    if (!$stmt2) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt2->bind_param("s", $equipment);
    if (!$stmt2->execute()) {
        throw new Exception("Execute failed: " . $stmt2->error);
    }
    $stmt2->bind_result($itemID);
    $stmt2->fetch();
    $stmt2->close();

    if (!$itemID) {
        // If no matching item, insert new item (optional)
        $stmt3 = $conn->prepare("INSERT INTO item_equipment_info (itemName) VALUES (?)");
        if (!$stmt3) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt3->bind_param("s", $equipment);
        if (!$stmt3->execute()) {
            throw new Exception("Execute failed: " . $stmt3->error);
        }
        $itemID = $stmt3->insert_id;
        $stmt3->close();
    }

    // Insert into item_maintenance
    $stmt4 = $conn->prepare("INSERT INTO item_maintenance (requestID, itemID, itemIssue, detailsMaintenance) VALUES (?, ?, ?, ?)");
    if (!$stmt4) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt4->bind_param("iiss", $requestID, $itemID, $issue, $details);
    if (!$stmt4->execute()) {
        throw new Exception("Execute failed: " . $stmt4->error);
    }
    $stmt4->close();

    // Handle multiple file uploads
    error_log("FILES array content: " . print_r($_FILES, true));
    if (isset($_FILES['file']) && is_array($_FILES['file']['name']) && count(array_filter($_FILES['file']['name'])) > 0) {
        $files = $_FILES['file'];
        error_log("Processing file uploads for requestID: $requestID");
        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] === UPLOAD_ERR_OK) {
                $originalName = basename($name);
                $uniqueName = uniqid() . '_' . $originalName;
                $target = $target_dir . DIRECTORY_SEPARATOR . $uniqueName;
                if (move_uploaded_file($files['tmp_name'][$index], $target)) {
                    error_log("Moved uploaded file to $target");
                    $stmt5 = $conn->prepare("INSERT INTO maintenance_request_images (requestID, imagePath) VALUES (?, ?)");
                    if (!$stmt5) {
                        error_log("Prepare failed: " . $conn->error);
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $stmt5->bind_param("is", $requestID, $uniqueName);
                    if (!$stmt5->execute()) {
                        error_log("Failed to insert image record: " . $stmt5->error);
                        throw new Exception("Execute failed: " . $stmt5->error);
                    } else {
                        error_log("Inserted image record for $uniqueName");
                    }
                    $stmt5->close();
                } else {
                    error_log("Failed to move uploaded file: " . $files['tmp_name'][$index]);
                    throw new Exception("Failed to move uploaded file: " . $name);
                }
            } else {
                error_log("File upload error code: " . $files['error'][$index]);
                throw new Exception("File upload error code: " . $files['error'][$index]);
            }
        }
    }

    // Commit transaction
    if (!$conn->commit()) {
        throw new Exception("Transaction commit failed: " . $conn->error);
    }

    header("Location: view_request.html");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
