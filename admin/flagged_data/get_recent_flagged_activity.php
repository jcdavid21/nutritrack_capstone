<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    $sql = "SELECT fr.flagged_id, fr.issue_type, fr.date_flagged, fr.flagged_status,
                   c.first_name, c.last_name, c.child_id, ft.flagged_name, ft.ft_id
            FROM tbl_flagged_record fr
            INNER JOIN tbl_child c ON fr.child_id = c.child_id
            LEFT JOIN tbl_flagged_type ft ON fr.issue_type = ft.ft_id
            ORDER BY fr.date_flagged DESC
            LIMIT 10";
    
    $result = $conn->query($sql);
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'activities' => $activities
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch recent activity: ' . $e->getMessage()
    ]);
}

$conn->close();
?>