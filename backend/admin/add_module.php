<?php 
    include "../config.php";
    include '../reports/audit_log.php';
    session_start();

    if(!isset($_SESSION["user_id"])) {
        echo json_encode(["status" => "error", "message" => "User not logged in."]);
        exit();
    }

    if(isset($_POST["module_title"]) && isset($_POST["module_content"]) )
    {
        $user_id = $_SESSION["user_id"];
        date_default_timezone_set('Asia/Manila');
        $current_date_time = date('Y-m-d H:i:s');
        $module_title = $_POST["module_title"];
        $module_content = $_POST["module_content"];

        $thumbnail_filename = '*NULL*';
        $upload_success = true;
        $error_message = "";

        if (isset($_FILES["module_thumbnail"]) && $_FILES["module_thumbnail"]["error"] == UPLOAD_ERR_OK) {
            $upload_dir = "../../assets/modules/";
            
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    echo json_encode(["status" => "error", "message" => "Failed to create upload directory."]);
                    exit;
                }
            }

            $file_tmp = $_FILES["module_thumbnail"]["tmp_name"];
            $file_name = $_FILES["module_thumbnail"]["name"];
            $file_size = $_FILES["module_thumbnail"]["size"];
            
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            
            if (in_array($file_ext, $allowed_extensions)) {
                if ($file_size <= 5000000) {
                    $thumbnail_filename = "module_" . time() . "_" . uniqid() . "." . $file_ext;
                    $upload_path = $upload_dir . $thumbnail_filename;
                    
                    if (!move_uploaded_file($file_tmp, $upload_path)) {
                        $upload_success = false;
                        $error_message = "Failed to upload module thumbnail.";
                    }
                } else {
                    $upload_success = false;
                    $error_message = "File size too large. Maximum 5MB allowed.";
                }
            } else {
                $upload_success = false;
                $error_message = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
            }
            
            // If upload failed, return error
            if (!$upload_success) {
                echo json_encode(["status" => "error", "message" => $error_message]);
                exit;
            }
        } else if (isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]["error"] != UPLOAD_ERR_NO_FILE) {
            // Handle other upload errors
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit.',
                UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory found.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
            ];
            
            $error_code = $_FILES["thumbnail"]["error"];
            $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown upload error.';
            
            echo json_encode(["status" => "error", "message" => $error_message]);
            exit;
        }

        $query = "INSERT INTO tbl_modules (module_title, module_content, module_thumbnail, created_by, posted_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssds", $module_title, $module_content, $thumbnail_filename, $user_id, $current_date_time);
        if($stmt->execute()) {
            $moduleId = $stmt->insert_id;
            audit_log($conn, $user_id, 'Added Module ID ' . $moduleId, $current_date_time);
            echo json_encode(["status" => "success", "message" => "Module added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add module."]);
        }
    }

?>