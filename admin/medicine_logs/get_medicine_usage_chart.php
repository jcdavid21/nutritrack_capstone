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
    
    $sql = "SELECT 
                m.medicine_name,
                COUNT(ml.log_id) as usage_count
            FROM tbl_medicine_log ml
            JOIN tbl_medicine m ON ml.medicine_id = m.medicine_id
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            WHERE {$whereClause}
            GROUP BY ml.medicine_id, m.medicine_name
            ORDER BY usage_count DESC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $labels = [];
    $values = [];
    
    foreach ($results as $row) {
        $labels[] = $row['medicine_name'];
        $values[] = (int)$row['usage_count'];
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'labels' => [],
        'values' => []
    ]);
}
?>