<?php
include_once("config.php");

$year = isset($_GET['year']) ? $_GET['year'] : 'all';
$zone_id = isset($_GET['zone_id']) && $_GET['zone_id'] !== '' ? intval($_GET['zone_id']) : null;

$query = "SELECT 
    DATE_FORMAT(fr.date_flagged, '%Y-%m') as month,
    YEAR(fr.date_flagged) as year,
    MONTH(fr.date_flagged) as month_num,
    MONTHNAME(fr.date_flagged) as month_name,
    COUNT(*) as count 
    FROM tbl_flagged_record fr";

$where_conditions = [];

if ($zone_id) {
    $query .= " JOIN tbl_child c ON fr.child_id = c.child_id 
                JOIN tbl_barangay b ON c.zone_id = b.zone_id";
    $where_conditions[] = "b.zone_id = $zone_id";
}

if ($year !== 'all') {
    $year_int = intval($year);
    $where_conditions[] = "YEAR(fr.date_flagged) = $year_int";
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

if ($year === 'all') {
    $query .= " GROUP BY DATE_FORMAT(fr.date_flagged, '%Y-%m') ORDER BY month DESC LIMIT 12";
} else {
    $query .= " GROUP BY DATE_FORMAT(fr.date_flagged, '%Y-%m') ORDER BY month ASC";
}

$result = mysqli_query($conn, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>