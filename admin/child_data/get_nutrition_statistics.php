<?php
include "../../backend/config.php";

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$zone = isset($_GET['zone']) ? trim($_GET['zone']) : '';
$startDate = isset($_GET['startDate']) ? trim($_GET['startDate']) : '';
$endDate = isset($_GET['endDate']) ? trim($_GET['endDate']) : '';

// Build WHERE clause for non-date filters (search and zone only)
$baseWhereConditions = [];
$baseParams = [];
$baseTypes = '';

if (!empty($search)) {
    $baseWhereConditions[] = "(tc.first_name LIKE ? OR tc.last_name LIKE ?)";
    $searchParam = "{$search}%";
    $baseParams[] = $searchParam;
    $baseParams[] = $searchParam;
    $baseTypes .= 'ss';
}

if (!empty($zone)) {
    $baseWhereConditions[] = "tc.zone_id = ?";
    $baseParams[] = $zone;
    $baseTypes .= 'i';
}

// Helper function to execute query safely
function executeQuery($conn, $query, $params = [], $types = '') {
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($conn));
            return null;
        }
        
        if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
            error_log("Bind param failed: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return null;
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Execute failed: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return null;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $data = $result ? $result->fetch_assoc() : null;
        mysqli_stmt_close($stmt);
        return $data;
    } else {
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log("Query failed: " . mysqli_error($conn));
            return null;
        }
        return $result->fetch_assoc();
    }
}

// Get total unique children with nutrition records (with date filtering)
$total_children_query = "SELECT COUNT(DISTINCT tc.child_id) as total
                        FROM tbl_child tc
                        LEFT JOIN (
                            SELECT nr1.child_id, nr1.date_recorded
                            FROM tbl_nutrition_record nr1
                            WHERE nr1.date_recorded = (
                                SELECT MAX(nr2.date_recorded) 
                                FROM tbl_nutrition_record nr2 
                                WHERE nr2.child_id = nr1.child_id
                            )
                        ) latest_record ON tc.child_id = latest_record.child_id";

// Build complete WHERE clause including date filters
$totalWhereConditions = $baseWhereConditions;
$totalParams = $baseParams;
$totalTypes = $baseTypes;

if (!empty($startDate)) {
    $totalWhereConditions[] = "DATE(latest_record.date_recorded) >= ?";
    $totalParams[] = $startDate;
    $totalTypes .= 's';
}

if (!empty($endDate)) {
    $totalWhereConditions[] = "DATE(latest_record.date_recorded) <= ?";
    $totalParams[] = $endDate;
    $totalTypes .= 's';
}

if (!empty($totalWhereConditions)) {
    $total_children_query .= " WHERE " . implode(" AND ", $totalWhereConditions);
}

$total_result = executeQuery($conn, $total_children_query, $totalParams, $totalTypes);
$total_children = $total_result ? (int)$total_result['total'] : 0;

// Build WHERE clause for status-specific queries
$statusWhereConditions = $baseWhereConditions;
$statusParams = $baseParams;
$statusTypes = $baseTypes;

// Add date filters for latest record if specified
$dateFilter = '';
if (!empty($startDate) && !empty($endDate)) {
    $dateFilter = " AND DATE(latest_record.date_recorded) BETWEEN ? AND ?";
    $statusParams[] = $startDate;
    $statusParams[] = $endDate;
    $statusTypes .= 'ss';
} elseif (!empty($startDate)) {
    $dateFilter = " AND DATE(latest_record.date_recorded) >= ?";
    $statusParams[] = $startDate;
    $statusTypes .= 's';
} elseif (!empty($endDate)) {
    $dateFilter = " AND DATE(latest_record.date_recorded) <= ?";
    $statusParams[] = $endDate;
    $statusTypes .= 's';
}

// Get children with normal status (latest record)
$normal_status_query = "SELECT COUNT(DISTINCT tc.child_id) as normal_count
                       FROM tbl_child tc
                       INNER JOIN (
                           SELECT nr1.child_id, nr1.status_id, nr1.date_recorded
                           FROM tbl_nutrition_record nr1
                           WHERE nr1.date_recorded = (
                               SELECT MAX(nr2.date_recorded) 
                               FROM tbl_nutrition_record nr2 
                               WHERE nr2.child_id = nr1.child_id
                           )
                       ) latest_record ON tc.child_id = latest_record.child_id
                       INNER JOIN tbl_nutrition_status tns ON latest_record.status_id = tns.status_id
                       WHERE LOWER(tns.status_name) LIKE '%normal%'" . 
                       (!empty($statusWhereConditions) ? " AND " . implode(" AND ", $statusWhereConditions) : "") . 
                       $dateFilter;

$normal_result = executeQuery($conn, $normal_status_query, $statusParams, $statusTypes);
$normal_status = $normal_result ? (int)$normal_result['normal_count'] : 0;

// Get children with underweight status (latest record)
$underweight_query = "SELECT COUNT(DISTINCT tc.child_id) as underweight_count
                     FROM tbl_child tc
                     INNER JOIN (
                         SELECT nr1.child_id, nr1.status_id, nr1.date_recorded
                         FROM tbl_nutrition_record nr1
                         WHERE nr1.date_recorded = (
                             SELECT MAX(nr2.date_recorded) 
                             FROM tbl_nutrition_record nr2 
                             WHERE nr2.child_id = nr1.child_id
                         )
                     ) latest_record ON tc.child_id = latest_record.child_id
                     INNER JOIN tbl_nutrition_status tns ON latest_record.status_id = tns.status_id
                     WHERE tns.status_name = 'Underweight'" . 
                     (!empty($statusWhereConditions) ? " AND " . implode(" AND ", $statusWhereConditions) : "") . 
                     $dateFilter;

