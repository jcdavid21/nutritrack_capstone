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

    if(isset($_POST["parent_name"]) && isset($_POST["parent_contact"]) && isset($_POST["parent_occupation"]) && isset($_POST["parent_relationship"]) && isset($_POST["child_id"]))
    {
        $parent_name = $_POST["parent_name"];
        $parent_contact = $_POST["parent_contact"];
        $parent_occupation = $_POST["parent_occupation"];
        $parent_relationship = $_POST["parent_relationship"];
        $child_id = $_POST["child_id"];

        // Validate and sanitize input
        if(empty($parent_name) || empty($parent_contact) || empty($parent_occupation) || empty($parent_relationship) || empty($child_id)) {
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            exit;
        }

        if(!preg_match("/^[0-9]{11}$/", $parent_contact)) {
            echo json_encode(["status" => "error", "message" => "Contact number must be exactly 11 digits."]);
            exit;
        }

        $check_parent_relation = "SELECT * FROM tbl_parent_details WHERE child_id = ? AND relationship = ?";
        $relation_stmt = $conn->prepare($check_parent_relation);
        $relation_stmt->bind_param("is", $child_id, $parent_relationship);
        $relation_stmt->execute();
        $relation_result = $relation_stmt->get_result();
        if ($relation_result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Parent with this relationship already exists for the child."]);
            $relation_stmt->close();
            exit;
        }
        

        // Insert parent details into the database
        $stmt = $conn->prepare("INSERT INTO tbl_parent_details (parent_name, contact, occupation, relationship, child_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $parent_name, $parent_contact, $parent_occupation, $parent_relationship, $child_id);

        if($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Parent details added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add parent details."]);
        }

        $stmt->close();
    }else{
        echo json_encode(["status" => "error", "message" => "You must fill out all fields."]);
    }
?>