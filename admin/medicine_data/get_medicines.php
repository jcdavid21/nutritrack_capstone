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
    // Get parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(100, intval($_GET['limit'] ?? 10)));
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $stock = trim($_GET['stock'] ?? '');
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(medicine_name LIKE ? OR brand LIKE ? OR generic_name LIKE ?)";
        $searchParam = "{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    // Stock filter logic
    if (!empty($stock)) {
        switch ($stock) {
            case 'normal':
                $whereConditions[] = "stock_quantity > minimum_stock * 1.5";
                break;
            case 'low':
                $whereConditions[] = "stock_quantity <= minimum_stock AND stock_quantity > 0";
                break;
            case 'critical':
                $whereConditions[] = "stock_quantity <= 0";
                break;
        }
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = "WHERE " . implode(" AND ", $whereConditions);
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM tbl_medicine $whereClause";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRecords = $countResult->fetch_assoc()['total'];
    
    // Get medicines with pagination
    $sql = "SELECT * FROM tbl_medicine $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $medicines = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'medicines' => $medicines,
        'total' => $totalRecords,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($totalRecords / $limit)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
}