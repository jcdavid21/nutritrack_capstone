<?php
include "../../config.php";
session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION["role_id"] != 2) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $medicine_id = intval($_POST['medicine_id'] ?? 0);
    
    if ($medicine_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid medicine ID'
        ]);
        exit();
    }
    
    // Check if medicine exists
    $checkSql = "SELECT medicine_name FROM tbl_medicine WHERE medicine_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('i', $medicine_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $medicine = $result->fetch_assoc();
    
    if (!$medicine) {
        echo json_encode([
            'success' => false,
            'message' => 'Medicine not found'
        ]);
        exit();
    }
    
    // Check if medicine is referenced in medicine logs
    $logSql = "SELECT COUNT(*) as count FROM tbl_medicine_log WHERE medicine_id = ?";
    $logStmt = $conn->prepare($logSql);
    $logStmt->bind_param('i', $medicine_id);
    $logStmt->execute();
    $result = $logStmt->get_result();
    $logCount = $result->fetch_assoc()['count'];
    
    if ($logCount > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete medicine that has administration records. Consider marking it as inactive instead.'
        ]);
        exit();
    }
    
    // Delete the medicine
    $deleteSql = "DELETE FROM tbl_medicine WHERE medicine_id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param('i', $medicine_id);
    $result = $deleteStmt->execute();

    if ($result && $deleteStmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Medicine deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete medicine');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
}