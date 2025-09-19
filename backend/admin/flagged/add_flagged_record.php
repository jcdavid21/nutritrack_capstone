<?php
header('Content-Type: application/json');
include_once "../../config.php";
include_once '../../reports/audit_log.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

    date_default_timezone_set('Asia/Manila');
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
     $medicine_entries = json_decode($_POST['medicine_entries'] ?? '[]', true);

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

    $conn->begin_transaction(); 

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

        // Insert medicine entries if any
        if (!empty($medicine_entries)) {
            foreach ($medicine_entries as $medicine) {
                // Validate medicine entry data
                if (!isset($medicine['medicine_id']) || !isset($medicine['quantity']) || !isset($medicine['date_administered'])) {
                    continue;
                }
                
                // Check if medicine has enough stock
                $stockQuery = "SELECT stock_quantity FROM tbl_medicine WHERE medicine_id = ?";
                $stockStmt = $conn->prepare($stockQuery);
                $stockStmt->bind_param('i', $medicine['medicine_id']);
                $stockStmt->execute();
                $stockResult = $stockStmt->get_result();
                $stockData = $stockResult->fetch_assoc();

                if ($stockData['stock_quantity'] < $medicine['quantity']) {
                    throw new Exception('Insufficient stock for medicine ID: ' . $medicine['medicine_id']);
                }

                $medicine_id = $medicine['medicine_id'];
                $quantity_given = $medicine['quantity'];
                $dosage_instructions = isset($medicine['dosage_instructions']) ? trim($medicine['dosage_instructions']) : null;
                $frequency = isset($medicine['frequency']) ? trim($medicine['frequency']) : null;
                $duration = isset($medicine['duration']) ? trim($medicine['duration']) : null;
                
                $date_administered = $medicine['date_administered'];
                $notes = isset($medicine['notes']) ? trim($medicine['notes']) : null;
                $administered_by = $_SESSION['user_id'] ?? null;
                
                // Insert medicine log entry
                $medicineQuery = "INSERT INTO tbl_medicine_log 
                                (flagged_id, child_id, medicine_id, quantity_given, dosage_instructions, 
                                 frequency, duration, administered_by, date_administered, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $medicineStmt = $conn->prepare($medicineQuery);
                $medicineStmt->bind_param("iiidsssiss", 
                    $flagged_id, 
                    $child_id, 
                    $medicine_id, 
                    $quantity_given, 
                    $dosage_instructions, 
                    $frequency, 
                    $duration, 
                    $administered_by, 
                    $date_administered, 
                    $notes
                );
                $medicineStmt->execute();
                
                // Update medicine stock
                $updateStockQuery = "UPDATE tbl_medicine 
                                   SET stock_quantity = stock_quantity - ? 
                                   WHERE medicine_id = ?";
                $updateStockStmt = $conn->prepare($updateStockQuery);
                $updateStockStmt->bind_param('ii', $medicine['quantity'], $medicine['medicine_id']);
                $updateStockStmt->execute();
            }
        }

        $conn->commit();

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