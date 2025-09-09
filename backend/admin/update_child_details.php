<?php
    include_once "../config.php";
    include_once '../reports/audit_log.php';
    session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = intval($_POST['child_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $zone_id = intval($_POST['zone_id']);
    $_SESSION['user_id'] = $user_id = $_SESSION['user_id'] ?? null;

    // Validation
    if (empty($first_name) || empty($last_name) || empty($gender) || empty($birthdate) || empty($zone_id)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    try {
        $update_query = "UPDATE tbl_child SET 
                        first_name = ?, 
                        last_name = ?, 
                        gender = ?, 
                        birthdate = ?, 
                        zone_id = ?
                        WHERE child_id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssii", $first_name, $last_name, $gender, $birthdate, $zone_id, $child_id);
        
        if ($stmt->execute()) {
            date_default_timezone_set('Asia/Manila');
            $activity_type = "Updated child details for child ID: " . $child_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode(['status' => 'success', 'message' => 'Child details updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update child details']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>