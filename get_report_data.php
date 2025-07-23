<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include 'db_connection.php';

$reportID = isset($_GET['reportID']) ? $_GET['reportID'] : null;

if (!$reportID) {
    echo json_encode(['error' => 'Missing reportID parameter']);
    exit;
}

// Fetch report details from report_log
$sqlReport = "SELECT reportType, dateStart, dateEnd FROM report_log WHERE reportID = ? LIMIT 1";
$stmtReport = $conn->prepare($sqlReport);
if (!$stmtReport) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmtReport->bind_param("i", $reportID);
if (!$stmtReport->execute()) {
    echo json_encode(['error' => 'Execute failed: ' . $stmtReport->error]);
    exit;
}
$resultReport = $stmtReport->get_result();
if (!$resultReport || $resultReport->num_rows === 0) {
    echo json_encode(['error' => 'Report not found']);
    exit;
}
$report = $resultReport->fetch_assoc();
$reportType = strtolower($report['reportType']);
$dateStart = $report['dateStart'];
$dateEnd = $report['dateEnd'];

$stmtReport->close();

if (!$dateStart || !$dateEnd) {
    echo json_encode(['error' => 'Report date range is missing']);
    exit;
}

$data = [];

if ($reportType === 'maintenance') {
    // Fetch maintenance report data
    $sql = "
    SELECT 
        mr.requestID,
        iei.itemName,
        im.itemIssue,
        im.detailsMaintenance,
        mr.dateSubmitted,
        u.name AS submittedBy,
        mr.status,
        '' AS maintenanceDate,
        '' AS handledBy,
        '' AS solution
    FROM maintenance_request mr
    INNER JOIN item_maintenance im ON mr.requestID = im.requestID
    INNER JOIN item_equipment_info iei ON im.itemID = iei.itemID
    LEFT JOIN user u ON mr.submittedBy = u.userID
    WHERE mr.dateSubmitted BETWEEN ? AND ?
    ORDER BY mr.dateSubmitted DESC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ss", $dateStart, $dateEnd);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();
} elseif ($reportType === 'usage') {
    // Fetch usage report data
    $sql = "
    SELECT 
        ur.usageID,
        iei.itemName,
        iu.quantityUsed,
        ur.usageDate,
        u.name AS usedBy
    FROM usage_record ur
    INNER JOIN item_usage iu ON ur.usageID = iu.usageID
    INNER JOIN item_equipment_info iei ON iu.itemID = iei.itemID
    LEFT JOIN user u ON ur.usedBy = u.userID
    WHERE ur.usageDate BETWEEN ? AND ?
    ORDER BY ur.usageDate DESC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ss", $dateStart, $dateEnd);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Unknown report type']);
    exit;
}

echo json_encode($data);
?>
