<?php
include_once "../config.php";
include_once '../reports/audit_log.php';
session_start();

if (!isset($_SESSION["user_id"]) && $_SESSION["role_id"] != 2) {
    echo json_encode(["status" => "error", "message" => "User not authenticated."]);
    exit;
}

if (isset($_POST["record_id"]) && isset($_POST["weight"]) && isset($_POST["height"]) && isset($_POST["status_id"]) && isset($_POST["bmi"]) && isset($_POST["date_recorded"])) {
    $record_id = $_POST["record_id"];
    $weight = $_POST["weight"];
    $height = $_POST["height"];
    $status_id = $_POST["status_id"];
    $bmi = $_POST["bmi"];
    date_default_timezone_set('Asia/Manila');
    $current_date_time = date('Y-m-d H:i:s');
    $user_id = $_SESSION["user_id"];
    $date_recorded = $_POST["date_recorded"];

    // Update nutrition record
    $update_query = "UPDATE tbl_nutrition_record 
                         SET date_recorded = ?, weight = ?, height = ?, status_id = ?, bmi = ?, recorded_by = ?
                         WHERE nutrition_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param(
        $stmt,
        "sddidii",
        $current_date_time,
        $weight,
        $height,
        $status_id,
        $bmi,
        $user_id,
        $record_id
    );


    if (mysqli_stmt_execute($stmt)) {
        date_default_timezone_set('Asia/Manila');
        $activity_type = "Updated nutrition record with ID: " . $record_id;
        $log_date = date('Y-m-d H:i:s');
        audit_log($conn, $user_id, $activity_type, $log_date);

        echo json_encode(["status" => "success", "message" => "Nutrition record updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update nutrition record."]);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["status" => "error", "message" => "Error processing request. All fields are required."]);
}
