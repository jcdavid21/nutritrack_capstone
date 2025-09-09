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
    $child_id = isset($_POST['child_id']) ? intval($_POST['child_id']) : 0;
    $report_type = isset($_POST['report_type']) ? trim($_POST['report_type']) : '';
    $report_date = isset($_POST['report_date']) ? trim($_POST['report_date']) : '';
    
    // Validate input
    if ($child_id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid child ID'
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
    
    // Get current user ID from session (fallback to 1001 for demo)
    $generated_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1001;
    
    // Check if child exists
    $childCheckQuery = "SELECT child_id FROM tbl_child WHERE child_id = ?";
    $childCheckStmt = $conn->prepare($childCheckQuery);
    $childCheckStmt->bind_param("i", $child_id);
    $childCheckStmt->execute();
    $childResult = $childCheckStmt->get_result();

    if ($childResult->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Child not found'
        ]);
        exit;
    }
    
    // Insert new report
    $insertQuery = "
        INSERT INTO tbl_report (child_id, generated_by, report_type, report_date) 
        VALUES (?, ?, ?, ?)
    ";
    date_default_timezone_set('Asia/Manila');
    $report_date = date('Y-m-d H:i:s', strtotime($report_date));
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iiss", $child_id, $generated_by, $report_type, $report_date);
    $success = $insertStmt->execute();
    
    if ($success) {
        $report_id = $conn->insert_id;
        // Log the activity
        date_default_timezone_set('Asia/Manila');
        $user_id = $_SESSION['user_id'] ?? null;
        $activity_type = "Generated report with ID: " . $report_id . " for child ID: " . $child_id;
        $log_date = date('Y-m-d H:i:s');
        audit_log($conn, $user_id, $activity_type, $log_date);

        echo json_encode([
            'status' => 'success',
            'message' => 'Report generated successfully',
            'report_id' => $report_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to generate report'
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