<?php 
    include_once "../config.php";
    include_once '../reports/audit_log.php';
    session_start();


    if(!isset($_SESSION["user_id"]) && $_SESSION["role_id"] != 2) {
        echo json_encode(["status" => "error", "message" => "User not authenticated."]);
        exit;
    }

    if(isset($_POST["child_id"]) && isset($_POST["weight"]) && isset($_POST["height"]) && isset($_POST["status_id"]) && isset($_POST["bmi"]) && isset($_POST["date_recorded"])) {
        $child_id = $_POST["child_id"];
        $weight = $_POST["weight"];
        $height = $_POST["height"];
        $status_id = $_POST["status_id"];
        $bmi = $_POST["bmi"];
        date_default_timezone_set('Asia/Manila');
        $current_date_time = date('Y-m-d H:i:s');
        $user_id = $_SESSION["user_id"];
        $date_recorded = $_POST["date_recorded"];

        // Validate inputs
        if (empty($child_id) || empty($date_recorded) || empty($weight) || empty($height) || empty($status_id) || empty($bmi)) {
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            exit;
        }

        // Insert nutrition record
        $insert_query = "INSERT INTO tbl_nutritrion_record (child_id, date_recorded, weight, height, status_id, bmi, recorded_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param(
            $stmt,
            "isddidi",
            $child_id,
            $current_date_time,
            $weight,
            $height,
            $status_id,
            $bmi,
            $user_id
        );

        if (mysqli_stmt_execute($stmt)) {
            date_default_timezone_set('Asia/Manila');
            $activity_type = "Added nutrition record for child ID: " . $child_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode(["status" => "success", "message" => "Nutrition record added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add nutrition record."]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Error processing request. All fields are required."]);
    }
?>