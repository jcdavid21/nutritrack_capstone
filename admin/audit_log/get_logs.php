<?php 
include "../../backend/config.php";

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 5;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get filter parameters (these were missing!)
$activity_type = isset($_GET['activity_type']) ? trim($_GET['activity_type']) : '';
$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$offset = ($page - 1) * $limit;

// Base query with role JOIN
$base_query = "FROM tbl_audit_log ts 
               INNER JOIN tbl_user tu ON ts.user_id = tu.user_id
               INNER JOIN tbl_user_details td ON ts.user_id = td.user_id
               LEFT JOIN tbl_roles tr ON tu.role_id = tr.role_id";

// Build WHERE conditions dynamically
$where_conditions = [];
$params = [];
$param_types = "";

// Add search filter if provided
if (!empty($search)) {
    $where_conditions[] = "(tu.username LIKE ? OR td.full_name LIKE ? OR tr.role_name LIKE ? OR ts.activity_type LIKE ?)";
    $search_param = "%{$search}%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $param_types .= "ssss";
}

// Add activity type filter with pattern matching
if (!empty($activity_type)) {
    switch(strtolower($activity_type)) {
        case 'delete':
            $where_conditions[] = "ts.activity_type LIKE ?";
            $params[] = "%Deleted%";
            $param_types .= "s";
            break;
        case 'add':
            $where_conditions[] = "ts.activity_type LIKE ?";
            $params[] = "%Created%";
            $param_types .= "s";
            break;
        case 'update':
            $where_conditions[] = "ts.activity_type LIKE ?";
            $params[] = "%Updated%";
            $param_types .= "s";
            break;
        case 'login':
            $where_conditions[] = "ts.activity_type LIKE ?";
            $params[] = "%Login%";
            $param_types .= "s";
            break;
        default:
            $where_conditions[] = "ts.activity_type LIKE ?";
            $params[] = "%{$activity_type}%";
            $param_types .= "s";
    }
}

// Add role filter
if ($role_id > 0) {
    $where_conditions[] = "tu.role_id = ?";
    $params[] = $role_id;
    $param_types .= "i";
}

// Add date range filters
if (!empty($date_from)) {
    $where_conditions[] = "DATE(ts.log_date) >= ?";
    $params[] = $date_from;
    $param_types .= "s";
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(ts.log_date) <= ?";
    $params[] = $date_to;
    $param_types .= "s";
}

// Build final WHERE clause
$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
}

// Get total count
$count_query = "SELECT COUNT(*) as total " . $base_query . $where_clause;
$count_stmt = mysqli_prepare($conn, $count_query);

if ($count_stmt === false) {
    die(json_encode(["error" => "Prepare failed for count query: " . mysqli_error($conn)]));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total = $count_result->fetch_assoc()['total'];

// Get logs with pagination
$logs_query = "SELECT ts.user_id, ts.activity_type, ts.log_id, ts.log_date, 
                      tu.username, tu.role_id, 
                      td.full_name, td.contact, td.address, 
                      tr.role_name " . 
              $base_query . $where_clause . 
              " ORDER BY ts.log_date DESC LIMIT ? OFFSET ?";

$logs_stmt = mysqli_prepare($conn, $logs_query);

if ($logs_stmt === false) {
    die(json_encode(["error" => "Prepare failed for logs query: " . mysqli_error($conn)]));
}

// Add limit and offset to parameters
$all_params = $params;
$all_params[] = $limit;
$all_params[] = $offset;
$all_param_types = $param_types . "ii";

if (!empty($params)) {
    mysqli_stmt_bind_param($logs_stmt, $all_param_types, ...$all_params);
} else {
    mysqli_stmt_bind_param($logs_stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($logs_stmt);
$result_logs = mysqli_stmt_get_result($logs_stmt);

$logs = [];
if ($result_logs) {
    while ($row = $result_logs->fetch_assoc()) {
        $logs[] = $row;
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

// Define fixed activity types with counts
$activity_count_query = "SELECT 
    SUM(CASE WHEN activity_type LIKE '%Logged%' THEN 1 ELSE 0 END) as logged_count,
    SUM(CASE WHEN activity_type LIKE '%Added%' THEN 1 ELSE 0 END) as added_count,
    SUM(CASE WHEN activity_type LIKE '%Update%' THEN 1 ELSE 0 END) as update_count,
    SUM(CASE WHEN activity_type LIKE '%Created%' THEN 1 ELSE 0 END) as created_count,
    SUM(CASE WHEN activity_type LIKE '%Deleted%' THEN 1 ELSE 0 END) as deleted_count
FROM tbl_audit_log";

$activity_count_result = mysqli_query($conn, $activity_count_query);
$counts = $activity_count_result->fetch_assoc();

// Create fixed activity types array in the order you specified
$activity_types = [];

if ($counts['logged_count'] > 0) {
    $activity_types[] = [
        'value' => 'logged',
        'label' => 'Logged',
        'count' => $counts['logged_count']
    ];
}

if ($counts['added_count'] > 0) {
    $activity_types[] = [
        'value' => 'added',
        'label' => 'Added',
        'count' => $counts['added_count']
    ];
}

if ($counts['update_count'] > 0) {
    $activity_types[] = [
        'value' => 'update',
        'label' => 'Update',
        'count' => $counts['update_count']
    ];
}

if ($counts['created_count'] > 0) {
    $activity_types[] = [
        'value' => 'created',
        'label' => 'Created',
        'count' => $counts['created_count']
    ];
}

if ($counts['deleted_count'] > 0) {
    $activity_types[] = [
        'value' => 'deleted',
        'label' => 'Deleted',
        'count' => $counts['deleted_count']
    ];
}

// Close statements
mysqli_stmt_close($count_stmt);
mysqli_stmt_close($logs_stmt);

// Debug information (remove this in production)
$debug_info = [
    'filters_received' => [
        'search' => $search,
        'activity_type' => $activity_type,
        'role_id' => $role_id,
        'date_from' => $date_from,
        'date_to' => $date_to
    ],
    'where_clause' => $where_clause,
    'total_found' => $total
];

// Add to your JSON response:
echo json_encode([
    "log_data" => $logs,
    "total" => $total,
    "roles" => $roles,
    "activity_types" => $activity_types,
    "page" => $page,
    "limit" => $limit,
    "totalPages" => ceil($total / $limit),
    "debug" => $debug_info
]);

?>