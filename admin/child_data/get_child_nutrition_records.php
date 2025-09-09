<?php
include "../../backend/config.php";

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$offset = ($page - 1) * $limit;

// Base query - get latest nutrition record for each child
$base_query = "FROM tbl_child tc
               LEFT JOIN tbl_barangay tb ON tc.zone_id = tb.zone_id
               LEFT JOIN (
                   SELECT nr1.child_id, nr1.nutrition_id, nr1.weight, nr1.height, nr1.bmi, 
                          nr1.date_recorded, nr1.status_id, tns.status_name
                   FROM tbl_nutritrion_record nr1
                   LEFT JOIN tbl_nutrition_status tns ON nr1.status_id = tns.status_id
                   WHERE nr1.date_recorded = (
                       SELECT MAX(nr2.date_recorded) 
                       FROM tbl_nutritrion_record nr2 
                       WHERE nr2.child_id = nr1.child_id
                   )
               ) latest_record ON tc.child_id = latest_record.child_id";

// ✅ no "latest_record.nutrition_id IS NOT NULL"
$where_clause = " WHERE 1=1";
$params = [];

// Add search filter if provided
if (!empty($search)) {
    $where_clause .= " AND (tc.first_name LIKE ? OR tc.last_name LIKE ? OR tb.zone_name LIKE ?)";
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
    mysqli_stmt_bind_param($count_stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total = $count_result->fetch_assoc()['total'];

// Get records with pagination
$records_query = "SELECT tc.child_id, tc.first_name, tc.last_name, tc.birthdate, tc.gender, tc.created_at,
                         tb.zone_name, 
                         latest_record.nutrition_id, latest_record.weight, latest_record.height, 
                         latest_record.bmi, latest_record.date_recorded, latest_record.status_name
                  " . $base_query . $where_clause . " 
                  ORDER BY latest_record.date_recorded DESC 
                  LIMIT ? OFFSET ?";

$records_stmt = mysqli_prepare($conn, $records_query);

if ($records_stmt === false) {
    die(json_encode(["error" => "Prepare failed for records query: " . mysqli_error($conn)]));
}

$bind_params = array_merge($params, [$limit, $offset]);
$types = str_repeat('s', count($params)) . 'ii';
mysqli_stmt_bind_param($records_stmt, $types, ...$bind_params);

mysqli_stmt_execute($records_stmt);
$result_records = mysqli_stmt_get_result($records_stmt);

$records = [];
if ($result_records) {
    while ($row = $result_records->fetch_assoc()) {
        $records[] = $row;
    }
}

// Close statements
mysqli_stmt_close($count_stmt);
mysqli_stmt_close($records_stmt);

echo json_encode([
    "records" => $records,
    "total" => $total,
    "page" => $page,
    "limit" => $limit,
    "totalPages" => ceil($total / $limit)
]);
?>