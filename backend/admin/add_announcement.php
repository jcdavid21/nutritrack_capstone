<?php 
    include_once "../config.php";
    include_once '../reports/audit_log.php';
    session_start();

    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["status" => "error", "message" => "User not authenticated."]);
        exit;
    }

    if(isset($_POST["title"]) && isset($_POST["zone_id"]) && isset($_POST["content"]) && isset($_POST["post_date"])) {
        $title = $_POST["title"];
        $zone_id = $_POST["zone_id"];
        $content = $_POST["content"];
        
        $posted_date = $_POST["post_date"];
        $user_id = $_SESSION["user_id"];
        
        if (empty($title)) {
            echo json_encode(["status" => "error", "message" => "Title is required."]);
            exit;
        }
        
        if (empty($content)) {
            echo json_encode(["status" => "error", "message" => "Content is required."]);
            exit;
        }
        
        if (empty($zone_id)) {
            echo json_encode(["status" => "error", "message" => "Zone selection is required."]);
            exit;
        }
        
        if (empty($posted_date)) {
            echo json_encode(["status" => "error", "message" => "Posted date is required."]);
            exit;
        }

        $zone_check = "SELECT zone_id FROM tbl_barangay WHERE zone_id = ?";
        $zone_stmt = $conn->prepare($zone_check);
        $zone_stmt->bind_param("i", $zone_id);
        $zone_stmt->execute();
        $zone_result = $zone_stmt->get_result();
        
        if ($zone_result->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Invalid zone selected."]);
            $zone_stmt->close();
            exit;
        }
        $zone_stmt->close();

        $thumbnail_filename = '*NULL*';
        $upload_success = true;
        $error_message = "";
        
        if (isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]["error"] == UPLOAD_ERR_OK) {
            $upload_dir = "../../assets/announcements/";
            
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    echo json_encode(["status" => "error", "message" => "Failed to create upload directory."]);
                    exit;
                }
            }
            
            $file_tmp = $_FILES["thumbnail"]["tmp_name"];
            $file_name = $_FILES["thumbnail"]["name"];
            $file_size = $_FILES["thumbnail"]["size"];
            
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            
            if (in_array($file_ext, $allowed_extensions)) {
                if ($file_size <= 5000000) {
                    $thumbnail_filename = "announcement_" . time() . "_" . uniqid() . "." . $file_ext;
                    $upload_path = $upload_dir . $thumbnail_filename;
                    
                    if (!move_uploaded_file($file_tmp, $upload_path)) {
                        $upload_success = false;
                        $error_message = "Failed to upload thumbnail.";
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

        // Insert new announcement into database
        $query = "INSERT INTO tbl_announcements (title, zone_id, content, post_date, user_id, img_content) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            // If file was uploaded, delete it since database operation failed
            if ($thumbnail_filename != '*NULL*' && file_exists($upload_dir . $thumbnail_filename)) {
                unlink($upload_dir . $thumbnail_filename);
            }
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("sissis", $title, $zone_id, $content, $posted_date, $user_id, $thumbnail_filename);

        if($stmt->execute()) {
            // Get the ID of the newly created announcement
            $new_announcement_id = $conn->insert_id;
            
            // Log the activity
            $activity_type = "Created new announcement with ID: " . $new_announcement_id;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode([
                "status" => "success", 
                "message" => "Announcement created successfully.",
                "announcement_id" => $new_announcement_id
            ]);
        } else {
            // If database insert fails and file was uploaded, delete the uploaded file
            if ($thumbnail_filename != '*NULL*' && file_exists($upload_dir . $thumbnail_filename)) {
                unlink($upload_dir . $thumbnail_filename);
            }
            echo json_encode(["status" => "error", "message" => "Failed to create announcement. Error: " . $stmt->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    }
    
    $conn->close();
?>