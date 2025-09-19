<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    if (!isset($_GET['flagged_type']) || empty($_GET['flagged_type'])) {
        throw new Exception('Flagged type parameter is required');
    }
    
    $flaggedType = $_GET['flagged_type'];
    
    // Build WHERE clause based on filters
    $whereConditions = [];
    $params = [];
    
    if ($flaggedType === 'No Flagged Record') {
        $whereConditions[] = 'ml.flagged_id IS NULL';
    } else {
        $whereConditions[] = 'ft.flagged_name = ?';
        $params[] = $flaggedType;
    }
    
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $whereConditions[] = 'ml.date_administered >= ?';
        $params[] = $_GET['start_date'] . ' 00:00:00';
    }
    
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $whereConditions[] = 'ml.date_administered <= ?';
        $params[] = $_GET['end_date'] . ' 23:59:59';
    }
    
    if (isset($_GET['medicine']) && !empty($_GET['medicine'])) {
        $whereConditions[] = 'ml.medicine_id = ?';
        $params[] = $_GET['medicine'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $sql = "SELECT 
                ml.log_id,
                ml.quantity_given,
                ml.date_administered,
                ml.notes,
                CONCAT(c.first_name, ' ', c.last_name) as child_name,
                m.medicine_name,
                m.unit,
                COALESCE(ft.flagged_name, 'No Flagged Record') as flagged_type,
                ud.full_name as administered_by_name
            FROM tbl_medicine_log ml
            JOIN tbl_child c ON ml.child_id = c.child_id
            JOIN tbl_medicine m ON ml.medicine_id = m.medicine_id
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            LEFT JOIN tbl_flagged_type ft ON fr.issue_type = ft.ft_id
            LEFT JOIN tbl_user u ON ml.administered_by = u.user_id
            LEFT JOIN tbl_user_details ud ON u.user_id = ud.user_id
            WHERE {$whereClause}
            ORDER BY ml.date_administered DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $details = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'details' => $details
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => []
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'details' => []
    ]);
}