<?php

session_start();
include_once "../reports/audit_log.php";
include_once '../config.php';

if(isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check if username exists
    $query = "SELECT * FROM tbl_user WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if(password_verify($password, $user['password'])) {
            // Log the activity
            date_default_timezone_set('Asia/Manila');
            $user_id = $user['user_id'];
            $activity_type = "User logged in";
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            $_SESSION["user_id"] = $user['user_id'];
            $_SESSION["role_id"] = $user['role_id'];
            echo json_encode(["status" => "success", "message" => "Login successful.", "role_id" => $user['role_id']]);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password."]);
            exit();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email not found."]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
}

?>