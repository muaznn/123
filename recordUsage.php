<?php
session_start();
header('Content-Type: application/json');
include 'db_connection.php';

// $usageID = $_SESSION['usageID'];

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search = $conn->real_escape_string($search);
}

// Build SQL
$sql = "SELECT itemID, itemName FROM item_equipment_info WHERE category = 'item'";

if ($search !== '') {
    $sql .= " AND (itemID LIKE '%$search%' OR itemName LIKE '%$search%')";
}

// Query DB
$result = $conn->query($sql);
$items = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

$conn->close();

// Return JSON
echo json_encode($items);
?>
