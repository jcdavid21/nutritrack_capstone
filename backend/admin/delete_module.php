<?php
    include_once "../config.php";
    include_once '../reports/audit_log.php';
    session_start();

    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["status" => "error", "message" => "User not authenticated."]);
        exit;
    }

    if(isset($_POST["module_id"])){
        $module_id = $_POST["module_id"];
        $user_id = $_SESSION["user_id"];

        // Delete module from database
        $query = "DELETE FROM tbl_modules WHERE module_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $module_id);

        if($stmt->execute()) {
            $activity_type = "Deleted module with ID: " . $module_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode(["status" => "success", "message" => "Module deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete module. Error: " . $stmt->error]);
        }

        $stmt->close();
    }
?>