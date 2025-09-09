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
    // Validate vaccine_id
    if (!isset($_POST['vaccine_id']) || empty($_POST['vaccine_id'])) {
        throw new Exception('Vaccine ID is required');
    }
    
    $vaccine_id = intval($_POST['vaccine_id']);
    
    // Check if vaccine record exists
    $check_sql = "SELECT vaccine_id FROM tbl_vaccine_record WHERE vaccine_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $vaccine_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Vaccine record not found');
    }
    
    // Delete vaccine record
    $sql = "DELETE FROM tbl_vaccine_record WHERE vaccine_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vaccine_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log the activity
            date_default_timezone_set('Asia/Manila');
            $user_id = $_SESSION['user_id'] ?? null;
            $activity_type = "Deleted vaccine record with ID: " . $vaccine_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode([
                'status' => 'success',
                'message' => 'Vaccine record deleted successfully'
            ]);
        } else {
            throw new Exception('Failed to delete vaccine record');
        }
    } else {
        throw new Exception('Database error occurred while deleting vaccine record');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>