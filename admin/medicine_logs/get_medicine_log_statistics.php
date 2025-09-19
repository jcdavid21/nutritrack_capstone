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
    
    // Total logs count
    $sql = "SELECT COUNT(ml.log_id) as total_logs 
            FROM tbl_medicine_log ml 
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            WHERE {$whereClause}";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $totalLogs = $result->fetch_assoc()['total_logs'];

    // Unique children count
    $sql = "SELECT COUNT(DISTINCT ml.child_id) as unique_children 
            FROM tbl_medicine_log ml 
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            WHERE {$whereClause}";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $uniqueChildren = $result->fetch_assoc()['unique_children'];

    // Unique medicines count
    $sql = "SELECT COUNT(DISTINCT ml.medicine_id) as unique_medicines 
            FROM tbl_medicine_log ml 
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            WHERE {$whereClause}";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $uniqueMedicines = $result->fetch_assoc()['unique_medicines'];

    // This month count
    $thisMonthConditions = $whereConditions;
    $thisMonthConditions[] = 'YEAR(ml.date_administered) = YEAR(CURDATE())';
    $thisMonthConditions[] = 'MONTH(ml.date_administered) = MONTH(CURDATE())';
    $thisMonthWhereClause = implode(' AND ', $thisMonthConditions);
    
    $sql = "SELECT COUNT(ml.log_id) as this_month 
            FROM tbl_medicine_log ml 
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            WHERE {$thisMonthWhereClause}";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $thisMonth = $result->fetch_assoc()['this_month'];

    echo json_encode([
        'success' => true,
        'total_logs' => (int)$totalLogs,
        'unique_children' => (int)$uniqueChildren,
        'unique_medicines' => (int)$uniqueMedicines,
        'this_month' => (int)$thisMonth
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'total_logs' => 0,
        'unique_children' => 0,
        'unique_medicines' => 0,
        'this_month' => 0
    ]);
}
?>