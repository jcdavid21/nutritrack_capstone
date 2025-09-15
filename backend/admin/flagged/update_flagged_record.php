<?php
header('Content-Type: application/json');
include_once "../../config.php";
include_once '../../reports/audit_log.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    date_default_timezone_set('Asia/Manila');
    
    // Get POST data
    $flagged_id = isset($_POST['flagged_id']) ? intval($_POST['flagged_id']) : 0;
    $issue_type = isset($_POST['issue_type']) ? trim($_POST['issue_type']) : '';
    $flagged_status = isset($_POST['flagged_status']) ? trim($_POST['flagged_status']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $resolution_notes = isset($_POST['resolution_notes']) ? trim($_POST['resolution_notes']) : null;
    $resolution_date = isset($_POST['resolution_date']) ? trim($_POST['resolution_date']) : null;
    
    // New resolution fields - Handle empty strings as NULL
    $resolution_type = isset($_POST['resolution_type']) && $_POST['resolution_type'] !== '' ? trim($_POST['resolution_type']) : null;
    $current_status = isset($_POST['current_status']) && $_POST['current_status'] !== '' ? trim($_POST['current_status']) : null;
    $follow_up_date = isset($_POST['follow_up_date']) && $_POST['follow_up_date'] !== '' ? trim($_POST['follow_up_date']) : null;

    // Validate required fields
    if (!$flagged_id) {
        throw new Exception('Flagged record ID is required');
    }
    
    if (empty($issue_type)) {
        throw new Exception('Issue type is required');
    }
    
    if (empty($flagged_status)) {
        throw new Exception('Status is required');
    }

    // Check if flagged record exists
    $check_sql = "SELECT flagged_id, child_id FROM tbl_flagged_record WHERE flagged_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $flagged_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Flagged record not found');
    }
    
    $record = $check_result->fetch_assoc();

    // If status is being changed to resolved, ensure required fields are provided
    if ($flagged_status === 'Resolved') {
        if (empty($resolution_notes)) {
            throw new Exception('Resolution notes are required when marking as resolved');
        }
        if (empty($resolution_type)) {
            throw new Exception('Resolution type is required when marking as resolved');
        }
        if (empty($resolution_date)) {
            $resolution_date = date('Y-m-d'); // Use current date if not provided
        }
        // If resolution type is 'improved', require current status
        if ($resolution_type === 'improved' && empty($current_status)) {
            throw new Exception('Current status is required when marking as improved');
        }
    } else {
        // Clear resolution data if status is not resolved
        $resolution_notes = null;
        $resolution_date = null;
        $resolution_type = null;
        $current_status = null;
        $follow_up_date = null;
    }

    // Convert empty date strings to NULL for database
    if ($resolution_date === '') $resolution_date = null;
    if ($follow_up_date === '') $follow_up_date = null;

    // Check for duplicate active flagged records (excluding current record)
    if ($flagged_status !== 'Resolved') {
        $duplicate_check_sql = "SELECT flagged_id FROM tbl_flagged_record 
                               WHERE child_id = ? AND issue_type = ? AND flagged_status IN ('Active', 'Under Review') 
                               AND flagged_id != ?";
        $duplicate_stmt = $conn->prepare($duplicate_check_sql);
        $duplicate_stmt->bind_param("isi", $record['child_id'], $issue_type, $flagged_id);
        $duplicate_stmt->execute();
        $duplicate_result = $duplicate_stmt->get_result();
        
        if ($duplicate_result->num_rows > 0) {
            throw new Exception('Another active or under review record already exists for this child and issue type');
        }
    }

    // Update flagged record - always update all fields to handle NULL values properly
    $sql = "UPDATE tbl_flagged_record 
            SET issue_type = ?, 
                flagged_status = ?, 
                description = ?, 
                resolution_notes = ?, 
                resolution_date = ?, 
                resolution_type = ?,
                current_status = ?, 
                follow_up_date = ?
            WHERE flagged_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", 
        $issue_type, 
        $flagged_status, 
        $description, 
        $resolution_notes, 
        $resolution_date, 
        $resolution_type,
        $current_status, 
        $follow_up_date, 
        $flagged_id
    );
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log the activity
            $user_id = $_SESSION['user_id'] ?? null;
            $activity_type = "Updated flagged record with ID: " . $flagged_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode([
                'status' => 'success',
                'message' => 'Flagged record updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'info',
                'message' => 'No changes were made to the record'
            ]);
        }
    } else {
        throw new Exception('Failed to update flagged record: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>