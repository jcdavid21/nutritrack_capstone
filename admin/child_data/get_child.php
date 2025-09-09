<?php
include "../../backend/config.php";

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 5;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$offset = ($page - 1) * $limit;

// Base query - get latest nutrition record for each child
$base_query = "FROM tbl_child tc
               LEFT JOIN tbl_barangay tb ON tc.zone_id = tb.zone_id
               LEFT JOIN tbl_user_details td ON tc.registered_by = td.user_id
               LEFT JOIN (
                   SELECT nr1.child_id, nr1.nutrition_id, nr1.recorded_by, nr1.weight, 
                          nr1.height, nr1.bmi, nr1.date_recorded, nr1.status_id, 
                          tns.status_name
                   FROM tbl_nutritrion_record nr1
                   INNER JOIN tbl_nutrition_status tns ON nr1.status_id = tns.status_id
                   WHERE nr1.date_recorded = (
                       SELECT MAX(nr2.date_recorded) 
                       FROM tbl_nutritrion_record nr2 
                       WHERE nr2.child_id = nr1.child_id
                   )
               ) latest_record ON tc.child_id = latest_record.child_id";

$where_clause = "";
$params = [];

// Add search filter if provided
if (!empty($search)) {
    $where_clause = " WHERE (tc.first_name LIKE ? OR tc.last_name LIKE ? OR tb.zone_name LIKE ? OR td.full_name LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param, $search_param];
}

// Get total count
$count_query = "SELECT COUNT(*) as total " . $base_query . $where_clause;
$count_stmt = mysqli_prepare($conn, $count_query);

if ($count_stmt === false) {
    die(json_encode(["error" => "Prepare failed for count query: " . mysqli_error($conn)]));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total = $count_result->fetch_assoc()['total'];

// Get children with their latest nutrition records
$children_query = "SELECT tc.child_id, tc.first_name, tc.last_name, tc.birthdate, tc.gender, tc.created_at,
                          tb.zone_name, td.full_name, td.address, td.contact,
                          latest_record.nutrition_id, latest_record.recorded_by, latest_record.weight, 
                          latest_record.height, latest_record.bmi, latest_record.date_recorded, 
                          latest_record.status_id, latest_record.status_name
                   " . $base_query . $where_clause . " 
                   ORDER BY tc.created_at DESC 
                   LIMIT ? OFFSET ?";

$children_stmt = mysqli_prepare($conn, $children_query);

if ($children_stmt === false) {
    die(json_encode(["error" => "Prepare failed for children query: " . mysqli_error($conn)]));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($children_stmt, str_repeat('s', count($params)) . 'ii', ...array_merge($params, [$limit, $offset]));
} else {
    mysqli_stmt_bind_param($children_stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($children_stmt);
$result_children = mysqli_stmt_get_result($children_stmt);

$children = [];
if ($result_children) {
    while ($row = $result_children->fetch_assoc()) {
        $children[] = $row;
    }
}

// Get all roles for dropdowns (if needed)
$roles_query = "SELECT role_id, role_name FROM tbl_roles ORDER BY role_name";
$roles_result = mysqli_query($conn, $roles_query);
$roles = [];
if ($roles_result) {
    while ($role = $roles_result->fetch_assoc()) {
        $roles[] = $role;
    }
}

// Get nutrition statuses for reference
$statuses_query = "SELECT status_id, status_name FROM tbl_nutrition_status ORDER BY status_name";
$statuses_result = mysqli_query($conn, $statuses_query);
$statuses = [];
if ($statuses_result) {
    while ($status = $statuses_result->fetch_assoc()) {
        $statuses[] = $status;
    }
}

// Close statements
mysqli_stmt_close($count_stmt);
mysqli_stmt_close($children_stmt);

echo json_encode([
    "user_data" => $children, // Keep same structure as original
    "roles" => $roles,
    "statuses" => $statuses,
    "total" => $total,
    "page" => $page,
    "limit" => $limit,
    "totalPages" => ceil($total / $limit)
]);
?>