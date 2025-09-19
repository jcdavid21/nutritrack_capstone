<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    // Build WHERE clause based on filters
    $whereConditions = ['1=1'];
    $params = [];
    
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $whereConditions[] = 'ml.date_administered >= ?';
        $params[] = $_GET['start_date'] . ' 00:00:00';
    }
    
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $whereConditions[] = 'ml.date_administered <= ?';
        $params[] = $_GET['end_date'] . ' 23:59:59';
    }
    
    if (isset($_GET['flagged_type']) && !empty($_GET['flagged_type'])) {
        $whereConditions[] = 'fr.issue_type = ?';
        $params[] = $_GET['flagged_type'];
    }
    
    if (isset($_GET['medicine']) && !empty($_GET['medicine'])) {
        $whereConditions[] = 'ml.medicine_id = ?';
        $params[] = $_GET['medicine'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $sql = "SELECT 
                DATE(ml.date_administered) as administration_date,
                COUNT(ml.log_id) as count
            FROM tbl_medicine_log ml
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            WHERE {$whereClause}
            GROUP BY DATE(ml.date_administered)
            ORDER BY administration_date ASC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $labels = [];
    $values = [];
    
    foreach ($results as $row) {
        $labels[] = date('M j', strtotime($row['administration_date']));
        $values[] = (int)$row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'labels' => [],
        'values' => []
    ]);
}