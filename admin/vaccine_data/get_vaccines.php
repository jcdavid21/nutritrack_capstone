<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Get parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $vaccine_type = isset($_GET['vaccine_type']) ? trim($_GET['vaccine_type']) : '';
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    if (!empty($search)) {
        $where_conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR v.vaccine_name LIKE ? OR ud.full_name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= 'ssss';
    }
    
    if (!empty($status)) {
        $where_conditions[] = "v.vaccine_status = ?";
        $params[] = $status;
        $param_types .= 's';
    }
    
    if (!empty($vaccine_type)) {
        $where_conditions[] = "v.vaccine_name = ?";
        $params[] = $vaccine_type;
        $param_types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Count total records
    $count_sql = "SELECT COUNT(*) as total 
                  FROM tbl_vaccine_record v
                  LEFT JOIN tbl_child c ON v.child_id = c.child_id
                  LEFT JOIN tbl_user_details ud ON v.administered_by = ud.user_id
                  $where_clause";
    
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($param_types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    
    // Get paginated records
    $sql = "SELECT v.vaccine_id, v.child_id, v.administered_by, v.vaccine_name, 
                   v.vaccine_status, v.vaccine_date,
                   c.first_name, c.last_name, c.birthdate, c.gender,
                   b.zone_name,
                   COALESCE(ud.full_name, 'Unknown Administrator') as administered_by_name
            FROM tbl_vaccine_record v
            LEFT JOIN tbl_child c ON v.child_id = c.child_id
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
            LEFT JOIN tbl_user_details ud ON v.administered_by = ud.user_id
            $where_clause
            ORDER BY v.vaccine_date DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters including limit and offset
    $all_params = array_merge($params, [$limit, $offset]);
    $all_param_types = $param_types . 'ii';
    
    $stmt->bind_param($all_param_types, ...$all_params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $vaccines = [];
    while ($row = $result->fetch_assoc()) {
        // Ensure all required fields exist
        $row['first_name'] = $row['first_name'] ?? 'Unknown';
        $row['last_name'] = $row['last_name'] ?? 'Child';
        $row['administered_by_name'] = $row['administered_by_name'] ?? 'Unknown Administrator';
        $row['vaccine_name'] = $row['vaccine_name'] ?? 'Unknown Vaccine';
        $row['vaccine_status'] = $row['vaccine_status'] ?? 'Ongoing';
        
        // Format date if needed
        if ($row['vaccine_date']) {
            $row['vaccine_date'] = date('Y-m-d H:i:s', strtotime($row['vaccine_date']));
        }
        
        $vaccines[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'vaccines' => $vaccines,
        'total' => intval($total),
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch vaccines: ' . $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine()
    ]);
}

$conn->close();
?>