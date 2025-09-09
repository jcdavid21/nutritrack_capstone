<?php
include "../../backend/config.php";

// Get all roles
$roles_query = "SELECT role_id, role_name FROM tbl_roles ORDER BY role_name ASC";
$result = mysqli_query($conn, $roles_query);

$roles = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $roles[] = $row;
    }
}

echo json_encode([
    "roles" => $roles
]);
?>