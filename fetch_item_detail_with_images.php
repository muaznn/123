<?php
include 'db_connection.php';

if (isset($_GET['itemID'])) {
    $itemID = $_GET['itemID'];

    // Prepare and execute query to fetch item details
    $query = "SELECT * FROM item_equipment_info WHERE itemID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $itemID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();

        // Fetch images for the item
        $images = [];
        $imgQuery = "SELECT imagePath FROM item_images WHERE itemID = ?";
        $imgStmt = $conn->prepare($imgQuery);
        $imgStmt->bind_param("s", $itemID);
        $imgStmt->execute();
        $imgResult = $imgStmt->get_result();

        while ($imgRow = $imgResult->fetch_assoc()) {
            $images[] = $imgRow['imagePath'];
        }
        $imgStmt->close();

        // Combine item details and images
        $response = [
            'item' => $item,
            'images' => $images
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Item not found']);
    }

    $stmt->close();
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'No item ID provided']);
}

$conn->close();
?>
