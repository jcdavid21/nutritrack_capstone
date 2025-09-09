<?php
    
    function audit_log($conn, $user_id, $activity_type, $log_date){
        $query = "INSERT INTO tbl_audit_log (user_id, activity_type, log_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $user_id, $activity_type, $log_date);
        return $stmt->execute();
    }

?>