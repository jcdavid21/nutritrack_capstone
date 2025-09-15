<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $zone = isset($_GET['zone']) ? trim($_GET['zone']) : '';
    $gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
    
    $offset = ($page - 1) * $limit;
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ?)";
        $search_param = "{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($zone)) {
        $where_conditions[] = "c.zone_id = ?";
        $params[] = $zone;
    }
    
    if (!empty($gender)) {
        $where_conditions[] = "c.gender = ?";
        $params[] = $gender;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Count total
    $count_sql = "SELECT COUNT(*) as total FROM tbl_child c LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id $where_clause";
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    
    // Get records
    $sql = "SELECT c.child_id, c.first_name, c.last_name, c.birthdate, c.gender, c.created_at, b.zone_name 
            FROM tbl_child c 
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id 
            $where_clause 
            ORDER BY c.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $all_params = array_merge($params, [$limit, $offset]);
    $types = str_repeat('s', count($params)) . 'ii';
    $stmt->bind_param($types, ...$all_params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'records' => $records,
        'total' => $total,
        'page' => $page,
        'total_pages' => ceil($total / $limit)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>