<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../backend/config.php";

header('Content-Type: application/json');

try {
    // Check if connection exists
    if (!$conn) {
        throw new Exception("Database connection not available");
    }

    // Build the base query using correct table names from schema
    $sql = "SELECT COUNT(*) as total FROM tbl_nutrition_record nr 
            JOIN tbl_child c ON nr.child_id = c.child_id 
            WHERE 1=1";
    
    $conditions = [];
    $types = "";
    $params = [];
    
    // Zone filter
    if (!empty($_GET['zone_id']) && $_GET['zone_id'] !== '') {
        $conditions[] = "c.zone_id = ?";
        $types .= "i";
        $params[] = intval($_GET['zone_id']);
    }
    
    // Status filter
    if (!empty($_GET['status_id']) && $_GET['status_id'] !== '') {
        $conditions[] = "nr.status_id = ?";
        $types .= "i";
        $params[] = intval($_GET['status_id']);
    }
    
    // Gender filter
    if (!empty($_GET['gender']) && $_GET['gender'] !== '') {
        $conditions[] = "c.gender = ?";
        $types .= "s";
        $params[] = $_GET['gender'];
    }
    
    // Age range filter
    if (!empty($_GET['age_range']) && $_GET['age_range'] !== '') {
        $age_range = explode('-', $_GET['age_range']);
        if (count($age_range) == 2) {
            $min_age = intval($age_range[0]);
            $max_age = intval($age_range[1]);
            $conditions[] = "TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) BETWEEN ? AND ?";
            $types .= "ii";
            $params[] = $min_age;
            $params[] = $max_age;
        }
    }
    
    // Date range filters
    if (!empty($_GET['date_from']) && $_GET['date_from'] !== '') {
        $conditions[] = "nr.date_recorded >= ?";
        $types .= "s";
        $params[] = $_GET['date_from'];
    }
    
    if (!empty($_GET['date_to']) && $_GET['date_to'] !== '') {
        $conditions[] = "nr.date_recorded <= ?";
        $types .= "s";
        $params[] = $_GET['date_to'];
    }
    
    // Add conditions to query
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result()->fetch_assoc();
    
    $response = [
        'total' => intval($result['total']),
        'children_count' => 0,
        'date_range' => 'All dates'
    ];
    
    // Get additional info if there are results
    if ($response['total'] > 0) {
        // Get children count using correct table names
        $children_sql = "SELECT COUNT(DISTINCT c.child_id) as children_count 
                        FROM tbl_nutrition_record nr 
                        JOIN tbl_child c ON nr.child_id = c.child_id 
                        WHERE 1=1";
        
        if (!empty($conditions)) {
            $children_sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $children_stmt = $conn->prepare($children_sql);
        if (!empty($params)) {
            $children_stmt->bind_param($types, ...$params);
        }
        $children_stmt->execute();
        $children_result = $children_stmt->get_result()->fetch_assoc();
        $response['children_count'] = intval($children_result['children_count']);
        
        // Get zone name if filtered - using correct table name
        if (!empty($_GET['zone_id']) && $_GET['zone_id'] !== '') {
            $zone_sql = "SELECT zone_name FROM tbl_barangay WHERE zone_id = ?";
            $zone_stmt = $conn->prepare($zone_sql);
            if ($zone_stmt) {
                $zone_id = intval($_GET['zone_id']);
                $zone_stmt->bind_param("i", $zone_id);
                $zone_stmt->execute();
                $zone_result = $zone_stmt->get_result()->fetch_assoc();
                if ($zone_result) {
                    $response['zones'] = $zone_result['zone_name'];
                }
            }
        }
        
        // Get status name if filtered - using correct table name
        if (!empty($_GET['status_id']) && $_GET['status_id'] !== '') {
            $status_sql = "SELECT status_name FROM tbl_nutrition_status WHERE status_id = ?";
            $status_stmt = $conn->prepare($status_sql);
            if ($status_stmt) {
                $status_id = intval($_GET['status_id']);
                $status_stmt->bind_param("i", $status_id);
                $status_stmt->execute();
                $status_result = $status_stmt->get_result()->fetch_assoc();
                if ($status_result) {
                    $response['statuses'] = $status_result['status_name'];
                }
            }
        }
        
        // Get date range if records exist
        $date_range_sql = "SELECT MIN(nr.date_recorded) as min_date, MAX(nr.date_recorded) as max_date 
                          FROM tbl_nutrition_record nr 
                          JOIN tbl_child c ON nr.child_id = c.child_id 
                          WHERE 1=1";
        
        if (!empty($conditions)) {
            $date_range_sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $date_stmt = $conn->prepare($date_range_sql);
        if (!empty($params)) {
            $date_stmt->bind_param($types, ...$params);
        }
        $date_stmt->execute();
        $date_result = $date_stmt->get_result()->fetch_assoc();
        
        if ($date_result && $date_result['min_date'] && $date_result['max_date']) {
            $min_date = date('M j, Y', strtotime($date_result['min_date']));
            $max_date = date('M j, Y', strtotime($date_result['max_date']));
            
            if ($min_date === $max_date) {
                $response['date_range'] = $min_date;
            } else {
                $response['date_range'] = $min_date . ' - ' . $max_date;
            }
        }
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'total' => 0,
        'children_count' => 0
    ]);
}
?>