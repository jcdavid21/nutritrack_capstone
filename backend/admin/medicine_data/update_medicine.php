<?php
include "../../config.php";
session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION["role_id"] != 2) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Validate required fields
    $medicine_id = intval($_POST['medicine_id'] ?? 0);
    $medicine_name = trim($_POST['medicine_name'] ?? '');
    $stock_quantity = floatval($_POST['stock_quantity'] ?? 0);
    
    if ($medicine_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid medicine ID'
        ]);
        exit();
    }
    
    if (empty($medicine_name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Medicine name is required'
        ]);
        exit();
    }
    
    if ($stock_quantity < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock quantity cannot be negative'
        ]);
        exit();
    }
    
    // Check if medicine exists
    $checkSql = "SELECT medicine_id FROM tbl_medicine WHERE medicine_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('i', $medicine_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Medicine not found'
        ]);
        exit();
    }
    
    // Check if another medicine with same name exists (excluding current one)
    $nameSql = "SELECT medicine_id FROM tbl_medicine WHERE medicine_name = ? AND medicine_id != ?";
    $nameStmt = $conn->prepare($nameSql);
    $nameStmt->bind_param('si', $medicine_name, $medicine_id);
    $nameStmt->execute();
    $result = $nameStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Another medicine with this name already exists'
        ]);
        exit();
    }
    
    // Prepare data for update
    $brand = trim($_POST['brand'] ?? '');
    $generic_name = trim($_POST['generic_name'] ?? '');
    $dosage_form = trim($_POST['dosage_form'] ?? '');
    $strength = trim($_POST['strength'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $minimum_stock = floatval($_POST['minimum_stock'] ?? 10);
    $unit_cost = floatval($_POST['unit_cost'] ?? 0);
    $expiry_date = trim($_POST['expiry_date'] ?? '');
    $batch_number = trim($_POST['batch_number'] ?? '');
    $supplier = trim($_POST['supplier'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');
    
    // Convert empty strings to NULL for database
    $brand = empty($brand) ? null : $brand;
    $generic_name = empty($generic_name) ? null : $generic_name;
    $dosage_form = empty($dosage_form) ? null : $dosage_form;
    $strength = empty($strength) ? null : $strength;
    $unit = empty($unit) ? null : $unit;
    $expiry_date = empty($expiry_date) ? null : $expiry_date;
    $batch_number = empty($batch_number) ? null : $batch_number;
    $supplier = empty($supplier) ? null : $supplier;
    $description = empty($description) ? null : $description;

    if($minimum_stock < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Minimum stock cannot be negative'
        ]);
        exit();
    }

    if($unit_cost < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Unit cost cannot be negative'
        ]);
        exit();
    }


    if($stock_quantity < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock quantity cannot be negative'
        ]);
        exit();
    }
    
    // Validate status
    $validStatuses = ['Active', 'Inactive', 'Expired'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status value'
        ]);
        exit();
    }
    
    // Validate expiry date if provided
    if ($expiry_date && !DateTime::createFromFormat('Y-m-d', $expiry_date)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid expiry date format'
        ]);
        exit();
    }
    
    $sql = "UPDATE tbl_medicine SET
        medicine_name = ?, brand = ?, generic_name = ?, dosage_form = ?, 
        strength = ?, unit = ?, stock_quantity = ?, minimum_stock = ?, 
        unit_cost = ?, expiry_date = ?, batch_number = ?, supplier = ?, 
        description = ?, status = ?, updated_at = NOW()
        WHERE medicine_id = ?";
    
    $stmt = $conn->prepare($sql);
    // Fixed: Changed 'ssssssdddssssi' to 'ssssssdddssssi' (15 characters for 15 parameters)
    $stmt->bind_param('ssssssddssssssi',
        $medicine_name, $brand, $generic_name, $dosage_form,
        $strength, $unit, $stock_quantity, $minimum_stock,
        $unit_cost, $expiry_date, $batch_number, $supplier,
        $description, $status, $medicine_id
    );
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Medicine updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update medicine');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
}
?>