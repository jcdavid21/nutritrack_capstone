<?php 
    include_once "../config.php";
    include_once '../reports/audit_log.php';
    session_start();

    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["status" => "error", "message" => "User not authenticated."]);
        exit;
    }

    // Check if the user has the required role
    if ($_SESSION["role_id"] != 2) {
        echo json_encode(["status" => "error", "message" => "User does not have permission to add children."]);
        exit;
    }


    if(isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["birthdate"]) && isset($_POST["zone_id"]) && isset($_POST["gender"])){
        $first_name = trim($_POST["first_name"]);
        $last_name = trim($_POST["last_name"]);
        $birthdate = trim($_POST["birthdate"]);
        $zone_id = trim($_POST["zone_id"]);
        $gender = trim($_POST["gender"]);
        $user_id = $_SESSION["user_id"];

        date_default_timezone_set('Asia/Manila');
        $created_at = date('Y-m-d H:i:s');

        // Validate inputs
        if (empty($first_name)) {
            echo json_encode(["status" => "error", "message" => "First name is required."]);
            exit;
        }

        if (empty($last_name)) {
            echo json_encode(["status" => "error", "message" => "Last name is required."]);
            exit;
        }

        if (empty($birthdate)) {
            echo json_encode(["status" => "error", "message" => "Birthdate is required."]);
            exit;
        }

        if (empty($zone_id)) {
            echo json_encode(["status" => "error", "message" => "Zone ID is required."]);
            exit;
        }

        if (empty($gender)) {
            echo json_encode(["status" => "error", "message" => "Gender is required."]);
            exit;
        }

        // Insert the child record into the database
        $stmt = $conn->prepare("INSERT INTO tbl_child (first_name, last_name, birthdate, zone_id, gender, registered_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $birthdate, $zone_id, $gender, $user_id, $created_at);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Child added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add child."]);
        }

        $stmt->close();
    }
?>