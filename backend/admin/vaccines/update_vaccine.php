<?php
header('Content-Type: application/json');
include_once "../../config.php";
include_once '../../reports/audit_log.php';
session_start();

// Check if user is logged in and has proper role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['vaccine_id', 'administered_by', 'vaccine_name', 'vaccine_status', 'vaccine_date'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $vaccine_id = intval($_POST['vaccine_id']);
    $administered_by = intval($_POST['administered_by']);
    $vaccine_name = trim($_POST['vaccine_name']);
    $vaccine_status = trim($_POST['vaccine_status']);
    $vaccine_date = trim($_POST['vaccine_date']);
    
    // Validate vaccine status
    $valid_statuses = ['Completed', 'Ongoing', 'Incomplete'];
    if (!in_array($vaccine_status, $valid_statuses)) {
        throw new Exception('Invalid vaccine status');
    }
    
    // Check if vaccine record exists
    $check_sql = "SELECT vaccine_id FROM tbl_vaccine_record WHERE vaccine_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $vaccine_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        throw new Exception('Vaccine record not found');
    }
    
    // Validate administrator exists
    $admin_check_sql = "SELECT user_id FROM tbl_user WHERE user_id = ? AND role_id IN (2, 3)";
    $admin_stmt = $conn->prepare($admin_check_sql);
    $admin_stmt->bind_param('i', $administered_by);
    $admin_stmt->execute();
    if ($admin_stmt->get_result()->num_rows === 0) {
        throw new Exception('Invalid administrator selected');
    }
    
    // Convert datetime format for database
    $vaccine_datetime = date('Y-m-d H:i:s', strtotime($vaccine_date));
    
    // Update vaccine record
    $sql = "UPDATE tbl_vaccine_record 
            SET administered_by = ?, vaccine_name = ?, vaccine_status = ?, vaccine_date = ?
            WHERE vaccine_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssi', $administered_by, $vaccine_name, $vaccine_status, $vaccine_datetime, $vaccine_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {

            // Log the activity
            date_default_timezone_set('Asia/Manila');
            $user_id = $_SESSION['user_id'] ?? null;
            $activity_type = "Updated vaccine record with ID: " . $vaccine_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            
            echo json_encode([
                'status' => 'success',
                'message' => 'Vaccine record updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'info',
                'message' => 'No changes were made to the vaccine record'
            ]);
        }
    } else {
        throw new Exception('Failed to update vaccine record');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>