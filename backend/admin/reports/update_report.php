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
    $report_type = isset($_POST['report_type']) ? trim($_POST['report_type']) : '';
    $report_date = isset($_POST['report_date']) ? trim($_POST['report_date']) : '';
    
    // Validate input
    if ($report_id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid report ID'
        ]);
        exit;
    }
    
    if (empty($report_type)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Report type is required'
        ]);
        exit;
    }
    
    if (empty($report_date)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Report date is required'
        ]);
        exit;
    }
    
    // Validate date format
    $date = DateTime::createFromFormat('Y-m-d\TH:i', $report_date);
    if (!$date) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid date format'
        ]);
        exit;
    }
    
    // Check if report exists
    $reportCheckQuery = "SELECT report_id FROM tbl_report WHERE report_id = ?";
    $reportCheckStmt = $conn->prepare($reportCheckQuery);
    $reportCheckStmt->bind_param("i", $report_id);
    $reportCheckStmt->execute();
    $result = $reportCheckStmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Report not found'
        ]);
        exit;
    }
    
    // Update report
    $updateQuery = "
        UPDATE tbl_report 
        SET report_type = ?, report_date = ? 
        WHERE report_id = ?
    ";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $report_type, $report_date, $report_id);
    $success = $updateStmt->execute();
    
    if ($success) {
        // Log the activity
        date_default_timezone_set('Asia/Manila');
        $user_id = $_SESSION['user_id'] ?? null;
        $activity_type = "Updated report with ID: " . $report_id;
        $log_date = date('Y-m-d H:i:s');
        audit_log($conn, $user_id, $activity_type, $log_date);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Report updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update report'
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