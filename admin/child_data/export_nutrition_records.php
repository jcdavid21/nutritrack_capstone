<?php

include "../../backend/config.php";

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="nutrition_records_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

try {
    // Base query with joins - get latest nutrition record for each child
    $query = "SELECT 
                c.child_id,
                c.first_name,
                c.last_name,
                c.gender,
                c.birthdate,
                c.zone_id,
                z.zone_name,
                n.nutrition_id,
                n.weight,
                n.height,
                n.bmi,
                n.date_recorded,
                ns.status_name,
                ns.status_id,
                c.created_at
              FROM tbl_child c
              LEFT JOIN tbl_barangay z ON c.zone_id = z.zone_id
              LEFT JOIN tbl_nutrition_record n ON c.child_id = n.child_id
              LEFT JOIN tbl_nutrition_status ns ON n.status_id = ns.status_id
              WHERE 1=1";
    
    $params = [];
    $types  = "";
    $values = [];

    // Apply filters
    if (!empty($_GET['status_id'])) {
        $query .= " AND n.status_id = ?";
        $types  .= "i";
        $values[] = $_GET['status_id'];
    }

    if (!empty($_GET['gender'])) {
        $query .= " AND c.gender = ?";
        $types  .= "s";
        $values[] = $_GET['gender'];
    }

    if (!empty($_GET['zone'])) {
        $query .= " AND z.zone_name = ?";
        $types  .= "s";
        $values[] = $_GET['zone'];
    }


    if (!empty($_GET['min_bmi'])) {
        $query .= " AND n.bmi >= ?";
        $types  .= "d";
        $values[] = floatval($_GET['min_bmi']);
    }

    if (!empty($_GET['max_bmi'])) {
        $query .= " AND n.bmi <= ?";
        $types  .= "d";
        $values[] = floatval($_GET['max_bmi']);
    }

    if (!empty($_GET['start_date'])) {
        $query .= " AND DATE(n.date_recorded) >= ?";
        $types  .= "s";
        $values[] = $_GET['start_date'];
    }

    if (!empty($_GET['end_date'])) {
        $query .= " AND DATE(n.date_recorded) <= ?";
        $types  .= "s";
        $values[] = $_GET['end_date'];
    }

    $query .= " ORDER BY c.child_id, n.date_recorded DESC";

    // Prepare & bind
    $stmt = $conn->prepare($query);
    if ($types && $values) {
        $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $allRecords = $result->fetch_all(MYSQLI_ASSOC);

    // Get latest record per child and apply age filters
    $records = [];
    $processedChildren = [];

    foreach ($allRecords as $record) {
        if (!in_array($record['child_id'], $processedChildren)) {
            $processedChildren[] = $record['child_id'];
            
            // Calculate age
            if ($record['birthdate']) {
                $birthDate = new DateTime($record['birthdate']);
                $today = new DateTime();
                $age = $birthDate->diff($today)->y;
                
                // Apply age filters
                if (!empty($_GET['min_age']) && $age < intval($_GET['min_age'])) {
                    continue;
                }
                if (!empty($_GET['max_age']) && $age > intval($_GET['max_age'])) {
                    continue;
                }
                
                $record['calculated_age'] = $age;
            } else {
                $record['calculated_age'] = 'N/A';
            }
            
            $records[] = $record;
        }
    }

    // Function to calculate age
    function calculateAge($birthdate) {
        if (!$birthdate) return 'N/A';
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        return $birth->diff($today)->y;
    }

    // Start Excel output
    echo '<table border="1">';

    // Headers
    echo '<tr>';
    echo '<th>Child ID</th>';
    echo '<th>Child Name</th>';
    echo '<th>Gender</th>';
    echo '<th>Age (Years)</th>';
    echo '<th>Date of Birth</th>';
    echo '<th>Zone</th>';
    echo '<th>Weight (kg)</th>';
    echo '<th>Height (cm)</th>';
    echo '<th>BMI</th>';
    echo '<th>Nutritional Status</th>';
    echo '<th>Last Recorded Date</th>';
    echo '<th>Days Since Last Record</th>';
    echo '<th>Registration Date</th>';
    echo '</tr>';

    // Data rows
    foreach ($records as $record) {
        $age = $record['calculated_age'];
        $lastRecordDate = $record['date_recorded'] ? new DateTime($record['date_recorded']) : null;
        $daysSinceRecord = $lastRecordDate ? $lastRecordDate->diff(new DateTime())->days : 'N/A';

        echo '<tr>';
        echo '<td>' . htmlspecialchars($record['child_id']) . '</td>';
        echo '<td>' . htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) . '</td>';
        echo '<td>' . htmlspecialchars($record['gender'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($age) . '</td>';
        echo '<td>' . htmlspecialchars($record['birthdate'] ? date('M d, Y', strtotime($record['birthdate'])) : 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($record['zone_name'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($record['weight'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($record['height'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($record['bmi'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($record['status_name'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($record['date_recorded'] ? date('M d, Y', strtotime($record['date_recorded'])) : 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($daysSinceRecord) . '</td>';
        echo '<td>' . htmlspecialchars($record['created_at'] ? date('M d, Y', strtotime($record['created_at'])) : 'N/A') . '</td>';
        echo '</tr>';
    }

    echo '</table>';

    // Summary information
    echo '<br><br>';
    echo '<table border="1">';
    echo '<tr><th colspan="2">Export Summary</th></tr>';
    echo '<tr><td>Total Records</td><td>' . count($records) . '</td></tr>';
    echo '<tr><td>Export Date</td><td>' . date('Y-m-d H:i:s') . '</td></tr>';

    if (!empty($_GET['status_id'])) {
        // Find status name for display
        $statusName = 'Unknown';
        foreach ($records as $record) {
            if ($record['status_id'] == $_GET['status_id']) {
                $statusName = $record['status_name'];
                break;
            }
        }
        echo '<tr><td>Status Filter</td><td>' . htmlspecialchars($statusName) . '</td></tr>';
    }
    if (!empty($_GET['gender'])) {
        echo '<tr><td>Gender Filter</td><td>' . htmlspecialchars($_GET['gender']) . '</td></tr>';
    }
    if (!empty($_GET['zone'])) {
        echo '<tr><td>Zone Filter</td><td>' . htmlspecialchars($_GET['zone']) . '</td></tr>';
    }
    if (!empty($_GET['min_age'])) {
        echo '<tr><td>Minimum Age</td><td>' . htmlspecialchars($_GET['min_age']) . ' years</td></tr>';
    }
    if (!empty($_GET['max_age'])) {
        echo '<tr><td>Maximum Age</td><td>' . htmlspecialchars($_GET['max_age']) . ' years</td></tr>';
    }
    if (!empty($_GET['min_bmi'])) {
        echo '<tr><td>Minimum BMI</td><td>' . htmlspecialchars($_GET['min_bmi']) . '</td></tr>';
    }
    if (!empty($_GET['max_bmi'])) {
        echo '<tr><td>Maximum BMI</td><td>' . htmlspecialchars($_GET['max_bmi']) . '</td></tr>';
    }

    if (!empty($_GET['start_date'])) {
        echo '<tr><td>Start Date</td><td>' . htmlspecialchars($_GET['start_date']) . '</td></tr>';
    }
    if (!empty($_GET['end_date'])) {
        echo '<tr><td>End Date</td><td>' . htmlspecialchars($_GET['end_date']) . '</td></tr>';
    }

    echo '</table>';

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
} finally {
    $conn->close();
}
?>