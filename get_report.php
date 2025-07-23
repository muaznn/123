<?php
include 'db_connection.php';

// fetch report logs with user info and startDate, endDate
$sql = "
SELECT 
    rl.reportID,
    rl.reportType,
    u.name AS userName,
    rl.generatedAt,
    rl.dateStart,
    rl.dateEnd
FROM report_log rl
JOIN user u ON rl.generatedBy = u.userID
ORDER BY rl.generatedAt DESC;
";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit;
}

$rows = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($rows, JSON_PRETTY_PRINT);

$conn->close();
?>
