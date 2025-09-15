<?php 
    include "../config.php";
    include '../reports/audit_log.php';
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

    if(isset($_POST["parent_name"]) && isset($_POST["parent_contact"]) && isset($_POST["parent_occupation"]) && isset($_POST["parent_relationship"]) && isset($_POST["parent_id"]))
    {
        $parent_name = $_POST["parent_name"];
        $parent_contact = $_POST["parent_contact"];
        $parent_occupation = $_POST["parent_occupation"];
        $parent_relationship = $_POST["parent_relationship"];
        $parent_id = $_POST["parent_id"];
        $user_id = $_SESSION["user_id"];

        // Validate and sanitize input
        if(empty($parent_name) || empty($parent_contact) || empty($parent_occupation) || empty($parent_relationship) || empty($parent_id)) {
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            exit;
        }

        if(!preg_match("/^[0-9]{11}$/", $parent_contact)) {
            echo json_encode(["status" => "error", "message" => "Contact number must be exactly 11 digits."]);
            exit;
        }

        $current_relation_ship = "SELECT relationship FROM tbl_parent_details WHERE parent_id = ?";
        $current_stmt = $conn->prepare($current_relation_ship);
        $current_stmt->bind_param("i", $parent_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        if ($current_result->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Parent record not found."]);
            $current_stmt->close();
            exit;
        }
        $current_row = $current_result->fetch_assoc();
        $current_relationship = $current_row['relationship'];
        $current_stmt->close();

        if ($current_relationship !== $parent_relationship) {
            $check_parent_relation = "SELECT * FROM tbl_parent_details WHERE relationship = ? AND parent_id != ?";
            $relation_stmt = $conn->prepare($check_parent_relation);
            $relation_stmt->bind_param("si", $parent_relationship, $parent_id);
            $relation_stmt->execute();
            $relation_result = $relation_stmt->get_result();
            if ($relation_result->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "Another parent with this relationship already exists for the child."]);
                $relation_stmt->close();
                exit;
            }
            $relation_stmt->close();
        }
        
        // Update parent details in the database
        $stmt = $conn->prepare("UPDATE tbl_parent_details SET parent_name = ?, contact = ?, occupation = ?, relationship = ? WHERE parent_id = ?");
        $stmt->bind_param("ssssi", $parent_name, $parent_contact, $parent_occupation, $parent_relationship, $parent_id);

        if($stmt->execute()) {
            // Log the activity
            date_default_timezone_set('Asia/Manila');
            $activity_type = "Updated parent details (Parent ID: $parent_id)";
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode(["status" => "success", "message" => "Parent details updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update parent details."]);
        }

        $stmt->close();
    }else{
        echo json_encode(["status" => "error", "message" => "You must fill out all fields."]);
    }
?>