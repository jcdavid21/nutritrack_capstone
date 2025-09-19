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
    $stats = [];
    
    // Total medicines
    $totalSql = "SELECT COUNT(*) as total FROM tbl_medicine WHERE status IN ('Active', 'Inactive')";
    $totalStmt = $conn->query($totalSql);
    $stats['total_medicines'] = $totalStmt->fetch_assoc()['total'];
    
    // Low stock count
    $lowStockSql = "SELECT COUNT(*) as low_stock FROM tbl_medicine 
                    WHERE stock_quantity <= minimum_stock AND stock_quantity > 0 
                    AND status IN ('Active', 'Inactive')";
    $lowStockStmt = $conn->query($lowStockSql);
    $stats['low_stock'] = $lowStockStmt->fetch_assoc()['low_stock'];
    
    // Expired count
    $expiredSql = "SELECT COUNT(*) as expired FROM tbl_medicine 
                   WHERE (expiry_date < CURDATE() OR status = 'Expired')";
    $expiredStmt = $conn->query($expiredSql);
    $stats['expired'] = $expiredStmt->fetch_assoc()['expired'];
    
    // Total value
    $valueSql = "SELECT SUM(stock_quantity * COALESCE(unit_cost, 0)) as total_value 
                 FROM tbl_medicine WHERE status IN ('Active', 'Inactive')";
    $valueStmt = $conn->query($valueSql);
    $totalValue = $valueStmt->fetch_assoc()['total_value'];
    $stats['total_value'] = floatval($totalValue ?? 0);
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
}