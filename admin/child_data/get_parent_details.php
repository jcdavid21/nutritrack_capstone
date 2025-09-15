<?php
include "../../backend/config.php";

if (!isset($_GET['child_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Child ID required']);
    exit;
}

$child_id = $_GET['child_id'];

// Fetch parent details for the given child ID
$parent_query = "SELECT parent_id, parent_name, contact, relationship, occupation
                 FROM tbl_parent_details
                 WHERE child_id = ?";
$parent_stmt = $conn->prepare($parent_query);
$parent_stmt->bind_param("i", $child_id);
$parent_stmt->execute();
$parent_result = $parent_stmt->get_result();

if ($parent_result->num_rows > 0) {
    $parent_details = [];
    
    // Fetch all rows, not just one
    while ($row = $parent_result->fetch_assoc()) {
        $parent_details[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $parent_details]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No parent details found']);
}

?>