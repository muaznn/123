<?php
include 'db_connection.php';

if (isset($_GET['requestID'])) {
    $requestID = intval($_GET['requestID']);

    $usageIDs = [];
    $getUsage = $conn->query("SELECT usageID FROM usage_record WHERE requestID = $requestID");
    while ($row = $getUsage->fetch_assoc()) {
        $usageIDs[] = $row['usageID'];
    }

    foreach ($usageIDs as $usageID) {
        $conn->query("DELETE FROM item_usage WHERE usageID = $usageID");
    }

    $conn->query("DELETE FROM usage_record WHERE requestID = $requestID");

    $conn->query("DELETE FROM item_maintenance WHERE requestID = $requestID");

    $sql = "DELETE FROM maintenance_request WHERE requestID = $requestID";
    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "fail: " . $conn->error;
    }

} else {
    echo "no-id";
}

$conn->close();
?>
