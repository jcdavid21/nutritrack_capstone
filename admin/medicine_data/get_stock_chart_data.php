<?php
include "../../backend/config.php";
session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION["role_id"] != 2) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get stock distribution data
    $data = [
        'normal' => 0,
        'low' => 0,
        'out_of_stock' => 0
    ];
    
    // Out of stock count
    $outOfStockSql = "SELECT COUNT(*) as count FROM tbl_medicine 
                      WHERE stock_quantity <= 0 AND status IN ('Active', 'Inactive')";
    $outOfStockStmt = $conn->prepare($outOfStockSql);
    $outOfStockStmt->execute();
    $result = $outOfStockStmt->get_result();
    $data['out_of_stock'] = $result->fetch_assoc()['count'];
    
    // Low stock count (above 0 but at or below minimum)
    $lowStockSql = "SELECT COUNT(*) as count FROM tbl_medicine 
                    WHERE stock_quantity > 0 AND stock_quantity <= minimum_stock 
                    AND status IN ('Active', 'Inactive')";
    $lowStockStmt = $conn->prepare($lowStockSql);
    $lowStockStmt->execute();
    $result = $lowStockStmt->get_result();
    $data['low'] = $result->fetch_assoc()['count'];

    // Normal stock count (above minimum)
    $normalStockSql = "SELECT COUNT(*) as count FROM tbl_medicine 
                       WHERE stock_quantity > minimum_stock 
                       AND status IN ('Active', 'Inactive')";
    $normalStockStmt = $conn->prepare($normalStockSql);
    $normalStockStmt->execute();
    $result = $normalStockStmt->get_result();
    $data['normal'] = $result->fetch_assoc()['count'];

    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
}