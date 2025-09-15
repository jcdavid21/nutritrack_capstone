<?php
   include "../config.php";
    include '../reports/audit_log.php';
    session_start();

    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["status" => "error", "message" => "User not authenticated."]);
        exit;
    }

    if(isset($_POST["parent_id"])) {
        $parent_id = $_POST["parent_id"];
        $user_id = $_SESSION["user_id"];

        // Check if parent record exists
        $check_query = "SELECT * FROM tbl_parent_details WHERE parent_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $parent_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Parent record not found."]);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        // Delete parent record
        $delete_query = "DELETE FROM tbl_parent_details WHERE parent_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $parent_id);

        if ($delete_stmt->execute()) {
            // Log the activity
            date_default_timezone_set('Asia/Manila');
            $activity_type = "Deleted parent record (Parent ID: $parent_id)";
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode(["status" => "success", "message" => "Parent record deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete parent record."]);
        }

        $delete_stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Parent ID is required."]);
    }
?>