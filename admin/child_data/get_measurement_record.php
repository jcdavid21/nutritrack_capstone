<?php
include "../../backend/config.php";

header('Content-Type: application/json');

if (!isset($_GET['record_id'])) {
    echo json_encode(['error' => 'Record ID is required']);
    exit();
}

$record_id = intval($_GET['record_id']);

try {
    // Get the specific nutrition record with child details
    $query = "
        SELECT 
            nr.nutrition_id,
            nr.child_id,
            nr.weight,
            nr.height,
            nr.bmi,
            nr.date_recorded,
            nr.status_id,
            c.first_name,
            c.last_name,
            c.gender,
            c.birthdate,
            ns.status_name
        FROM tbl_nutritrion_record nr
        LEFT JOIN tbl_child c ON nr.child_id = c.child_id
        LEFT JOIN tbl_nutrition_status ns ON nr.status_id = ns.status_id
        WHERE nr.nutrition_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $record_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $record = $result->fetch_assoc();
        
        // Format the date for input field (YYYY-MM-DD)
        $record['date_recorded'] = date('Y-m-d', strtotime($record['date_recorded']));
        
        echo json_encode([
            'success' => true,
            'record' => $record
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Record not found'
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>