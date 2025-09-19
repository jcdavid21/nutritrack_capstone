<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $sql = "SELECT 
                fr.flagged_id,
                CONCAT(c.first_name, ' ', c.last_name) as child_name,
                ft.flagged_name as issue_type,
                fr.flagged_status
            FROM tbl_flagged_record fr
            JOIN tbl_child c ON fr.child_id = c.child_id
            JOIN tbl_flagged_type ft ON fr.issue_type = ft.ft_id
            WHERE fr.flagged_status IN ('Active', 'Under Review')
            ORDER BY fr.date_flagged DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $flagged = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'flagged' => $flagged
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}