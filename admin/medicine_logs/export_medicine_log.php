<?php
require_once '../../backend/config.php';
session_start();

// Check if user is logged in and has proper role
if (!isset($_SESSION['user_id']) || $_SESSION["role_id"] != 2) {
    header("Location: ../../components/login.php");
    exit();
}

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
                ml.log_id,
                CONCAT(c.first_name, ' ', c.last_name) as child_name,
                TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) as child_age,
                c.gender as child_gender,
                m.medicine_name,
                m.brand,
                m.generic_name,
                m.dosage_form,
                m.strength,
                ml.quantity_given,
                m.unit,
                ml.frequency,
                ml.duration,
                ml.dosage_instructions,
                COALESCE(ft.flagged_name, 'No Flagged Record') as flagged_type,
                fr.description as flagged_description,
                ml.date_administered,
                ud.full_name as administered_by,
                r.role_name as administrator_role,
                ml.notes,
                ml.created_at
            FROM tbl_medicine_log ml
            JOIN tbl_child c ON ml.child_id = c.child_id
            JOIN tbl_medicine m ON ml.medicine_id = m.medicine_id
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            LEFT JOIN tbl_flagged_type ft ON fr.issue_type = ft.ft_id
            LEFT JOIN tbl_user u ON ml.administered_by = u.user_id
            LEFT JOIN tbl_user_details ud ON u.user_id = ud.user_id
            LEFT JOIN tbl_roles r ON u.role_id = r.role_id
            WHERE {$whereClause}
            ORDER BY ml.date_administered DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = $result->fetch_all(MYSQLI_ASSOC);

    // Generate filename with current date
    $filename = 'medicine_log_report_' . date('Y-m-d_H-i-s') . '.csv';
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV headers
    $headers = [
        'Log ID',
        'Child Name',
        'Child Age',
        'Child Gender',
        'Medicine Name',
        'Brand',
        'Generic Name',
        'Dosage Form',
        'Strength',
        'Quantity Given',
        'Unit',
        'Frequency',
        'Duration',
        'Dosage Instructions',
        'Flagged Issue Type',
        'Issue Description',
        'Date Administered',
        'Administered By',
        'Administrator Role',
        'Notes',
        'Log Created At'
    ];
    
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($logs as $log) {
        $row = [
            $log['log_id'],
            $log['child_name'],
            $log['child_age'] . ' years old',
            $log['child_gender'] ?: 'Not specified',
            $log['medicine_name'],
            $log['brand'] ?: 'N/A',
            $log['generic_name'] ?: 'N/A',
            $log['dosage_form'] ?: 'N/A',
            $log['strength'] ?: 'N/A',
            $log['quantity_given'],
            $log['unit'] ?: '',
            $log['frequency'] ?: 'Not specified',
            $log['duration'] ?: 'Not specified',
            $log['dosage_instructions'] ?: 'Not specified',
            $log['flagged_type'],
            $log['flagged_description'] ?: 'N/A',
            date('Y-m-d H:i:s', strtotime($log['date_administered'])),
            $log['administered_by'] ?: 'System',
            $log['administrator_role'] ?: 'N/A',
            $log['notes'] ?: 'No notes',
            date('Y-m-d H:i:s', strtotime($log['created_at']))
        ];
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
    
} catch (PDOException $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Export Error</h1>';
    echo '<p>Failed to export medicine log data: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="javascript:history.back()">Go Back</a></p>';
    exit();
}