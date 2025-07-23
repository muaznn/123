<?php
header('Content-Type: text/plain');

if (!isset($_GET['itemID'])) {
    echo "error: missing itemID";
    exit;
}

$itemID = $_GET['itemID'];

include 'db_connection.php';

if (!$conn) {
    echo "error: database connection failed";
    exit;
}

// Delete from item_usage
$sql_usage = "DELETE FROM item_usage WHERE itemID = ?";
$stmt_usage = $conn->prepare($sql_usage);
if (!$stmt_usage) {
    echo "error: failed to prepare usage delete - " . $conn->error;
    $conn->close();
    exit;
}
$stmt_usage->bind_param("s", $itemID);
if (!$stmt_usage->execute()) {
    echo "error: failed to delete from item_usage - " . $stmt_usage->error;
    $stmt_usage->close();
    $conn->close();
    exit;
}
$stmt_usage->close();

// Delete from item_equipment_info
$sql_item = "DELETE FROM item_equipment_info WHERE itemID = ?";
$stmt_item = $conn->prepare($sql_item);
if (!$stmt_item) {
    echo "error: failed to prepare item delete - " . $conn->error;
    $conn->close();
    exit;
}
$stmt_item->bind_param("s", $itemID);
if (!$stmt_item->execute()) {
    echo "error: failed to delete from item_equipment_info - " . $stmt_item->error;
    $stmt_item->close();
    $conn->close();
    exit;
}

if ($stmt_item->affected_rows > 0) {
    echo "success";
} else {
    echo "error: no record deleted";
}

$stmt_item->close();
$conn->close();
?>
