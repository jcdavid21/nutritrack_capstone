<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    if (!isset($_GET['log_id']) || empty($_GET['log_id'])) {
        throw new Exception('Log ID parameter is required');
    }
    
    $logId = (int)$_GET['log_id'];
    
    $sql = "SELECT 
                ml.log_id,
                ml.quantity_given,
                ml.frequency,
                ml.duration,
                ml.dosage_instructions,
                ml.date_administered,
                ml.notes,
                CONCAT(c.first_name, ' ', c.last_name) as child_name,
                TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) as child_age,
                c.gender as child_gender,
                m.medicine_name,
                m.brand,
                m.generic_name,
                m.dosage_form,
                m.strength,
                m.unit,
                ft.flagged_name as flagged_type,
                ud.full_name as administered_by_name,
                r.role_name as administered_by_role
            FROM tbl_medicine_log ml
            JOIN tbl_child c ON ml.child_id = c.child_id
            JOIN tbl_medicine m ON ml.medicine_id = m.medicine_id
            LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
            LEFT JOIN tbl_flagged_type ft ON fr.issue_type = ft.ft_id
            LEFT JOIN tbl_user u ON ml.administered_by = u.user_id
            LEFT JOIN tbl_user_details ud ON u.user_id = ud.user_id
            LEFT JOIN tbl_roles r ON u.role_id = r.role_id
            WHERE ml.log_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $logId);
    $stmt->execute();
    $result = $stmt->get_result();
    $log = $result->fetch_assoc();
    
    if (!$log) {
        throw new Exception('Medicine log not found');
    }
    
    echo json_encode([
        'success' => true,
        'log' => $log
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'log' => null
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'log' => null
    ]);
}