<?php
include "../../config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$flagged_name = trim($_POST['flagged_name'] ?? '');

if (empty($flagged_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Issue type name is required']);
    exit;
}

try {
    // Check if type already exists
    $check_query = "SELECT ft_id FROM tbl_flagged_type WHERE flagged_name = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $flagged_name);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Issue type already exists']);
        exit;
    }
    
    // Insert new type
    $insert_query = "INSERT INTO tbl_flagged_type (flagged_name) VALUES (?)";
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "s", $flagged_name);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Issue type added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add issue type']);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>