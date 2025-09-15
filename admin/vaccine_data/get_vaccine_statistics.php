<?php
header('Content-Type: application/json');
include "../../backend/config.php";

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $vaccine_type = isset($_GET['vaccine_type']) ? trim($_GET['vaccine_type']) : '';
    $zone = isset($_GET['zone']) ? trim($_GET['zone']) : '';

    $conditions = [];
    $params = [];
    $types = '';

    if ($search) {
        $conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR tv.vaccine_name LIKE ?)";
        $search_param = "{$search}%";
        $params = [$search_param, $search_param, $search_param];
        $types .= 'sss';
    }

    if ($status) {
        $conditions[] = "tv.vaccine_status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if ($vaccine_type) {
        $conditions[] = "tv.vaccine_name = ?";
        $params[] = $vaccine_type;
        $types .= 's';
    }

    if ($zone) {
        $conditions[] = "c.zone_id = ?";
        $params[] = $zone;
        $types .= 'i';
    }

try {
    // Get overall statistics
    $stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN tv.vaccine_status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN tv.vaccine_status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing,
                SUM(CASE WHEN tv.vaccine_status = 'Incomplete' THEN 1 ELSE 0 END) as incomplete,
                SUM(CASE WHEN tv.vaccine_name LIKE '%BCG%' THEN 1 ELSE 0 END) as bcg,
                SUM(CASE WHEN tv.vaccine_name LIKE '%Hepatitis%' THEN 1 ELSE 0 END) as hepatitis,
                SUM(CASE WHEN tv.vaccine_name LIKE '%DPT%' THEN 1 ELSE 0 END) as dpt,
                SUM(CASE WHEN tv.vaccine_name LIKE '%Polio%' THEN 1 ELSE 0 END) as polio,
                SUM(CASE WHEN tv.vaccine_name LIKE '%MMR%' THEN 1 ELSE 0 END) as mmr
              FROM tbl_vaccine_record tv 
              INNER JOIN tbl_child c ON tv.child_id = c.child_id" . ($conditions ? " WHERE " . implode(" AND ", $conditions) : "");

    
    $stmt = $conn->prepare($stats_sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    
    // Ensure all values are integers
    foreach ($stats as $key => $value) {
        $stats[$key] = intval($value);
    }
    
    echo json_encode([
        'status' => 'success',
        'total' => $stats['total'],
        'completed' => $stats['completed'],
        'ongoing' => $stats['ongoing'],
        'incomplete' => $stats['incomplete'],
        'bcg' => $stats['bcg'],
        'hepatitis' => $stats['hepatitis'],
        'dpt' => $stats['dpt'],
        'polio' => $stats['polio'],
        'mmr' => $stats['mmr']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch statistics: ' . $e->getMessage(),
        'total' => 0,
        'completed' => 0,
        'ongoing' => 0,
        'incomplete' => 0,
        'bcg' => 0,
        'hepatitis' => 0,
        'dpt' => 0,
        'polio' => 0,
        'mmr' => 0
    ]);
}

$conn->close();
?>