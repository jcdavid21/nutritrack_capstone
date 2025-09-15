<?php
header('Content-Type: application/json');
require_once '../../backend/config.php';

try {
    // Check if parent_id is provided
    if (!isset($_GET['parent_id']) || empty($_GET['parent_id'])) {
        throw new Exception('Parent ID is required');
    }

    $parent_id = intval($_GET['parent_id']);

    // Validate parent_id is a positive integer
    if ($parent_id <= 0) {
        throw new Exception('Invalid parent ID');
    }

    // Prepare and execute query to get parent details
    $query = "SELECT 
                p.parent_id,
                p.child_id,
                p.parent_name,
                p.contact,
                p.occupation,
                p.relationship,
                c.first_name,
                c.last_name
              FROM tbl_parent_details p
              LEFT JOIN tbl_child c ON p.child_id = c.child_id
              WHERE p.parent_id = ?";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param('i', $parent_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Parent not found');
    }

    $parent = $result->fetch_assoc();
    
    // Close statement
    $stmt->close();

    // Return success response
    echo json_encode([
        'status' => 'success',
        'parent' => [
            'parent_id' => $parent['parent_id'],
            'child_id' => $parent['child_id'],
            'parent_name' => $parent['parent_name'],
            'contact' => $parent['contact'],
            'occupation' => $parent['occupation'],
            'relationship' => $parent['relationship'],
            'child_name' => $parent['first_name'] . ' ' . $parent['last_name']
        ]
    ]);

} catch (Exception $e) {
    // Log error for debugging
    error_log('Error in get_parent_for_edit.php: ' . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>