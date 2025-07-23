<?php
include 'db.php';
header('Content-Type: application/json');

if (isset($_GET['requestID'])) {
    $requestID = intval($_GET['requestID']);

    $sql = "SELECT 
                mr.requestID,
                mr.dateSubmitted,
                mr.status,
                u.userID,
                u.name AS userName,
                ie.itemID,
                ie.itemName,
                im.itemIssue,
                im.detailsMaintenance
            FROM maintenance_request mr
            LEFT JOIN user u ON mr.submittedBy = u.userID
            LEFT JOIN item_maintenance im ON mr.requestID = im.requestID
            LEFT JOIN item_equipment_info ie ON im.itemID = ie.itemID
            WHERE mr.requestID = $requestID";

    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Request not found"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "No requestID provided"]);
}
?>
