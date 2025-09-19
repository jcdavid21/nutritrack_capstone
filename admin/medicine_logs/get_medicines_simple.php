<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    $sql = "SELECT medicine_id, medicine_name, brand, generic_name, dosage_form, strength, unit 
            FROM tbl_medicine 
            WHERE status = 'Active' 
            ORDER BY medicine_name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $medicines = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'medicines' => $medicines
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}