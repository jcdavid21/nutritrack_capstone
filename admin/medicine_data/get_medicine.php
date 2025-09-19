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
    $medicine_id = intval($_GET['medicine_id'] ?? 0);
    
    if ($medicine_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid medicine ID'
        ]);
        exit();
    }
    
    $sql = "SELECT * FROM tbl_medicine WHERE medicine_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $medicine_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $medicine = $result->fetch_assoc();

    if ($medicine) {
        echo json_encode([
            'success' => true,
            'medicine' => $medicine
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Medicine not found'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
}