<?php
include "../../backend/config.php";

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 5;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$offset = ($page - 1) * $limit;

// Base query with role JOIN
$base_query = "FROM tbl_user ts 
               INNER JOIN tbl_user_details td ON ts.user_id = td.user_id
               LEFT JOIN tbl_roles tr ON ts.role_id = tr.role_id";
$where_clause = "";
$params = [];

// Add search filter if provided
if (!empty($search)) {
    $where_clause = " WHERE (ts.username LIKE ? OR td.full_name LIKE ? OR tr.role_name LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param];
}

// Get total count
$count_query = "SELECT COUNT(*) as total " . $base_query . $where_clause;
$count_stmt = mysqli_prepare($conn, $count_query);

if ($count_stmt === false) {
    die(json_encode(["error" => "Prepare failed for count query: " . mysqli_error($conn)]));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, "sss", ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total = $count_result->fetch_assoc()['total'];

// Get users with pagination
$users_query = "SELECT ts.user_id, ts.username, ts.status, ts.role_id, ts.date_added, 
                       td.*, tr.role_name " . $base_query . $where_clause . " ORDER BY ts.date_added DESC LIMIT ? OFFSET ?";
$user_stmt = mysqli_prepare($conn, $users_query);

if ($user_stmt === false) {
    die(json_encode(["error" => "Prepare failed for users query: " . mysqli_error($conn)]));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($user_stmt, "sssii", ...[...$params, $limit, $offset]);
} else {
    mysqli_stmt_bind_param($user_stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($user_stmt);
$result_users = mysqli_stmt_get_result($user_stmt);

$users = [];
if ($result_users) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get all roles for dropdowns
$roles_query = "SELECT role_id, role_name FROM tbl_roles ORDER BY role_name";
$roles_result = mysqli_query($conn, $roles_query);
$roles = [];
if ($roles_result) {
    while ($role = $roles_result->fetch_assoc()) {
        $roles[] = $role;
    }
}

// Close statements
mysqli_stmt_close($count_stmt);
mysqli_stmt_close($user_stmt);

echo json_encode([
    "user_data" => $users,
    "roles" => $roles,
    "total" => $total,
    "page" => $page,
    "limit" => $limit,
    "totalPages" => ceil($total / $limit)
]);
?>