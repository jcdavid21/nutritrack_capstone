<?php
include "../../backend/config.php";

$child_id = isset($_GET['child_id']) ? intval($_GET['child_id']) : 0;

if ($child_id <= 0) {
    echo json_encode(["error" => "Invalid child ID"]);
    exit;
}

// Get child details
$child_query = "SELECT tc.*, tb.zone_name, tud.full_name as registered_by_name
                FROM tbl_child tc
                LEFT JOIN tbl_barangay tb ON tc.zone_id = tb.zone_id
                LEFT JOIN tbl_user_details tud ON tc.registered_by = tud.user_id
                WHERE tc.child_id = ?";

$child_stmt = mysqli_prepare($conn, $child_query);
mysqli_stmt_bind_param($child_stmt, "i", $child_id);
mysqli_stmt_execute($child_stmt);
$child_result = mysqli_stmt_get_result($child_stmt);
$child = $child_result->fetch_assoc();

if (!$child) {
    echo json_encode(["error" => "Child not found"]);
    exit;
}

// Get all nutrition records for this child
$records_query = "SELECT nr.*, tns.status_name,
                         tud.full_name as recorded_by_name
                  FROM tbl_nutritrion_record nr
                  INNER JOIN tbl_nutrition_status tns ON nr.status_id = tns.status_id
                  LEFT JOIN tbl_user_details tud ON nr.recorded_by = tud.user_id
                  WHERE nr.child_id = ?
                  ORDER BY nr.date_recorded DESC";

$records_stmt = mysqli_prepare($conn, $records_query);
mysqli_stmt_bind_param($records_stmt, "i", $child_id);
mysqli_stmt_execute($records_stmt);
$records_result = mysqli_stmt_get_result($records_stmt);

$records = [];
if ($records_result) {
    while ($record = $records_result->fetch_assoc()) {
        $records[] = $record;
    }
}

// Close statements
mysqli_stmt_close($child_stmt);
mysqli_stmt_close($records_stmt);

echo json_encode([
    "child" => $child,
    "records" => $records
]);
?>