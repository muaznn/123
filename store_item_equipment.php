<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

ob_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set header for JSON response
    header('Content-Type: application/json');

    // Collecting data from form fields
    $itemID = isset($_POST['itemID']) ? intval($_POST['itemID']) : 0;
    $itemName = $_POST['itemName'];
    $category = $_POST['category'];
    $status = $_POST['stat'];
    $condition = $_POST['condisi'];
    $quantity = $_POST['quantity'];
    $stockLevel = $_POST['stockLevel'];
    $descr = $_POST['descr'];

    // Validate form fields
    if (empty($itemName) || !is_numeric($quantity) || !is_numeric($stockLevel)) {
        error_log("Invalid input data: itemName='$itemName', quantity='$quantity', stockLevel='$stockLevel'");
        echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
        exit;
    }

    if ($itemID > 0) {
        // Update existing item
        $stmt = $conn->prepare("UPDATE item_equipment_info SET itemName=?, category=?, stat=?, condisi=?, quantity=?, stockLevel=?, descr=? WHERE itemID=?");
        if (!$stmt) {
            error_log("Prepare statement failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'Prepare statement failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("ssssiisi", $itemName, $category, $status, $condition, $quantity, $stockLevel, $descr, $itemID);
        if (!$stmt->execute()) {
            $errorMsg = $stmt->error;
            error_log("Execute statement error: " . $errorMsg);
            $stmt->close();
            $conn->close();
            echo json_encode(['success' => false, 'error' => 'Error: ' . $errorMsg]);
            exit;
        }
        $stmt->close();

        // Handle images update
        // Existing images sent as JSON string
        $existingImages = isset($_POST['existingImages']) ? json_decode($_POST['existingImages'], true) : [];

        // Get current images from DB
        $currentImages = [];
        $res = $conn->query("SELECT imageID, imagePath FROM item_images WHERE itemID = $itemID");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $currentImages[$row['imageID']] = $row['imagePath'];
            }
            $res->free();
        }

        // Determine images to delete (those in DB but not in existingImages)
        $imagesToDelete = [];
        foreach ($currentImages as $imageID => $imagePath) {
            if (!in_array($imagePath, $existingImages)) {
                $imagesToDelete[$imageID] = $imagePath;
            }
        }

        // Delete images from DB and filesystem
        foreach ($imagesToDelete as $imageID => $imagePath) {
            $stmtDel = $conn->prepare("DELETE FROM item_images WHERE imageID = ?");
            if ($stmtDel) {
                $stmtDel->bind_param("i", $imageID);
                $stmtDel->execute();
                $stmtDel->close();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        // Handle new uploaded images
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (!empty($_FILES['image']['name'][0])) {
            foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['image']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = uniqid('img_') . '.' . pathinfo($_FILES['image']['name'][$key], PATHINFO_EXTENSION);
                    $filePath = $uploadDir . $fileName;
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $stmtImg = $conn->prepare("INSERT INTO item_images (itemID, imagePath) VALUES (?, ?)");
                        if ($stmtImg) {
                            $stmtImg->bind_param("is", $itemID, $filePath);
                            if (!$stmtImg->execute()) {
                                $errorImg = $stmtImg->error;
                                error_log("Image insert error: " . $errorImg);
                                $stmtImg->close();
                                $conn->close();
                                echo json_encode(['success' => false, 'error' => 'Image insert error: ' . $errorImg]);
                                exit;
                            }
                            $stmtImg->close();
                        } else {
                            error_log("Prepare statement for image insert failed.");
                            $conn->close();
                            echo json_encode(['success' => false, 'error' => 'Prepare statement for image insert failed.']);
                            exit;
                        }
                    } else {
                        error_log("Failed to move uploaded file.");
                        $conn->close();
                        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
                        exit;
                    }
                } else {
                    error_log("File upload error code: " . $_FILES['image']['error'][$key]);
                    $conn->close();
                    echo json_encode(['success' => false, 'error' => 'File upload error code: ' . $_FILES['image']['error'][$key]]);
                    exit;
                }
            }
        }

        $conn->close();
        echo json_encode(['success' => true, 'itemID' => $itemID]);
        exit;

    } else {
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO item_equipment_info (itemName, category, stat, condisi, quantity, stockLevel, descr) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare statement failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'Prepare statement failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("ssssiis", $itemName, $category, $status, $condition, $quantity, $stockLevel, $descr);

        if ($stmt->execute()) {
            $itemId = $stmt->insert_id;
            $stmt->close();

            // Handle multiple image uploads (image[])
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (!empty($_FILES['image']['name'][0])) {
                foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['image']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = uniqid('img_') . '.' . pathinfo($_FILES['image']['name'][$key], PATHINFO_EXTENSION);
                        $filePath = $uploadDir . $fileName;
                        if (move_uploaded_file($tmpName, $filePath)) {
                            $stmtImg = $conn->prepare("INSERT INTO item_images (itemID, imagePath) VALUES (?, ?)");
                            if ($stmtImg) {
                                $stmtImg->bind_param("is", $itemId, $filePath);
                                if (!$stmtImg->execute()) {
                                    $errorImg = $stmtImg->error;
                                    error_log("Image insert error: " . $errorImg);
                                    $stmtImg->close();
                                    $conn->close();
                                    echo json_encode(['success' => false, 'error' => 'Image insert error: ' . $errorImg]);
                                    exit;
                                }
                                $stmtImg->close();
                            } else {
                                error_log("Prepare statement for image insert failed.");
                                $conn->close();
                                echo json_encode(['success' => false, 'error' => 'Prepare statement for image insert failed.']);
                                exit;
                            }
                        } else {
                            error_log("Failed to move uploaded file.");
                            $conn->close();
                            echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
                            exit;
                        }
                    } else {
                        error_log("File upload error code: " . $_FILES['image']['error'][$key]);
                        $conn->close();
                        echo json_encode(['success' => false, 'error' => 'File upload error code: ' . $_FILES['image']['error'][$key]]);
                        exit;
                    }
                }
            }

            $conn->close();
            echo json_encode(['success' => true, 'itemID' => $itemId]);
            exit;
        } else {
            $errorMsg = $stmt->error;
            error_log("Execute statement error: " . $errorMsg);
            $stmt->close();
            $conn->close();
            echo json_encode(['success' => false, 'error' => 'Error: ' . $errorMsg]);
            exit;
        }
    }
} else {
    // If not POST request, return error JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$conn->close();
ob_end_flush();
?>
