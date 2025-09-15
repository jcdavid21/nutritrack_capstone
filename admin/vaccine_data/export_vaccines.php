<?php
require_once '../../backend/config.php';

try {
    // Get filter parameters
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $vaccine_type = isset($_GET['vaccine_type']) ? $_GET['vaccine_type'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $zone_id = isset($_GET['zone_id']) ? $_GET['zone_id'] : '';
    
    // Build the query
    $sql = "SELECT 
                vr.vaccine_id,
                vr.child_id,
                c.first_name,
                c.last_name,
                c.birthdate,
                c.gender,
                c.zone_id,
                z.zone_name,
                vr.vaccine_name,
                vr.vaccine_status,
                vr.vaccine_date,
                vr.administered_by,
                td.full_name as administered_by_name
            FROM tbl_vaccine_record vr
            LEFT JOIN tbl_child c ON vr.child_id = c.child_id
            LEFT JOIN tbl_barangay z ON c.zone_id = z.zone_id
            LEFT JOIN tbl_user_details td ON vr.administered_by = td.user_id
            WHERE 1=1";
    
    $conditions = [];
    $types = '';
    $params = [];
    
    // Add filters
    if (!empty($status)) {
        $conditions[] = "vr.vaccine_status = ?";
        $types .= 's';
        $params[] = $status;
    }
    
    if (!empty($vaccine_type)) {
        $conditions[] = "vr.vaccine_name LIKE ?";
        $types .= 's';
        $params[] = '%' . $vaccine_type . '%';
    }
    
    if (!empty($start_date)) {
        $conditions[] = "DATE(vr.vaccine_date) >= ?";
        $types .= 's';
        $params[] = $start_date;
    }
    
    if (!empty($end_date)) {
        $conditions[] = "DATE(vr.vaccine_date) <= ?";
        $types .= 's';
        $params[] = $end_date;
    }

    if(!empty($zone_id)) {
        $conditions[] = "c.zone_id = ?";
        $types .= 'i';
        $params[] = intval($zone_id);
    }
    
    // Add conditions to SQL
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY vr.vaccine_date DESC";
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Set headers for CSV download
    $filename = 'vaccine_records_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 (helps with Excel compatibility)
    fwrite($output, "\xEF\xBB\xBF");
    
    // CSV headers
    $headers = [
        'Vaccine ID',
        'Child ID', 
        'Child Name',
        'Child Age',
        'Gender',
        'Zone',
        'Vaccine Name',
        'Status',
        'Date Administered',
        'Administered By',
        'Administrator ID'
    ];
    
    fputcsv($output, $headers);
    
    // Output data rows
    while ($row = $result->fetch_assoc()) {
        // Calculate age
        $birthdate = new DateTime($row['birthdate']);
        $today = new DateTime();
        $age = $today->diff($birthdate)->y;
        
        // Format date
        $vaccine_date = new DateTime($row['vaccine_date']);
        $formatted_date = $vaccine_date->format('Y-m-d H:i:s');
        
        $csv_row = [
            'VAC-' . str_pad($row['vaccine_id'], 4, '0', STR_PAD_LEFT),
            $row['child_id'],
            $row['first_name'] . ' ' . $row['last_name'],
            $age . ' years',
            $row['gender'] ?: 'N/A',
            $row['zone_name'] ?: 'N/A',
            $row['vaccine_name'],
            $row['vaccine_status'],
            $formatted_date,
            $row['administered_by_name'] ?: 'Unknown',
            $row['administered_by'] ?: 'N/A'
        ];
        
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Export failed: ' . $e->getMessage()
    ]);
}
?>