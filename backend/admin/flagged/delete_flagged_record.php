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
    $flagged_id = isset($_POST['flagged_id']) ? intval($_POST['flagged_id']) : 0;

    // Validate required fields
    if (!$flagged_id) {
        throw new Exception('Flagged record ID is required');
    }

    // Check if flagged record exists
    $check_sql = "SELECT flagged_id FROM tbl_flagged_record WHERE flagged_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $flagged_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Flagged record not found');
    }

    $conn->begin_transaction();
    // If the flagged record is linked to a medicine log, restore the stock
    $getMedicineQuery = "SELECT ml.medicine_id, ml.quantity_given 
                        FROM tbl_medicine_log ml 
                        LEFT JOIN tbl_flagged_record fr ON ml.flagged_id = fr.flagged_id
                        WHERE fr.flagged_id = ?";
    $getMedicineStmt = $conn->prepare($getMedicineQuery);
    $getMedicineStmt->bind_param('i', $flagged_id);
    $getMedicineStmt->execute();
    $result = $getMedicineStmt->get_result();

    if($result->num_rows > 0){
        while($data = $result->fetch_assoc()){
            // Restore stock quantity
            $updateStockQuery = "UPDATE tbl_medicine 
                                SET stock_quantity = stock_quantity + ? 
                                WHERE medicine_id = ?";
            $updateStockStmt = $conn->prepare($updateStockQuery);
            $updateStockStmt->bind_param('ii', $data['quantity_given'], $data['medicine_id']);
            $updateStockStmt->execute();
        }
    }

    // Delete flagged record
    $sql = "DELETE FROM tbl_flagged_record WHERE flagged_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $flagged_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log the activity
            date_default_timezone_set('Asia/Manila');
            $user_id = $_SESSION['user_id'] ?? null;
            $activity_type = "Deleted flagged record with ID: " . $flagged_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode([
                'status' => 'success',
                'message' => 'Flagged record deleted successfully'
            ]);
        } else {
            throw new Exception('No record was deleted');
        }

        $conn->commit();
    } else {
        throw new Exception('Failed to delete flagged record: ' . $stmt->error);
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