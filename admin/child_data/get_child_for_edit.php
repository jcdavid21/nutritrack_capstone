<?php
include "../../backend/config.php";

if (!isset($_GET['child_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Child ID required']);
    exit;
}

$child_id = intval($_GET['child_id']);

try {
    // Get child details
    $child_query = "SELECT c.*, z.zone_name FROM tbl_child c 
                   LEFT JOIN tbl_barangay z ON c.zone_id = z.zone_id 
                   WHERE c.child_id = ?";
    $child_stmt = $conn->prepare($child_query);
    $child_stmt->bind_param("i", $child_id);
    $child_stmt->execute();
    $child_result = $child_stmt->get_result();
    $child = $child_result->fetch_assoc();

    // Get all zones
    $zones_query = "SELECT zone_id, zone_name FROM tbl_barangay ORDER BY zone_name";
    $zones_result = $conn->query($zones_query);
    $zones = [];
    while ($zone = $zones_result->fetch_assoc()) {
        $zones[] = $zone;
    }

    echo json_encode([
        'status' => 'success',
        'child' => $child,
        'zones' => $zones
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>