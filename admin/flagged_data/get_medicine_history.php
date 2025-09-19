<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    $flagged_id = $_GET['flagged_id'] ?? null;
    
    if (!$flagged_id) {
        throw new Exception('Flagged ID is required');
    }
    
    $query = "SELECT 
            ml.log_id,
            ml.quantity_given,
            ml.dosage_instructions,
            ml.frequency,
            ml.duration,
            ml.date_administered,
            ml.notes,
            m.medicine_name,
            m.brand,
            m.generic_name,
            m.dosage_form,
            m.strength,
            m.unit,
            ud.full_name AS admin_full_name
            FROM tbl_medicine_log ml
            JOIN tbl_medicine m ON ml.medicine_id = m.medicine_id
            LEFT JOIN tbl_user u ON ml.administered_by = u.user_id
            LEFT JOIN tbl_user_details ud ON u.user_id = ud.user_id
            WHERE ml.flagged_id = ?
            ORDER BY ml.date_administered DESC;";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $flagged_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $medicines = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status' => 'success',
        'medicines' => $medicines
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching medicine history: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching medicine history'
    ]);
}