<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Get parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $report_type = isset($_GET['report_type']) ? trim($_GET['report_type']) : '';
    $zone = isset($_GET['zone']) ? trim($_GET['zone']) : '';
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    if (!empty($search)) {
        $where_conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR r.report_type LIKE ? OR ud.full_name LIKE ?)";
        $search_param = "{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= 'ssss';
    }
    
    if (!empty($report_type)) {
        $where_conditions[] = "r.report_type = ?";
        $params[] = $report_type;
        $param_types .= 's';
    }

    if (!empty($zone)) {
        $where_conditions[] = "c.zone_id = ?";
        $params[] = $zone;
        $param_types .= 'i';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Count total records
    $count_sql = "SELECT COUNT(*) as total 
                  FROM tbl_report r
                  LEFT JOIN tbl_child c ON r.child_id = c.child_id
                  LEFT JOIN tbl_user_details ud ON r.generated_by = ud.user_id
                  $where_clause";
    
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($param_types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    
    // Get paginated records
    $sql = "SELECT r.report_id, r.child_id, r.generated_by, r.report_type, r.report_date,
                   c.first_name, c.last_name,
                   COALESCE(ud.full_name, 'System') as generated_by_name
            FROM tbl_report r
            LEFT JOIN tbl_child c ON r.child_id = c.child_id
            LEFT JOIN tbl_user_details ud ON r.generated_by = ud.user_id
            $where_clause
            ORDER BY r.report_date DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters including limit and offset
    $all_params = array_merge($params, [$limit, $offset]);
    $all_param_types = $param_types . 'ii';
    
    $stmt->bind_param($all_param_types, ...$all_params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        // Ensure all required fields exist
        $row['first_name'] = $row['first_name'] ?? 'Unknown';
        $row['last_name'] = $row['last_name'] ?? 'Child';
        $row['generated_by_name'] = $row['generated_by_name'] ?? 'System';
        $row['report_type'] = $row['report_type'] ?? 'General';
        
        // Format date if needed
        if ($row['report_date']) {
            $row['report_date'] = date('Y-m-d H:i:s', strtotime($row['report_date']));
        }
        
        $reports[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'reports' => $reports,
        'total' => intval($total),
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch reports: ' . $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine()
    ]);
}

$conn->close();
?>