<?php 
    include_once "../config.php";
    include_once '../reports/audit_log.php';
    session_start();

    if(isset($_POST["announcement_id"]) && isset($_POST["title"]) && isset($_POST["zone_id"]) && isset($_POST["content"]) && isset($_POST["post_date"])) {
        $ancId = $_POST["announcement_id"];
        $title = $_POST["title"];
        $zone_id = $_POST["zone_id"];
        date_default_timezone_set('Asia/Manila');
        $current_date_time = date('Y-m-d H:i:s');
        $posted_date = $current_date_time;
        $content = $_POST["content"];
        $user_id = $_SESSION["user_id"];
        
        // Handle thumbnail upload
        $thumbnail_filename = null;
        $upload_success = true;
        $error_message = "";
        
        if (isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]["error"] == UPLOAD_ERR_OK) {
            $upload_dir = "../../assets/announcements/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_tmp = $_FILES["thumbnail"]["tmp_name"];
            $file_name = $_FILES["thumbnail"]["name"];
            $file_size = $_FILES["thumbnail"]["size"];
            $file_error = $_FILES["thumbnail"]["error"];
            
            // Get file extension
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Allowed file types
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            
            // Check if file extension is allowed
            if (in_array($file_ext, $allowed_extensions)) {
                // Check file size (5MB max)
                if ($file_size <= 5000000) {
                    // Generate unique filename
                    $thumbnail_filename = "announcement_" . $ancId . "_" . time() . "." . $file_ext;
                    $upload_path = $upload_dir . $thumbnail_filename;
                    
                    // Move uploaded file
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
        }

        // Update announcement in the database
        if ($thumbnail_filename) {
            // Get current image to delete it later if update is successful
            $get_current_img = "SELECT img_content FROM tbl_announcements WHERE announcement_id = ?";
            $get_stmt = $conn->prepare($get_current_img);
            $get_stmt->bind_param("i", $ancId);
            $get_stmt->execute();
            $current_result = $get_stmt->get_result();
            $current_img = null;
            if ($current_result && $current_result->num_rows > 0) {
                $current_row = $current_result->fetch_assoc();
                $current_img = $current_row['img_content'];
            }
            $get_stmt->close();
            
            // Update with new thumbnail - FIXED: Corrected parameter binding
            $query = "UPDATE tbl_announcements SET title = ?, zone_id = ?, content = ?, post_date = ?, user_id = ?, img_content = ? WHERE announcement_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sissisi", $title, $zone_id, $content, $posted_date, $user_id, $thumbnail_filename, $ancId);
        } else {
            // Update without changing thumbnail - FIXED: Corrected parameter binding
            $query = "UPDATE tbl_announcements SET title = ?, zone_id = ?, content = ?, post_date = ?, user_id = ? WHERE announcement_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sissii", $title, $zone_id, $content, $posted_date, $user_id, $ancId);
        }

        if($stmt->execute()) {
            // Delete old image file if a new one was uploaded successfully
            if ($thumbnail_filename && isset($current_img) && !empty($current_img) && $current_img != '*NULL*') {
                $old_file_path = $upload_dir . $current_img;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            
            $activity_type = "Updated announcement with ID: " . $ancId;
            $log_date = date('Y-m-d H:i:s');
            audit_log($conn, $user_id, $activity_type, $log_date);

            echo json_encode(["status" => "success", "message" => "Announcement updated successfully."]);
        } else {
            // If database update fails and file was uploaded, delete the uploaded file
            if ($thumbnail_filename && file_exists($upload_dir . $thumbnail_filename)) {
                unlink($upload_dir . $thumbnail_filename);
            }
            echo json_encode(["status" => "error", "message" => "Failed to update announcement. Error: " . $stmt->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
    }
    
    $conn->close();
?>