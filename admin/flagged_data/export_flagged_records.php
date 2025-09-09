<?php
include "../../backend/config.php";

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="flagged_records_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $sql = "SELECT fr.flagged_id, 
                   CONCAT(c.first_name, ' ', c.last_name) as child_name,
                   c.child_id,
                   TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) as age,
                   c.gender,
                   b.zone_name,
                   fr.issue_type,
                   fr.flagged_status,
                   DATE_FORMAT(fr.date_flagged, '%Y-%m-%d %H:%i:%s') as date_flagged,
                   DATEDIFF(CURDATE(), DATE(fr.date_flagged)) as days_open,
                   fr.description,
                   fr.resolution_notes,
                   DATE_FORMAT(fr.resolution_date, '%Y-%m-%d %H:%i:%s') as resolution_date
            FROM tbl_flagged_record fr
            INNER JOIN tbl_child c ON fr.child_id = c.child_id
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
            ORDER BY fr.date_flagged DESC";
    
    $result = $conn->query($sql);
    
    // Create file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    $headers = [
        'Flagged ID',
        'Child Name', 
        'Child ID',
        'Age',
        'Gender',
        'Zone',
        'Issue Type',
        'Status',
        'Date Flagged',
        'Days Open',
        'Description',
        'Resolution Notes',
        'Resolution Date'
    ];
    fputcsv($output, $headers);
    
    // Add data rows
    while ($row = $result->fetch_assoc()) {
        $csv_row = [
            $row['flagged_id'],
            $row['child_name'],
            $row['child_id'],
            $row['age'],
            $row['gender'],
            $row['zone_name'] ?: 'N/A',
            $row['issue_type'],
            $row['flagged_status'],
            $row['date_flagged'],
            $row['flagged_status'] === 'Resolved' ? 'N/A' : $row['days_open'],
            $row['description'] ?: '',
            $row['resolution_notes'] ?: '',
            $row['resolution_date'] ?: ''
        ];
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    // If there's an error, send error response instead of CSV
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to export flagged records: ' . $e->getMessage()
    ]);
}

$conn->close();
?>