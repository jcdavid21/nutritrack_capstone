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

    // Build the base query using vaccine records table
    $sql = "SELECT COUNT(*) as total FROM tbl_vaccine_record vr 
            JOIN tbl_child c ON vr.child_id = c.child_id 
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
    
    // Vaccine status filter
    if (!empty($_GET['vaccine_status']) && $_GET['vaccine_status'] !== '') {
        $conditions[] = "vr.vaccine_status = ?";
        $types .= "s";
        $params[] = $_GET['vaccine_status'];
    }
    
    // Vaccine name filter
    if (!empty($_GET['vaccine_name']) && $_GET['vaccine_name'] !== '') {
        $conditions[] = "vr.vaccine_name = ?";
        $types .= "s";
        $params[] = $_GET['vaccine_name'];
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
        $conditions[] = "vr.vaccine_date >= ?";
        $types .= "s";
        $params[] = $_GET['date_from'];
    }
    
    if (!empty($_GET['date_to']) && $_GET['date_to'] !== '') {
        $conditions[] = "vr.vaccine_date <= ?";
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
        // Get children count with vaccine records
        $children_sql = "SELECT COUNT(DISTINCT c.child_id) as children_count 
                        FROM tbl_vaccine_record vr 
                        JOIN tbl_child c ON vr.child_id = c.child_id 
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
        
        // Get zone name if filtered
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
        
        // Get vaccine status info if filtered
        if (!empty($_GET['vaccine_status']) && $_GET['vaccine_status'] !== '') {
            $response['vaccine_status'] = $_GET['vaccine_status'];
        }
        
        // Get vaccine name info if filtered
        if (!empty($_GET['vaccine_name']) && $_GET['vaccine_name'] !== '') {
            $response['vaccine_name'] = $_GET['vaccine_name'];
        }
        
        // Get date range from vaccine records
        $date_range_sql = "SELECT MIN(vr.vaccine_date) as min_date, MAX(vr.vaccine_date) as max_date 
                          FROM tbl_vaccine_record vr 
                          JOIN tbl_child c ON vr.child_id = c.child_id 
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