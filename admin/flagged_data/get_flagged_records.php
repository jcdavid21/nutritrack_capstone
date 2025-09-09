<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Get parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $issue = isset($_GET['issue']) ? trim($_GET['issue']) : '';
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR fr.issue_type LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($status)) {
        $where_conditions[] = "fr.flagged_status = ?";
        $params[] = $status;
    }
    
    if (!empty($issue)) {
        $where_conditions[] = "fr.issue_type = ?";
        $params[] = $issue;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Count total records
    $count_sql = "SELECT COUNT(*) as total 
                  FROM tbl_flagged_record fr
                  INNER JOIN tbl_child c ON fr.child_id = c.child_id
                  LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
                  $where_clause";
    
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    
    // Get paginated records
    $sql = "SELECT fr.flagged_id, fr.child_id, fr.issue_type, fr.date_flagged, fr.flagged_status,
                   fr.description, fr.resolution_notes, fr.resolution_date,
                   c.first_name, c.last_name, c.birthdate, c.gender,
                   b.zone_name
            FROM tbl_flagged_record fr
            INNER JOIN tbl_child c ON fr.child_id = c.child_id
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
            $where_clause
            ORDER BY fr.date_flagged DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters including limit and offset
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
        'limit' => $limit
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch flagged records: ' . $e->getMessage()
    ]);
}

$conn->close();
?>