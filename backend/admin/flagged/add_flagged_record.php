<?php
header('Content-Type: application/json');
include_once "../../config.php";
include_once '../../reports/audit_log.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get POST data
    $child_id = isset($_POST['child_id']) ? intval($_POST['child_id']) : 0;
    $issue_type = isset($_POST['issue_type']) ? trim($_POST['issue_type']) : '';
    $date_flagged = isset($_POST['date_flagged']) ? trim($_POST['date_flagged']) : '';
    $flagged_status = isset($_POST['flagged_status']) ? trim($_POST['flagged_status']) : 'Active';
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    // Validate required fields
    if (!$child_id) {
        throw new Exception('Child ID is required');
    }
    
    if (empty($issue_type)) {
        throw new Exception('Issue type is required');
    }
    
    if (empty($date_flagged)) {
        throw new Exception('Date flagged is required');
    }

    // Validate child exists
    $child_check_sql = "SELECT child_id FROM tbl_child WHERE child_id = ?";
    $child_stmt = $conn->prepare($child_check_sql);
    $child_stmt->bind_param("i", $child_id);
    $child_stmt->execute();
    $child_result = $child_stmt->get_result();
    
    if ($child_result->num_rows === 0) {
        throw new Exception('Selected child does not exist');
    }

    // Check for duplicate active flagged records for the same child and issue type
    $duplicate_check_sql = "SELECT flagged_id FROM tbl_flagged_record 
                           WHERE child_id = ? AND issue_type = ? AND flagged_status IN ('Active', 'Under Review')";
    $duplicate_stmt = $conn->prepare($duplicate_check_sql);
    $duplicate_stmt->bind_param("is", $child_id, $issue_type);
    $duplicate_stmt->execute();
    $duplicate_result = $duplicate_stmt->get_result();
    
    if ($duplicate_result->num_rows > 0) {
        throw new Exception('An active or under review record already exists for this child and issue type');
    }

    // Insert flagged record
    $sql = "INSERT INTO tbl_flagged_record (child_id, issue_type, date_flagged, flagged_status, description) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $child_id, $issue_type, $date_flagged, $flagged_status, $description);
    
    if ($stmt->execute()) {
        $flagged_id = $conn->insert_id;

        // Log the activity
        date_default_timezone_set('Asia/Manila');
        $user_id = $_SESSION['user_id'] ?? null;
        $activity_type = "Added flagged record with ID: " . $flagged_id . " for child ID: " . $child_id;
        $log_date = date('Y-m-d H:i:s');
        audit_log($conn, $user_id, $activity_type, $log_date);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Flagged record added successfully',
            'flagged_id' => $flagged_id
        ]);
    } else {
        throw new Exception('Failed to insert flagged record: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>