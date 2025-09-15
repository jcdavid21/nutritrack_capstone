<?php
    include_once "../config.php";
    include_once '../reports/audit_log.php';
    session_start();

    if(!isset($_SESSION["user_id"]) && $_SESSION["role_id"] != 2) {
        echo json_encode(["status" => "error", "message" => "User not authenticated."]);
        exit;
    }

    if(isset($_POST["record_id"])) {
        $record_id = $_POST["record_id"];
        $user_id = $_SESSION["user_id"];

        // Validate inputs
        if (empty($record_id)) {
            echo json_encode(["status" => "error", "message" => "Record ID is required."]);
            exit;
        }

        // Delete measurement record
        $delete_query = "DELETE FROM tbl_nutrition_record WHERE nutrition_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $record_id);

        if (mysqli_stmt_execute($stmt)) {
            date_default_timezone_set('Asia/Manila');
            $activity_type = "Deleted nutrition record with ID: " . $record_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode(["status" => "success", "message" => "Nutrition record deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete nutrition record."]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Error processing request. Record ID is required."]);
    }

?>