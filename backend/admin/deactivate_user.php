<?php
include "../config.php";
include_once '../reports/audit_log.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $user_id_session = $_SESSION['user_id'] ?? null;

    // Validate required fields
    if (empty($user_id)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User ID is required.'
        ]);
        exit;
    }

    // Check if user exists
    $user_check = "SELECT user_id FROM tbl_user WHERE user_id = ?";
    $user_check_stmt = mysqli_prepare($conn, $user_check);
    mysqli_stmt_bind_param($user_check_stmt, "i", $user_id);
    mysqli_stmt_execute($user_check_stmt);
    $user_check_result = mysqli_stmt_get_result($user_check_stmt);
    
    if (mysqli_num_rows($user_check_result) === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found.'
        ]);
        mysqli_stmt_close($user_check_stmt);
        exit;
    }
    mysqli_stmt_close($user_check_stmt);

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        $details_query = "UPDATE tbl_user SET status = 'Inactive' WHERE user_id = ?";
        $details_stmt = mysqli_prepare($conn, $details_query);
        mysqli_stmt_bind_param($details_stmt, "i", $user_id);
        
        if (!mysqli_stmt_execute($details_stmt)) {
            throw new Exception("Failed to deactivate user details.");
        }
        mysqli_stmt_close($details_stmt);

        // Commit transaction
        mysqli_commit($conn);

        date_default_timezone_set('Asia/Manila');
        $activity_type = "Deactivated user with ID: " . $user_id;
        $log_date = date('Y-m-d H:i:s');
        audit_log($conn, $user_id_session, $activity_type, $log_date);

        echo json_encode([
            'status' => 'success',
            'message' => 'User deactivated successfully.'
        ]);

    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>