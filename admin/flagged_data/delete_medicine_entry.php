<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $log_id = $input['log_id'] ?? null;
    
    if (!$log_id) {
        throw new Exception('Medicine log ID is required');
    }
    
    // Get medicine details before deletion for stock restoration
    $getMedicineQuery = "SELECT ml.medicine_id, ml.quantity_given 
                        FROM tbl_medicine_log ml 
                        WHERE ml.log_id = ?";
    $getMedicineStmt = $conn->prepare($getMedicineQuery);
    $getMedicineStmt->bind_param('i', $log_id);
    $getMedicineStmt->execute();
    $result = $getMedicineStmt->get_result();
    $medicineData = $result->fetch_assoc();
    
    if (!$medicineData) {
        throw new Exception('Medicine log entry not found');
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete the medicine log entry
        $deleteQuery = "DELETE FROM tbl_medicine_log WHERE log_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param('i', $log_id);
        $deleteStmt->execute();

        // Restore stock quantity (add back the quantity that was administered)
        $updateStockQuery = "UPDATE tbl_medicine 
                           SET stock_quantity = stock_quantity + ? 
                           WHERE medicine_id = ?";
        $updateStockStmt = $conn->prepare($updateStockQuery);
        $updateStockStmt->bind_param('ii', $medicineData['quantity_given'], $medicineData['medicine_id']);
        $updateStockStmt->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Medicine entry deleted successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error deleting medicine entry: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error deleting medicine entry: ' . $e->getMessage()
    ]);
}