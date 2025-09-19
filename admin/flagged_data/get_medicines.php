<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    $query = "SELECT 
                medicine_id, 
                medicine_name, 
                brand, 
                generic_name, 
                dosage_form, 
                strength, 
                unit,
                stock_quantity,
                status 
              FROM tbl_medicine 
              WHERE status = 'Active' 
              ORDER BY medicine_name ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $medicines = [];
    while ($row = $result->fetch_assoc()) {
        $medicines[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'medicines' => $medicines
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching medicines: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching medicines'
    ]);
}