<?php 
    include "../config.php";
    include '../reports/audit_log.php';
    session_start();

    if(isset($_POST["event_id"]) && isset($_POST["title"]) && isset($_POST["zone_id"]) && isset($_POST["description"]) && isset($_POST["event_date"])) {
        $eventId = $_POST["event_id"];
        $title = $_POST["title"];
        $zone_id = $_POST["zone_id"];
        date_default_timezone_set('Asia/Manila');
        $current_date_time = date('Y-m-d H:i:s');
        $event_date = $_POST["event_date"];
        $description = $_POST["description"];
        $user_id = $_SESSION["user_id"];

        $update_query = "UPDATE tbl_events SET title = ?, zone_id = ?, description = ?, event_date = ? WHERE event_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sissi", $title, $zone_id, $description, $event_date, $eventId);
        if($update_stmt->execute()) {
            // Log the update action
            audit_log($conn, $user_id, 'Updated Event ID ' . $eventId, $current_date_time);
            echo json_encode(["status" => "success", "message" => "Event updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update event."]);
        }
    }
?>