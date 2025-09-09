<?php
include "../../backend/config.php";

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 5;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$offset = ($page - 1) * $limit;

// Base query
$base_query = "FROM tbl_modules tm";
$where_clause = "";
$params = [];

// Add search filter if provided
if (!empty($search)) {
    $where_clause = " WHERE (tm.module_title LIKE ? OR tm.module_content LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param];
}

// Get total count
$count_query = "SELECT COUNT(*) as total " . $base_query . $where_clause;
$count_stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, "ss", ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total = $count_result->fetch_assoc()['total'];

// Get modules with pagination
$modules_query = "SELECT tm.* " . $base_query . $where_clause . " ORDER BY tm.posted_date DESC LIMIT ? OFFSET ?";
$modules_stmt = mysqli_prepare($conn, $modules_query);

if (!empty($params)) {
    mysqli_stmt_bind_param($modules_stmt, "ssii", ...[...$params, $limit, $offset]);
} else {
    mysqli_stmt_bind_param($modules_stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($modules_stmt);
$result_modules = mysqli_stmt_get_result($modules_stmt);

$modules = [];
if ($result_modules) {
    while ($row = $result_modules->fetch_assoc()) {
        $modules[] = $row;
    }
}

echo json_encode([
    "modules" => $modules,
    "total" => $total,
    "page" => $page,
    "limit" => $limit,
    "totalPages" => ceil($total / $limit)
]);
?>