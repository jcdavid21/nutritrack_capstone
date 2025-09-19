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
    // Get medicines that are expired or expiring within 90 days
    $sql = "SELECT 
                medicine_id,
                medicine_name,
                brand,
                generic_name,
                stock_quantity,
                unit,
                expiry_date,
                DATEDIFF(expiry_date, CURDATE()) as days_until_expiry
            FROM tbl_medicine 
            WHERE expiry_date IS NOT NULL 
                AND (expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) OR expiry_date < CURDATE())
                AND status IN ('Active', 'Inactive')
                AND stock_quantity > 0
            ORDER BY 
                CASE 
                    WHEN expiry_date < CURDATE() THEN 0 
                    ELSE 1 
                END,
                expiry_date ASC
            LIMIT 20";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $alerts = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'alerts' => $alerts
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
}