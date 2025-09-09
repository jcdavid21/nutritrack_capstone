<?php 

    include "../config.php";
    include '../reports/audit_log.php';
    session_start();

    if(isset($_POST["announcement_id"])) {
        $ancId = $_POST["announcement_id"];
        $user_id = $_SESSION["user_id"];

        // Check if announcement exists
        $check_query = "SELECT * FROM tbl_announcements WHERE announcement_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $ancId);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Announcement not found."]);
            exit();
        }
        $check_stmt->close();

        // Proceed to delete the announcement
        $delete_query = "DELETE FROM tbl_announcements WHERE announcement_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $ancId);

        if($delete_stmt->execute()) {
            // Log the deletion activity
            date_default_timezone_set('Asia/Manila');
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, 'Deleted Announcement ID ' . $ancId, $log_date);

            echo json_encode(["status" => "success", "message" => "Announcement deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete announcement."]);
        }

        $delete_stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Announcement ID is required."]);
    }
?>