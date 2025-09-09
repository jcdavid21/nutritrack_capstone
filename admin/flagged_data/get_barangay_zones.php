<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    $sql = "SELECT zone_id, zone_name FROM tbl_barangay ORDER BY zone_name";
    $result = $conn->query($sql);
    
    $zones = [];
    while ($row = $result->fetch_assoc()) {
        $zones[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'zones' => $zones
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch zones: ' . $e->getMessage()
    ]);
}

$conn->close();
?>