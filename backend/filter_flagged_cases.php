<?php
include_once("./config.php");
header('Content-Type: application/json');

$zone_id = $_GET['zone_id'] ?? null;

if ($zone_id) {
    $query = "SELECT DATE_FORMAT(fr.date_flagged, '%Y-%m') as month, COUNT(*) as count 
              FROM tbl_flagged_record fr 
              JOIN tbl_child c ON fr.child_id = c.child_id 
              WHERE c.zone_id = ? 
              GROUP BY DATE_FORMAT(fr.date_flagged, '%Y-%m') 
              ORDER BY month DESC LIMIT 6";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $zone_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    echo json_encode(array_reverse($data));
} else {
    echo json_encode([]);
}
?>