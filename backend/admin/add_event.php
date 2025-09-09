<?php 
    include "../config.php";
    include '../reports/audit_log.php';
    session_start();

    if(isset($_POST["title"]) && isset($_POST["zone_id"]) && isset($_POST["description"]) && isset($_POST["event_date"])) {
        $title = $_POST["title"];
        $zone_id = $_POST["zone_id"];
        date_default_timezone_set('Asia/Manila');
        $current_date_time = date('Y-m-d H:i:s');
        $event_date = $_POST["event_date"];
        $description = $_POST["description"];
        $user_id = $_SESSION["user_id"];



        $insert_query = "INSERT INTO tbl_events (title, zone_id, description, event_date) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("siss", $title, $zone_id, $description, $event_date);
        if($insert_stmt->execute()) {
            $eventId = $insert_stmt->insert_id;
            audit_log($conn, $user_id, 'Added Event ID ' . $eventId, $current_date_time);
            echo json_encode(["status" => "success", "message" => "Event added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add event."]);
        }
    }
?>