$underweight_result = executeQuery($conn, $underweight_query, $statusParams, $statusTypes);
$underweight = $underweight_result ? (int)$underweight_result['underweight_count'] : 0;

// Get recent records (this month) - no filters applied as it's a general statistic
$recent_records_query = "SELECT COUNT(*) as recent_count
                        FROM tbl_nutrition_record nr
                        WHERE MONTH(nr.date_recorded) = MONTH(CURRENT_DATE())
                        AND YEAR(nr.date_recorded) = YEAR(CURRENT_DATE())";

$recent_result = executeQuery($conn, $recent_records_query);
$recent_records = $recent_result ? (int)$recent_result['recent_count'] : 0;

// Get children with severe underweight status (latest record)
$severe_underweight_query = "SELECT COUNT(DISTINCT tc.child_id) as severe_underweight_count
                     FROM tbl_child tc
                     INNER JOIN (
                         SELECT nr1.child_id, nr1.status_id, nr1.date_recorded
                         FROM tbl_nutrition_record nr1
                         WHERE nr1.date_recorded = (
                             SELECT MAX(nr2.date_recorded) 
                             FROM tbl_nutrition_record nr2 
                             WHERE nr2.child_id = nr1.child_id
                         )
                     ) latest_record ON tc.child_id = latest_record.child_id
                     INNER JOIN tbl_nutrition_status tns ON latest_record.status_id = tns.status_id
                     WHERE LOWER(tns.status_name) LIKE '%severely underweight%'" . 
                     (!empty($statusWhereConditions) ? " AND " . implode(" AND ", $statusWhereConditions) : "") . 
                     $dateFilter;

$severe_underweight_result = executeQuery($conn, $severe_underweight_query, $statusParams, $statusTypes);
$severe_underweight = $severe_underweight_result ? (int)$severe_underweight_result['severe_underweight_count'] : 0;

// Get children with overweight status (latest record)
$overweight_query = "SELECT COUNT(DISTINCT tc.child_id) as overweight_count
                     FROM tbl_child tc
                     INNER JOIN (
                         SELECT nr1.child_id, nr1.status_id, nr1.date_recorded
                         FROM tbl_nutrition_record nr1
                         WHERE nr1.date_recorded = (
                             SELECT MAX(nr2.date_recorded) 
                             FROM tbl_nutrition_record nr2 
                             WHERE nr2.child_id = nr1.child_id
                         )
                     ) latest_record ON tc.child_id = latest_record.child_id
                     INNER JOIN tbl_nutrition_status tns ON latest_record.status_id = tns.status_id
                     WHERE LOWER(tns.status_name) LIKE '%overweight%'" . 
                     (!empty($statusWhereConditions) ? " AND " . implode(" AND ", $statusWhereConditions) : "") . 
                     $dateFilter;

$overweight_result = executeQuery($conn, $overweight_query, $statusParams, $statusTypes);
$overweight = $overweight_result ? (int)$overweight_result['overweight_count'] : 0;


$no_records_query = "
    SELECT COUNT(DISTINCT tc.child_id) as no_record_count
    FROM tbl_child tc
    LEFT JOIN (
        SELECT nr1.child_id, nr1.date_recorded
        FROM tbl_nutrition_record nr1
        WHERE nr1.date_recorded = (
            SELECT MAX(nr2.date_recorded)
            FROM tbl_nutrition_record nr2
            WHERE nr2.child_id = nr1.child_id
        )
    ) latest_record ON tc.child_id = latest_record.child_id
";

// build WHERE clause
$noRecordsWhere = $baseWhereConditions; 
$noRecordsParams = $baseParams;
$noRecordsTypes = $baseTypes;

// child must have **no nutrition record**
$noRecordsWhere[] = "latest_record.child_id IS NULL";

// date filters (same as total_children)
if (!empty($startDate)) {
    $noRecordsWhere[] = "DATE(latest_record.date_recorded) >= ?";
    $noRecordsParams[] = $startDate;
    $noRecordsTypes .= 's';
}

if (!empty($endDate)) {
    $noRecordsWhere[] = "DATE(latest_record.date_recorded) <= ?";
    $noRecordsParams[] = $endDate;
    $noRecordsTypes .= 's';
}

if (!empty($noRecordsWhere)) {
    $no_records_query .= " WHERE " . implode(" AND ", $noRecordsWhere);
}

$no_records_result = executeQuery($conn, $no_records_query, $noRecordsParams, $noRecordsTypes);
$no_records = $no_records_result ? (int)$no_records_result['no_record_count'] : 0;


// Set proper content type header
header('Content-Type: application/json');

echo json_encode([
    "total_children" => $total_children,
    "normal_status" => $normal_status,
    "underweight" => $underweight,
    "recent_records" => $recent_records,
    "severe_underweight" => $severe_underweight,
    "overweight" => $overweight,
    "no_records" => $no_records
]);
?>