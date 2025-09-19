<?php
include_once("config.php");

header('Content-Type: application/json');

$year = isset($_GET['year']) ? $_GET['year'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';
$case_type = isset($_GET['case_type']) ? $_GET['case_type'] : '';
$zone_id = isset($_GET['zone_id']) ? $_GET['zone_id'] : '';

$query = "SELECT 
    DATE_FORMAT(fr.date_flagged, '%Y-%m') as month,
    YEAR(fr.date_flagged) as year,
    MONTH(fr.date_flagged) as month_num,
    MONTHNAME(fr.date_flagged) as month_name,
    ft.flagged_name,
    ft.ft_id,
    COUNT(*) as count 
    FROM tbl_flagged_record fr
    JOIN tbl_flagged_type ft ON fr.issue_type = ft.ft_id";

// Add zone filter if needed
if (!empty($zone_id)) {
    $query .= " JOIN tbl_child c ON fr.child_id = c.child_id
                JOIN tbl_barangay b ON c.zone_id = b.zone_id";
}

$query .= " WHERE 1=1";

$conditions = [];
$params = [];
$types = '';

// Add year filter
if (!empty($year)) {
    $conditions[] = "YEAR(fr.date_flagged) = ?";
    $params[] = $year;
    $types .= 'i';
}

// Add month filter
if (!empty($month)) {
    $conditions[] = "DATE_FORMAT(fr.date_flagged, '%Y-%m') = ?";
    $params[] = $month;
    $types .= 's';
}

// Add case type filter
if (!empty($case_type)) {
    $conditions[] = "ft.ft_id = ?";
    $params[] = $case_type;
    $types .= 'i';
}

// Add zone filter
if (!empty($zone_id)) {
    $conditions[] = "b.zone_id = ?";
    $params[] = $zone_id;
    $types .= 'i';
}

// Add conditions to query
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " GROUP BY DATE_FORMAT(fr.date_flagged, '%Y-%m'), ft.ft_id, ft.flagged_name
            ORDER BY fr.date_flagged DESC, ft.flagged_name ASC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo json_encode($data);
?>