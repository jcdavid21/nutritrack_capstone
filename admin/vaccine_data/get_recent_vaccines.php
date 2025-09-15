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

    if (!empty($search)) {
        $conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR v.vaccine_name LIKE ?)";
        $search_param = "{$search}%";
        $params = [$search_param, $search_param, $search_param];
        $types .= 'sss';
    }

    if (!empty($status)) {
        $conditions[] = "v.vaccine_status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if (!empty($vaccine_type)) {
        $conditions[] = "v.vaccine_name = ?";
        $params[] = $vaccine_type;
        $types .= 's';
    }

    if (!empty($zone)) {
        $conditions[] = "c.zone_id = ?";
        $params[] = $zone;
        $types .= 'i';
    }

try {
    // Get recent vaccine records (last 10)
    $sql = "SELECT v.vaccine_id, v.child_id, v.vaccine_name, v.vaccine_status, v.vaccine_date,
                   c.first_name, c.last_name,
                   ud.full_name as administered_by_name
            FROM tbl_vaccine_record v
            LEFT JOIN tbl_child c ON v.child_id = c.child_id
            LEFT JOIN tbl_user_details ud ON v.administered_by = ud.user_id" .
            (!empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "") .
            " ORDER BY v.vaccine_date DESC LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $vaccines = [];
    while ($row = $result->fetch_assoc()) {
        // Ensure all required fields exist
        $row['first_name'] = $row['first_name'] ?? 'Unknown';
        $row['last_name'] = $row['last_name'] ?? 'Child';
        $row['administered_by_name'] = $row['administered_by_name'] ?? 'Unknown Administrator';
        $row['vaccine_name'] = $row['vaccine_name'] ?? 'Unknown Vaccine';
        $row['vaccine_status'] = $row['vaccine_status'] ?? 'Ongoing';
        
        // Format date
        if ($row['vaccine_date']) {
            $row['vaccine_date'] = date('Y-m-d H:i:s', strtotime($row['vaccine_date']));
        }
        
        $vaccines[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'vaccines' => $vaccines
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch recent vaccines: ' . $e->getMessage(),
        'vaccines' => []
    ]);
}

$conn->close();
?>