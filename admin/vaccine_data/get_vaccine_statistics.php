<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    // Get overall statistics
    $stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN vaccine_status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN vaccine_status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing,
                SUM(CASE WHEN vaccine_status = 'Incomplete' THEN 1 ELSE 0 END) as incomplete,
                SUM(CASE WHEN vaccine_name LIKE '%BCG%' THEN 1 ELSE 0 END) as bcg,
                SUM(CASE WHEN vaccine_name LIKE '%Hepatitis%' THEN 1 ELSE 0 END) as hepatitis,
                SUM(CASE WHEN vaccine_name LIKE '%DPT%' THEN 1 ELSE 0 END) as dpt,
                SUM(CASE WHEN vaccine_name LIKE '%Polio%' THEN 1 ELSE 0 END) as polio,
                SUM(CASE WHEN vaccine_name LIKE '%MMR%' THEN 1 ELSE 0 END) as mmr
              FROM tbl_vaccine_record";

    
    $stmt = $conn->prepare($stats_sql);
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