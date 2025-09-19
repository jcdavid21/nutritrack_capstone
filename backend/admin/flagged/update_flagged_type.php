<?php
include "../../config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$ft_id = intval($_POST['ft_id'] ?? 0);
$flagged_name = trim($_POST['flagged_name'] ?? '');

if ($ft_id <= 0 || empty($flagged_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

try {
    // Check if type exists
    $check_query = "SELECT ft_id FROM tbl_flagged_type WHERE ft_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $ft_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Issue type not found']);
        exit;
    }
    
    // Check if name already exists for different ID
    $name_check_query = "SELECT ft_id FROM tbl_flagged_type WHERE flagged_name = ? AND ft_id != ?";
    $name_check_stmt = mysqli_prepare($conn, $name_check_query);
    mysqli_stmt_bind_param($name_check_stmt, "si", $flagged_name, $ft_id);
    mysqli_stmt_execute($name_check_stmt);
    $name_check_result = mysqli_stmt_get_result($name_check_stmt);
    
    if (mysqli_num_rows($name_check_result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Issue type name already exists']);
        exit;
    }
    
    // Update type
    $update_query = "UPDATE tbl_flagged_type SET flagged_name = ? WHERE ft_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "si", $flagged_name, $ft_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Issue type updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update issue type']);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>