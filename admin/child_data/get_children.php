<?php
include "../../backend/config.php";

// Get all children for dropdown
$children_query = "SELECT tc.child_id, tc.first_name, tc.last_name, tc.birthdate, tc.gender, 
                          tc.created_at, tb.zone_name
                   FROM tbl_child tc
                   LEFT JOIN tbl_barangay tb ON tc.zone_id = tb.zone_id
                   ORDER BY tc.first_name, tc.last_name";

$children_result = mysqli_query($conn, $children_query);
$children = [];

if ($children_result) {
    while ($child = $children_result->fetch_assoc()) {
        $children[] = $child;
    }
}

echo json_encode([
    "children" => $children
]);
?>