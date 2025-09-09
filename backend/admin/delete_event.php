<?php 

    include "../config.php";
    include '../reports/audit_log.php';
    session_start();

    if(isset($_POST["event_id"])) {
        $eventId = $_POST["event_id"];
        $user_id = $_SESSION["user_id"];

        // Check if event exists
        $check_query = "SELECT * FROM tbl_events WHERE event_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $eventId);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Event not found."]);
            exit();
        }
        $check_stmt->close();

        // Proceed to delete the event
        $delete_query = "DELETE FROM tbl_events WHERE event_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $eventId);

        if($delete_stmt->execute()) {
            // Log the deletion activity
            date_default_timezone_set('Asia/Manila');
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, 'Deleted Event ID ' . $eventId, $log_date);

            echo json_encode(["status" => "success", "message" => "Event deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete event."]);
        }

        $delete_stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Event ID is required."]);
    }
?>