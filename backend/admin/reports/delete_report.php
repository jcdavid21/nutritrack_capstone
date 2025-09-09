<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include_once "../../config.php";
include_once '../../reports/audit_log.php';
session_start();


try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
        exit;
    }
    
    // Get POST data
    $report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
    
    // Validate input
    if ($report_id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid report ID'
        ]);
        exit;
    }
    
    // Check if report exists
    $reportCheckQuery = "SELECT report_id FROM tbl_report WHERE report_id = ?";
    $reportCheckStmt = $conn->prepare($reportCheckQuery);
    $reportCheckStmt->bind_param("i", $report_id);
    $reportCheckStmt->execute();
    $reportCheckResult = $reportCheckStmt->get_result();
    if ($reportCheckResult->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Report not found'
        ]);
        exit;
    }
    
    // Delete report
    $deleteQuery = "DELETE FROM tbl_report WHERE report_id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $report_id);
    $success = $deleteStmt->execute();
    
     // Log the activity
     date_default_timezone_set('Asia/Manila');
     $user_id = $_SESSION['user_id'] ?? null;
     $activity_type = "Deleted report with ID: " . $report_id;
     $log_date = date('Y-m-d H:i:s');
     audit_log($conn, $user_id, $activity_type, $log_date);

    if ($success && $deleteStmt->affected_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Report deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete report'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>