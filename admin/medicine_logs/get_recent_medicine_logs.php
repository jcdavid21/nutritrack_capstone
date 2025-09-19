<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    // Build WHERE clause based on filters
    $whereConditions = ['1=1'];
    $params = [];
    $paramTypes = '';
    
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $whereConditions[] = 'ml.date_administered >= ?';
        $params[] = $_GET['start_date'] . ' 00:00:00';
        $paramTypes .= 's';
    }
    
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $whereConditions[] = 'ml.date_administered <= ?';
        $params[] = $_GET['end_date'] . ' 23:59:59';
        $paramTypes .= 's';
    }
    
    if (isset($_GET['flagged_type']) && !empty($_GET['flagged_type'])) {
        $whereConditions[] = 'fr.issue_type = ?';
        $params[] = $_GET['flagged_type'];
        $paramTypes .= 'i';
    }
    
    if (isset($_GET['medicine']) && !empty($_GET['medicine'])) {
        $whereConditions[] = 'ml.medicine_id = ?';
        $params[] = $_GET['medicine'];
        $paramTypes .= 'i';
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(ml.log_id) as total 
                FROM tbl_medicine_log ml
                LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
                WHERE {$whereClause}";
    $stmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    
    // Get paginated logs
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    $sql = "SELECT 
                ml.log_id,
                ml.child_id,
                ml.quantity_given,
                ml.frequency,
                ml.duration,
                ml.date_administered,
                ml.notes,
                ml.dosage_instructions,
                CONCAT(c.first_name, ' ', c.last_name) as child_name,
                m.medicine_name,
                m.brand,
                m.generic_name,
                m.unit,
                ft.flagged_name as flagged_type,
                ud.full_name as administered_by_name,
                r.role_name as administered_by_role
            FROM tbl_medicine_log ml
            JOIN tbl_child c ON ml.child_id = c.child_id
            JOIN tbl_medicine m ON ml.medicine_id = m.medicine_id
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            LEFT JOIN tbl_flagged_type ft ON fr.issue_type = ft.ft_id
            LEFT JOIN tbl_user u ON ml.administered_by = u.user_id
            LEFT JOIN tbl_user_details ud ON u.user_id = ud.user_id
            LEFT JOIN tbl_roles r ON u.role_id = r.role_id
            WHERE {$whereClause}
            ORDER BY ml.date_administered DESC
            LIMIT {$limit} OFFSET {$offset}";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'total' => (int)$total
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'logs' => [],
        'total' => 0
    ]);
}
?>