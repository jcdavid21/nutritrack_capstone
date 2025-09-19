<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    $sql = "SELECT 
                c.child_id, 
                c.first_name, 
                c.last_name,
                c.birthdate,
                c.gender,
                TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) as age
            FROM tbl_child c
            ORDER BY c.first_name ASC, c.last_name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $children = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'children' => $children
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}