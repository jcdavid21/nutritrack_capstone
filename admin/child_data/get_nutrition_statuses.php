<?php
include "../../backend/config.php";

// Get all nutrition statuses for dropdown
$statuses_query = "SELECT status_id, status_name 
                   FROM tbl_nutrition_status 
                   ORDER BY status_name";

$statuses_result = mysqli_query($conn, $statuses_query);
$statuses = [];

if ($statuses_result) {
    while ($status = $statuses_result->fetch_assoc()) {
        $statuses[] = $status;
    }
}

echo json_encode([
    "statuses" => $statuses
]);
?>