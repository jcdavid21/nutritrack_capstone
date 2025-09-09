<?php
include "../../backend/config.php";

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 5;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$offset = ($page - 1) * $limit;

// Base query
$base_query = "FROM tbl_events te INNER JOIN tbl_barangay tz ON tz.zone_id = te.zone_id";
$where_clause = "";
$params = [];

// Add search filter if provided
if (!empty($search)) {
    $where_clause = " WHERE (te.title LIKE ? OR te.description LIKE ? OR tz.zone_name LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param];
}

// Get total count
$count_query = "SELECT COUNT(*) as total " . $base_query . $where_clause;
$count_stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, "sss", ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total = $count_result->fetch_assoc()['total'];

// Get events with pagination
$events_query = "SELECT te.*, tz.zone_name " . $base_query . $where_clause . " ORDER BY te.event_date DESC LIMIT ? OFFSET ?";
$events_stmt = mysqli_prepare($conn, $events_query);

if (!empty($params)) {
    mysqli_stmt_bind_param($events_stmt, "sssii", ...[...$params, $limit, $offset]);
} else {
    mysqli_stmt_bind_param($events_stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($events_stmt);
$result_events = mysqli_stmt_get_result($events_stmt);

$events = [];
if ($result_events) {
    while ($row = $result_events->fetch_assoc()) {
        $events[] = $row;
    }
}

echo json_encode([
    "events" => $events,
    "total" => $total,
    "page" => $page,
    "limit" => $limit,
    "totalPages" => ceil($total / $limit)
]);
?>