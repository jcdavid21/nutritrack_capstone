<?php
include "../../backend/config.php";

session_start();

// Check if user is logged in and has proper role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("HTTP/1.1 403 Forbidden");
    exit('Unauthorized access');
}

try {
    // Set headers for CSV download
    $filename = 'vaccine_records_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    $headers = [
        'Vaccine ID',
        'Child ID',
        'Child First Name',
        'Child Last Name',
        'Child Age',
        'Child Gender',
        'Zone',
        'Vaccine Name',
        'Vaccine Status',
        'Administered By',
        'Date Administered'
    ];
    
    fputcsv($output, $headers);
    
    // Get all vaccine records
    $sql = "SELECT v.vaccine_id, v.child_id, v.vaccine_name, v.vaccine_status, v.vaccine_date,
                   c.first_name, c.last_name, c.birthdate, c.gender,
                   b.zone_name,
                   COALESCE(ud.full_name, 'Unknown Administrator') as administered_by_name
            FROM tbl_vaccine_record v
            LEFT JOIN tbl_child c ON v.child_id = c.child_id
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
            LEFT JOIN tbl_user_details ud ON v.administered_by = ud.user_id
            ORDER BY v.vaccine_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Calculate age
        $age = 'N/A';
        if ($row['birthdate']) {
            $birthDate = new DateTime($row['birthdate']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
        }
        
        // Format date
        $formatted_date = 'N/A';
        if ($row['vaccine_date']) {
            $formatted_date = date('Y-m-d H:i:s', strtotime($row['vaccine_date']));
        }
        
        $csv_row = [
            '#VAC-' . str_pad($row['vaccine_id'], 4, '0', STR_PAD_LEFT),
            $row['child_id'],
            $row['first_name'] ?? 'Unknown',
            $row['last_name'] ?? 'Child',
            $age,
            $row['gender'] ?? 'Unknown',
            $row['zone_name'] ?? 'Unknown',
            $row['vaccine_name'] ?? 'Unknown',
            $row['vaccine_status'] ?? 'Unknown',
            $row['administered_by_name'] ?? 'Unknown',
            $formatted_date
        ];
        
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    exit('Error: ' . $e->getMessage());
}