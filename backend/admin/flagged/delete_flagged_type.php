<?php
include "../../config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$ft_id = intval($_POST['ft_id'] ?? 0);

if ($ft_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid type ID']);
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
    
    $check_if_this_type_used_query = "SELECT flagged_id FROM tbl_flagged_record WHERE issue_type = ?";
    $check_if_this_type_used_stmt = mysqli_prepare($conn, $check_if_this_type_used_query);
    mysqli_stmt_bind_param($check_if_this_type_used_stmt, "i", $ft_id);
    mysqli_stmt_execute($check_if_this_type_used_stmt);
    $check_if_this_type_used_result = mysqli_stmt_get_result($check_if_this_type_used_stmt);

    if (mysqli_num_rows($check_if_this_type_used_result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Issue type is in use and cannot be deleted']);
        exit;
    }

    $delete_query = "DELETE FROM tbl_flagged_type WHERE ft_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $ft_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Issue type deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete issue type']);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>