<?php
include_once("./config.php");

$zone_id = isset($_GET['zone_id']) ? intval($_GET['zone_id']) : null;

$query = "SELECT 
    CASE 
        WHEN completed_children.child_id IS NOT NULL THEN 'Completed'
        ELSE 'Not Completed'
    END as vaccine_status,
    COUNT(*) as count
FROM (
    SELECT DISTINCT c.child_id
    FROM tbl_child c
    JOIN tbl_barangay b ON c.zone_id = b.zone_id";

if ($zone_id) {
    $query .= " WHERE b.zone_id = $zone_id";
}

$query .= "
) zone_children
LEFT JOIN (
    SELECT DISTINCT vr.child_id
    FROM tbl_vaccine_record vr
    JOIN tbl_child c ON vr.child_id = c.child_id
    JOIN tbl_barangay b ON c.zone_id = b.zone_id
    WHERE vr.vaccine_status = 'Completed'";

if ($zone_id) {
    $query .= " AND b.zone_id = $zone_id";
}

$query .= "
) completed_children ON zone_children.child_id = completed_children.child_id
GROUP BY vaccine_status";

$result = mysqli_query($conn, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>