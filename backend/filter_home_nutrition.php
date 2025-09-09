<?php
include_once("config.php");
header('Content-Type: application/json');

$year = $_GET['year'] ?? null;
$zone_id = $_GET['zone_id'] ?? null;

$query = "SELECT ns.status_name, COUNT(*) as count 
          FROM (
              SELECT DISTINCT nr1.child_id, nr1.status_id
              FROM tbl_nutritrion_record nr1
              INNER JOIN (
                  SELECT child_id, MAX(date_recorded) as max_date
                  FROM tbl_nutritrion_record
                  GROUP BY child_id
              ) nr2 ON nr1.child_id = nr2.child_id AND nr1.date_recorded = nr2.max_date
              JOIN tbl_child c ON nr1.child_id = c.child_id
              WHERE 1=1";

$params = [];
$types = "";

if ($year) {
    $query .= " AND YEAR(nr1.date_recorded) = ?";
    $params[] = $year;
    $types .= "i";
}

if ($zone_id) {
    $query .= " AND c.zone_id = ?";
    $params[] = $zone_id;
    $types .= "i";
}

$query .= "          ) latest_records
          JOIN tbl_nutrition_status ns ON latest_records.status_id = ns.status_id 
          GROUP BY ns.status_name";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>