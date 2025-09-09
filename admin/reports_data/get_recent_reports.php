<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Query to get recent reports (last 10 reports)
    $sql = "SELECT r.report_id, r.child_id, r.generated_by, r.report_type, r.report_date,
                   c.first_name, c.last_name,
                   ud.full_name as generated_by_name
            FROM tbl_report r
            LEFT JOIN tbl_child c ON r.child_id = c.child_id
            LEFT JOIN tbl_user_details ud ON r.generated_by = ud.user_id
            ORDER BY r.report_date DESC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'reports' => $reports,
        'total' => count($reports)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch recent reports: ' . $e->getMessage()
    ]);
}

$conn->close();
?>