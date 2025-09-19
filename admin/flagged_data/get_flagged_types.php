<?php
include "../../backend/config.php";

header('Content-Type: application/json');

try {
    $query = "SELECT ft.*, COUNT(fr.flagged_id) as usage_count 
              FROM tbl_flagged_type ft 
              LEFT JOIN tbl_flagged_record fr ON ft.ft_id = fr.issue_type 
              GROUP BY ft.ft_id 
              ORDER BY ft.flagged_name";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $types = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $types[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'types' => $types]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch types']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>