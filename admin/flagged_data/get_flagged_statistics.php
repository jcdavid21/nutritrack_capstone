<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Active flags
    $active_sql = "SELECT COUNT(*) as count FROM tbl_flagged_record WHERE flagged_status = 'Active'";
    $active_result = $conn->query($active_sql);
    $active_count = $active_result->fetch_assoc()['count'];
    
    // Under review
    $review_sql = "SELECT COUNT(*) as count FROM tbl_flagged_record WHERE flagged_status = 'Under Review'";
    $review_result = $conn->query($review_sql);
    $review_count = $review_result->fetch_assoc()['count'];
    
    // Resolved
    $resolved_sql = "SELECT COUNT(*) as count FROM tbl_flagged_record WHERE flagged_status = 'Resolved'";
    $resolved_result = $conn->query($resolved_sql);
    $resolved_count = $resolved_result->fetch_assoc()['count'];
    
    // This week (last 7 days)
    $week_sql = "SELECT COUNT(*) as count FROM tbl_flagged_record 
                 WHERE date_flagged >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $week_result = $conn->query($week_sql);
    $week_count = $week_result->fetch_assoc()['count'];
    
    echo json_encode([
        'status' => 'success',
        'active' => $active_count,
        'under_review' => $review_count,
        'resolved' => $resolved_count,
        'this_week' => $week_count
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch statistics: ' . $e->getMessage()
    ]);
}

$conn->close();
?>