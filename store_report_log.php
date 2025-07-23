<?php
// Include database connection
include 'db_connection.php';

// Check if form data is sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get report type from POST data
    $reportType = $_POST['reportType'] ?? '';
    $dateStart = $_POST['startDate'] ?? '';
    $dateEnd = $_POST['endDate'] ?? '';

    // TODO: Implement session to get active user ID
    // session_start();
    // $generatedBy = $_SESSION['userID'];

    // For now, use a placeholder user ID (e.g., 1)
    // This causes foreign key constraint error if userID 1 does not exist
    // To avoid error, set generatedBy to NULL or a valid userID
    $generatedBy = 123;

    // Get current datetime
    $generatedAt = date('Y-m-d H:i:s');

    // Prepare and execute insert query
    $stmt = $conn->prepare("INSERT INTO report_log (reportType, generatedBy, generatedAt, dateStart, dateEnd) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $reportType, $generatedBy, $generatedAt, $dateStart, $dateEnd);

    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Report log stored successfully.',
            'reportID' => $stmt->insert_id
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        $response = [
            'success' => false,
            'message' => 'Error storing report log: ' . $stmt->error
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
