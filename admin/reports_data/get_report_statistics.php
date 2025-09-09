<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Get current date and time boundaries
    $today = date('Y-m-d');
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $month_start = date('Y-m-01');
    
    // Query for total reports
    $total_sql = "SELECT COUNT(*) as total FROM tbl_report";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total = $total_result->fetch_assoc()['total'];
    
    // Query for today's reports
    $today_sql = "SELECT COUNT(*) as today FROM tbl_report WHERE DATE(report_date) = ?";
    $today_stmt = $conn->prepare($today_sql);
    $today_stmt->bind_param('s', $today);
    $today_stmt->execute();
    $today_result = $today_stmt->get_result();
    $todayCount = $today_result->fetch_assoc()['today'];
    
    // Query for this week's reports
    $week_sql = "SELECT COUNT(*) as this_week FROM tbl_report WHERE DATE(report_date) >= ?";
    $week_stmt = $conn->prepare($week_sql);
    $week_stmt->bind_param('s', $week_start);
    $week_stmt->execute();
    $week_result = $week_stmt->get_result();
    $weekCount = $week_result->fetch_assoc()['this_week'];
    
    // Query for this month's reports
    $month_sql = "SELECT COUNT(*) as this_month FROM tbl_report WHERE DATE(report_date) >= ?";
    $month_stmt = $conn->prepare($month_sql);
    $month_stmt->bind_param('s', $month_start);
    $month_stmt->execute();
    $month_result = $month_stmt->get_result();
    $monthCount = $month_result->fetch_assoc()['this_month'];
    
    // Query for report type distribution
    $type_sql = "SELECT 
                    SUM(CASE WHEN report_type LIKE '%malnutrition%' OR report_type LIKE '%Malnutrition%' THEN 1 ELSE 0 END) as malnutrition,
                    SUM(CASE WHEN report_type LIKE '%growth%' OR report_type LIKE '%Growth%' THEN 1 ELSE 0 END) as growth,
                    SUM(CASE WHEN report_type LIKE '%vaccination%' OR report_type LIKE '%Vaccination%' THEN 1 ELSE 0 END) as vaccination,
                    SUM(CASE WHEN report_type LIKE '%nutrition%' OR report_type LIKE '%Nutrition%' THEN 1 ELSE 0 END) as nutrition
                 FROM tbl_report";
    $type_stmt = $conn->prepare($type_sql);
    $type_stmt->execute();
    $type_result = $type_stmt->get_result();
    $typeStats = $type_result->fetch_assoc();
    
    echo json_encode([
        'status' => 'success',
        'total' => intval($total),
        'today' => intval($todayCount),
        'this_week' => intval($weekCount),
        'this_month' => intval($monthCount),
        'malnutrition' => intval($typeStats['malnutrition']),
        'growth' => intval($typeStats['growth']),
        'vaccination' => intval($typeStats['vaccination']),
        'nutrition' => intval($typeStats['nutrition'])
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch report statistics: ' . $e->getMessage()
    ]);
}

$conn->close();
?>