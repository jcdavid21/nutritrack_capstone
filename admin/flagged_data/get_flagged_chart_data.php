<?php
header('Content-Type: application/json');
include "../../backend/config.php";

try {
    $type = isset($_GET['type']) ? $_GET['type'] : 'status';
    
    switch ($type) {
        case 'status':
            // Status distribution
            $active_sql = "SELECT COUNT(*) as count FROM tbl_flagged_record WHERE flagged_status = 'Active'";
            $active_result = $conn->query($active_sql);
            $active_count = $active_result->fetch_assoc()['count'];
            
            $review_sql = "SELECT COUNT(*) as count FROM tbl_flagged_record WHERE flagged_status = 'Under Review'";
            $review_result = $conn->query($review_sql);
            $review_count = $review_result->fetch_assoc()['count'];
            
            $resolved_sql = "SELECT COUNT(*) as count FROM tbl_flagged_record WHERE flagged_status = 'Resolved'";
            $resolved_result = $conn->query($resolved_sql);
            $resolved_count = $resolved_result->fetch_assoc()['count'];
            
            echo json_encode([
                'status' => 'success',
                'active' => $active_count,
                'under_review' => $review_count,
                'resolved' => $resolved_count
            ]);
            break;
            
        case 'issue':
            // Issue type distribution
            $sql = "SELECT issue_type, COUNT(*) as count 
                    FROM tbl_flagged_record 
                    GROUP BY issue_type 
                    ORDER BY count DESC";
            $result = $conn->query($sql);
            
            $labels = [];
            $values = [];
            
            while ($row = $result->fetch_assoc()) {
                $labels[] = $row['issue_type'];
                $values[] = (int)$row['count'];
            }
            
            echo json_encode([
                'status' => 'success',
                'labels' => $labels,
                'values' => $values
            ]);
            break;
            
        case 'trend':
            // Trend over last 30 days
            $sql = "SELECT DATE(date_flagged) as flag_date, COUNT(*) as count
                    FROM tbl_flagged_record 
                    WHERE date_flagged >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(date_flagged)
                    ORDER BY flag_date ASC";
            $result = $conn->query($sql);
            
            $labels = [];
            $values = [];
            
            // Create array for all 30 days with 0 as default
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $formatted_date = date('M j', strtotime($date));
                $labels[] = $formatted_date;
                $values[] = 0;
            }
            
            // Fill in actual data
            while ($row = $result->fetch_assoc()) {
                $date = $row['flag_date'];
                $formatted_date = date('M j', strtotime($date));
                $index = array_search($formatted_date, $labels);
                if ($index !== false) {
                    $values[$index] = (int)$row['count'];
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'labels' => $labels,
                'values' => $values
            ]);
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid chart type'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch chart data: ' . $e->getMessage()
    ]);
}

$conn->close();
?>