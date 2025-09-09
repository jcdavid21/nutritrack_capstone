<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    $flagged_id = isset($_GET['flagged_id']) ? intval($_GET['flagged_id']) : 0;
    
    if (!$flagged_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid flagged record ID'
        ]);
        exit;
    }
    
    $sql = "SELECT fr.flagged_id, fr.child_id, fr.issue_type, fr.date_flagged, fr.flagged_status,
                   fr.description, fr.resolution_notes, fr.resolution_date,
                   c.first_name, c.last_name, c.birthdate, c.gender,
                   b.zone_name
            FROM tbl_flagged_record fr
            INNER JOIN tbl_child c ON fr.child_id = c.child_id
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
            WHERE fr.flagged_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $flagged_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'success',
            'record' => $row
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Flagged record not found'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch flagged record: ' . $e->getMessage()
    ]);
}

$conn->close();
?>