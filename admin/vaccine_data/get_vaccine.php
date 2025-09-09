<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    if (!isset($_GET['vaccine_id']) || empty($_GET['vaccine_id'])) {
        throw new Exception('Vaccine ID is required');
    }
    
    $vaccine_id = intval($_GET['vaccine_id']);
    
    $sql = "SELECT v.vaccine_id, v.child_id, v.administered_by, v.vaccine_name, 
                   v.vaccine_status, v.vaccine_date,
                   c.first_name, c.last_name, c.birthdate, c.gender,
                   b.zone_name,
                   ud.full_name as administered_by_name
            FROM tbl_vaccine_record v
            LEFT JOIN tbl_child c ON v.child_id = c.child_id
            LEFT JOIN tbl_barangay b ON c.zone_id = b.zone_id
            LEFT JOIN tbl_user_details ud ON v.administered_by = ud.user_id
            WHERE v.vaccine_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vaccine_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Ensure all required fields exist
        $row['first_name'] = $row['first_name'] ?? 'Unknown';
        $row['last_name'] = $row['last_name'] ?? 'Child';
        $row['administered_by_name'] = $row['administered_by_name'] ?? 'Unknown Administrator';
        $row['vaccine_name'] = $row['vaccine_name'] ?? 'Unknown Vaccine';
        $row['vaccine_status'] = $row['vaccine_status'] ?? 'Ongoing';
        $row['zone_name'] = $row['zone_name'] ?? 'N/A';
        
        // Format date
        if ($row['vaccine_date']) {
            $row['vaccine_date'] = date('Y-m-d H:i:s', strtotime($row['vaccine_date']));
        }
        
        echo json_encode([
            'status' => 'success',
            'vaccine' => $row
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Vaccine record not found'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch vaccine: ' . $e->getMessage()
    ]);
}

$conn->close();
?>