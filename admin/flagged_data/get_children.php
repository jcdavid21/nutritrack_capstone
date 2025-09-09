<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    $sql = "SELECT c.child_id, c.first_name, c.last_name, c.birthdate, c.gender,
                   b.zone_name
            FROM tbl_child c
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
            ORDER BY c.first_name, c.last_name";
    
    $result = $conn->query($sql);
    
    $children = [];
    while ($row = $result->fetch_assoc()) {
        $children[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'children' => $children
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch children: ' . $e->getMessage()
    ]);
}

$conn->close();
?>