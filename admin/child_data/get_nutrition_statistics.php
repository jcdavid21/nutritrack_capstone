<?php
include "../../backend/config.php";

// Get total unique children with nutrition records
$total_children_query = "SELECT COUNT(DISTINCT tc.child_id) as total
                        FROM tbl_child tc
                        INNER JOIN tbl_nutritrion_record nr ON tc.child_id = nr.child_id";
$total_result = mysqli_query($conn, $total_children_query);
$total_children = $total_result ? $total_result->fetch_assoc()['total'] : 0;

// Get children with normal status (latest record)
$normal_status_query = "SELECT COUNT(DISTINCT tc.child_id) as normal_count
                       FROM tbl_child tc
                       INNER JOIN (
                           SELECT nr1.child_id, nr1.status_id
                           FROM tbl_nutritrion_record nr1
                           WHERE nr1.date_recorded = (
                               SELECT MAX(nr2.date_recorded) 
                               FROM tbl_nutritrion_record nr2 
                               WHERE nr2.child_id = nr1.child_id
                           )
                       ) latest_record ON tc.child_id = latest_record.child_id
                       INNER JOIN tbl_nutrition_status tns ON latest_record.status_id = tns.status_id
                       WHERE LOWER(tns.status_name) LIKE '%normal%'";
$normal_result = mysqli_query($conn, $normal_status_query);
$normal_status = $normal_result ? $normal_result->fetch_assoc()['normal_count'] : 0;

// Get children with underweight status (latest record)
$underweight_query = "SELECT COUNT(DISTINCT tc.child_id) as underweight_count
                     FROM tbl_child tc
                     INNER JOIN (
                         SELECT nr1.child_id, nr1.status_id
                         FROM tbl_nutritrion_record nr1
                         WHERE nr1.date_recorded = (
                             SELECT MAX(nr2.date_recorded) 
                             FROM tbl_nutritrion_record nr2 
                             WHERE nr2.child_id = nr1.child_id
                         )
                     ) latest_record ON tc.child_id = latest_record.child_id
                     INNER JOIN tbl_nutrition_status tns ON latest_record.status_id = tns.status_id
                     WHERE LOWER(tns.status_name) LIKE '%underweight%'";
$underweight_result = mysqli_query($conn, $underweight_query);
$underweight = $underweight_result ? $underweight_result->fetch_assoc()['underweight_count'] : 0;

// Get recent records (this month)
$recent_records_query = "SELECT COUNT(*) as recent_count
                        FROM tbl_nutritrion_record nr
                        WHERE MONTH(nr.date_recorded) = MONTH(CURRENT_DATE())
                        AND YEAR(nr.date_recorded) = YEAR(CURRENT_DATE())";
$recent_result = mysqli_query($conn, $recent_records_query);
$recent_records = $recent_result ? $recent_result->fetch_assoc()['recent_count'] : 0;

echo json_encode([
    "total_children" => $total_children,
    "normal_status" => $normal_status,
    "underweight" => $underweight,
    "recent_records" => $recent_records
]);
?>