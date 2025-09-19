<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    $medicine_id = $_GET['medicine_id'] ?? null;
    
    if (!$medicine_id) {
        throw new Exception('Medicine ID is required');
    }
    
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
              WHERE medicine_id = ? AND status = 'Active'";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $medicine_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $medicine = $result->fetch_assoc();

    if ($medicine) {
        echo json_encode([
            'status' => 'success',
            'medicine' => $medicine
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Medicine not found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error fetching medicine details: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching medicine details'
    ]);
}