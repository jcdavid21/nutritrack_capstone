<?php
include "../config.php";
include "../reports/audit_log.php";
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
    $password = trim($_POST['password'] ?? '');

    // Validate required fields
    if (empty($full_name) || empty($username) || empty($contact) || empty($address) || empty($status) || empty($role_id) || empty($password)) {
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

    // Check if username already exists
    $username_check = "SELECT user_id FROM tbl_user WHERE username = ?";
    $username_stmt = mysqli_prepare($conn, $username_check);
    mysqli_stmt_bind_param($username_stmt, "s", $username);
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

    try {
        // Insert into tbl_user
        $user_query = "INSERT INTO tbl_user (username, password, status, role_id, date_added) VALUES (?, ?, ?, ?, NOW())";
        $user_stmt = mysqli_prepare($conn, $user_query);
        
        $default_password = password_hash($password, PASSWORD_BCRYPT);
        
        mysqli_stmt_bind_param($user_stmt, "sssi", $username, $default_password, $status, $role_id);
        
        if (!mysqli_stmt_execute($user_stmt)) {
            throw new Exception("Failed to create user account.");
        }
        
        $user_id = mysqli_insert_id($conn);
        mysqli_stmt_close($user_stmt);

        // Insert into tbl_user_details
        $details_query = "INSERT INTO tbl_user_details (user_id, full_name, contact, address) VALUES (?, ?, ?, ?)";
        $details_stmt = mysqli_prepare($conn, $details_query);
        mysqli_stmt_bind_param($details_stmt, "isss", $user_id, $full_name, $contact, $address);
        
        if (!mysqli_stmt_execute($details_stmt)) {
            throw new Exception("Failed to save user details.");
        }
        mysqli_stmt_close($details_stmt);

        // Commit transaction
        mysqli_commit($conn);

        date_default_timezone_set('Asia/Manila');
        $current_date = date('Y-m-d H:i:s');

        audit_log($conn, $_SESSION['user_id'], "Created new user with ID: $user_id", $current_date);

        echo json_encode([
            'status' => 'success',
            'message' => 'User created successfully.',
            'user_id' => $user_id
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