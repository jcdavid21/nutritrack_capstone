<?php
include "../config.php";
include_once '../reports/audit_log.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $user_id_session = $_SESSION['user_id'] ?? null;
    $password = trim($_POST['password'] ?? '');

    // Validate required fields
    if (empty($user_id) || empty($full_name) || empty($username) || empty($contact) || empty($address) || empty($status) || empty($role_id)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    // Validate status
    if (!in_array($status, ['active', 'inactive'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid status value.'
        ]);
        exit;
    }

    // Check if role exists
    $role_check = "SELECT role_id FROM tbl_roles WHERE role_id = ?";
    $role_stmt = mysqli_prepare($conn, $role_check);
    mysqli_stmt_bind_param($role_stmt, "i", $role_id);
    mysqli_stmt_execute($role_stmt);
    $role_result = mysqli_stmt_get_result($role_stmt);
    
    if (mysqli_num_rows($role_result) === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid role selected.'
        ]);
        mysqli_stmt_close($role_stmt);
        exit;
    }
    mysqli_stmt_close($role_stmt);

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

    // Check if username already exists for other users
    $username_check = "SELECT user_id FROM tbl_user WHERE username = ? AND user_id != ?";
    $username_stmt = mysqli_prepare($conn, $username_check);
    mysqli_stmt_bind_param($username_stmt, "si", $username, $user_id);
    mysqli_stmt_execute($username_stmt);
    $username_result = mysqli_stmt_get_result($username_stmt);
    
    if (mysqli_num_rows($username_result) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username already exists. Please choose a different username.'
        ]);
        mysqli_stmt_close($username_stmt);
        exit;
    }
    mysqli_stmt_close($username_stmt);

    // Start transaction
    mysqli_begin_transaction($conn);

    if(!empty($password) && strlen($password) < 6){
        echo json_encode([
            'status' => 'error',
            'message' => 'Password must be at least 6 characters long.'
        ]);
        exit;
    }

    try {
        // Update tbl_user
        $user_query = "UPDATE tbl_user SET username = ?, status = ?, role_id = ? WHERE user_id = ?";
        $user_stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($user_stmt, "ssii", $username, $status, $role_id, $user_id);
        
        if (!mysqli_stmt_execute($user_stmt)) {
            throw new Exception("Failed to update user account.");
        }
        mysqli_stmt_close($user_stmt);

        // Update tbl_user_details
        $details_query = "UPDATE tbl_user_details SET full_name = ?, contact = ?, address = ? WHERE user_id = ?";
        $details_stmt = mysqli_prepare($conn, $details_query);
        mysqli_stmt_bind_param($details_stmt, "sssi", $full_name, $contact, $address, $user_id);
        
        if (!mysqli_stmt_execute($details_stmt)) {
            throw new Exception("Failed to update user details.");
        }
        mysqli_stmt_close($details_stmt);

        if(!empty($password)){
            // Update password if provided
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $password_query = "UPDATE tbl_user SET password = ? WHERE user_id = ?";
            $password_stmt = mysqli_prepare($conn, $password_query);
            mysqli_stmt_bind_param($password_stmt, "si", $password_hash, $user_id);
            
            if (!mysqli_stmt_execute($password_stmt)) {
                throw new Exception("Failed to update password.");
            }
            mysqli_stmt_close($password_stmt);
        }
        
        date_default_timezone_set('Asia/Manila');
        $current_date = date('Y-m-d H:i:s');
        audit_log($conn, $user_id_session, "Updated user with ID: " . $user_id, $current_date);

        // Commit transaction
        mysqli_commit($conn);

        echo json_encode([
            'status' => 'success',
            'message' => 'User updated successfully.'
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