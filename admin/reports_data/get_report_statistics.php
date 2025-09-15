<?php
header('Content-Type: application/json');
include "../../backend/config.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$report_type = isset($_GET['report_type']) ? trim($_GET['report_type']) : '';
$zone = isset($_GET['zone']) ? trim($_GET['zone']) : '';

$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR r.report_type LIKE ?)";
    $search_param = "{$search}%";
    $params = [$search_param, $search_param, $search_param];
    $types .= 'sss';
}

if (!empty($report_type)) {
    $conditions[] = "r.report_type = ?";
    $params[] = $report_type;
    $types .= 's';
}

if (!empty($zone)) {
    $conditions[] = "c.zone_id = ?";
    $params[] = $zone;
    $types .= 'i';
}

try {
    // Get current date and time boundaries
    $today = date('Y-m-d');
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $month_start = date('Y-m-01');

    // Query for total reports - FIXED: Changed COUNT(r.chil_id) to COUNT(r.child_id)
    $total_sql = "SELECT COUNT(r.child_id) as total FROM tbl_report r 
    INNER JOIN tbl_child c ON r.child_id = c.child_id" . (!empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "");
    $total_stmt = $conn->prepare($total_sql);
    if (!empty($params)) {
        $total_stmt->bind_param($types, ...$params);
    }
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total = $total_result->fetch_assoc()['total'];

    // Query for today's reports - FIXED: Changed COUNT(r.*) to COUNT(*)
    $today_sql = "SELECT COUNT(*) as today FROM tbl_report r
    INNER JOIN tbl_child c ON r.child_id = c.child_id
     WHERE DATE(r.report_date) = ? " . (!empty($conditions) ? " AND " . implode(" AND ", $conditions) : "");
    $today_stmt = $conn->prepare($today_sql);
    if (!empty($conditions)) {
        $today_stmt->bind_param('s' . $types, $today, ...$params);
    } else {
        $today_stmt->bind_param('s', $today);
    }
    $today_stmt->execute();
    $today_result = $today_stmt->get_result();
    $todayCount = $today_result->fetch_assoc()['today'];

    // Query for this week's reports - FIXED: Changed COUNT(r.*) to COUNT(*)
    $week_sql = "SELECT COUNT(*) as this_week FROM tbl_report r
    INNER JOIN tbl_child c ON r.child_id = c.child_id
     WHERE DATE(r.report_date) >= ? " . (!empty($conditions) ? " AND " . implode(" AND ", $conditions) : "");
    $week_stmt = $conn->prepare($week_sql);
    if (!empty($conditions)) {
        $week_stmt->bind_param('s' . $types, $week_start, ...$params);
    } else {
        $week_stmt->bind_param('s', $week_start);
    }
    $week_stmt->execute();
    $week_result = $week_stmt->get_result();
    $weekCount = $week_result->fetch_assoc()['this_week'];

    // Query for this month's reports - FIXED: Changed COUNT(r.*) to COUNT(*)
    $month_sql = "SELECT COUNT(*) as this_month FROM tbl_report r
    INNER JOIN tbl_child c ON r.child_id = c.child_id
     WHERE DATE(r.report_date) >= ? " . (!empty($conditions) ? " AND " . implode(" AND ", $conditions) : "");
    $month_stmt = $conn->prepare($month_sql);
    if (!empty($conditions)) {
        $month_stmt->bind_param('s' . $types, $month_start, ...$params);
    } else {
        $month_stmt->bind_param('s', $month_start);
    }
    $month_stmt->execute();
    $month_result = $month_stmt->get_result();
    $monthCount = $month_result->fetch_assoc()['this_month'];

    // Query for report type distribution
    $type_sql = "SELECT 
                COUNT(*) as total_reports,
                SUM(CASE WHEN r.report_type LIKE '%malnutrition%' OR r.report_type LIKE '%Malnutrition%' THEN 1 ELSE 0 END) as malnutrition,
                SUM(CASE WHEN r.report_type LIKE '%growth%' OR r.report_type LIKE '%Growth%' THEN 1 ELSE 0 END) as growth,
                SUM(CASE WHEN r.report_type LIKE '%vaccination%' OR r.report_type LIKE '%Vaccination%' THEN 1 ELSE 0 END) as vaccination,
                SUM(CASE WHEN r.report_type LIKE '%nutrition%' OR r.report_type LIKE '%Nutrition%' AND r.report_type NOT LIKE '%malnutrition%' AND r.report_type NOT LIKE '%Malnutrition%' THEN 1 ELSE 0 END) as nutrition
             FROM tbl_report r
             INNER JOIN tbl_child c ON r.child_id = c.child_id" . (!empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "");

    $type_stmt = $conn->prepare($type_sql);
    if (!empty($params)) {
        $type_stmt->bind_param($types, ...$params);
    }
    $type_stmt->execute();
    $type_result = $type_stmt->get_result();
    $typeStats = $type_result->fetch_assoc();

    // Calculate others count
    $categorized = intval($typeStats['malnutrition']) + intval($typeStats['growth']) + intval($typeStats['vaccination']) + intval($typeStats['nutrition']);
    $others = intval($typeStats['total_reports']) - $categorized;

    echo json_encode([
        'status' => 'success',
        'total' => intval($total),
        'today' => intval($todayCount),
        'this_week' => intval($weekCount),
        'this_month' => intval($monthCount),
        'malnutrition' => intval($typeStats['malnutrition']),
        'growth' => intval($typeStats['growth']),
        'vaccination' => intval($typeStats['vaccination']),
        'nutrition' => intval($typeStats['nutrition']),
        'others' => $others
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch report statistics: ' . $e->getMessage()
    ]);
}

$conn->close();
