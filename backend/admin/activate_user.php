<?php 
    include_once "../config.php";
    include_once '../reports/audit_log.php';
    session_start();

    if(isset($_POST["user_id"])) {
        $user_id = $_POST["user_id"];
        
        if (!isset($_SESSION["user_id"])) {
            echo json_encode(["status" => "error", "message" => "User not authenticated."]);
            exit;
        }

        $admin_id = $_SESSION["user_id"];

        // Check if user exists and is inactive
        $check_query = "SELECT * FROM tbl_user WHERE user_id = ? AND status = 'inactive'";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "User not found or already active."]);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        // Activate user
        $activate_query = "UPDATE tbl_user SET status = 'active' WHERE user_id = ?";
        $activate_stmt = $conn->prepare($activate_query);
        $activate_stmt->bind_param("i", $user_id);
        
        if ($activate_stmt->execute()) {
            // Log the activity
            date_default_timezone_set('Asia/Manila');
            $activity_type = "Activated user account (User ID: $user_id)";
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $admin_id, $activity_type, $log_date);

            echo json_encode(["status" => "success", "message" => "User account activated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to activate user account."]);
        }
        
        $activate_stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "User ID is required."]);
    }
?>