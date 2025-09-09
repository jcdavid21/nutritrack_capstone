<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Get users who can administer vaccines (typically healthcare workers and admin)
    $sql = "SELECT u.user_id, ud.full_name
            FROM tbl_user u
            INNER JOIN tbl_user_details ud ON u.user_id = ud.user_id
            WHERE u.role_id IN (2, 3) -- Admin and Healthcare Worker roles
            ORDER BY ud.full_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'users' => $users
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch users: ' . $e->getMessage(),
        'users' => []
    ]);
}

$conn->close();
?>