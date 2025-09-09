<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Get report ID from query parameter
    $report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;
    
    if ($report_id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid report ID'
        ]);
        exit;
    }
    
    // Query to get specific report with child and user information
    $sql = "SELECT r.report_id, r.child_id, r.generated_by, r.report_type, r.report_date,
                   c.first_name, c.last_name, c.birthdate, c.gender, c.zone_id,
                   b.zone_name,
                   ud.full_name as generated_by_name
            FROM tbl_report r
            LEFT JOIN tbl_child c ON r.child_id = c.child_id
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
            LEFT JOIN tbl_user_details ud ON r.generated_by = ud.user_id
            WHERE r.report_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $report = $result->fetch_assoc();
    
    if (!$report) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Report not found'
        ]);
        exit;
    }
    
    echo json_encode([
        'status' => 'success',
        'report' => $report
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch report: ' . $e->getMessage()
    ]);
}

$conn->close();
?>