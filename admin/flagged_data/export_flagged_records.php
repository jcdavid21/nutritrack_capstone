<?php

include "../../backend/config.php";


// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="flagged_records_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

try {
    // Base query with joins
    $query = "SELECT 
                fr.flagged_id,
                fr.child_id,
                c.first_name,
                c.last_name,
                c.gender,
                c.birthdate,
                c.zone_id,
                z.zone_name,
                fr.issue_type,
                fr.description,
                fr.flagged_status,
                fr.date_flagged,
                fr.resolution_notes,
                fr.resolution_date,
                fr.resolution_type,
                fr.current_status,
                fr.follow_up_date
              FROM tbl_flagged_record fr
              LEFT JOIN tbl_child c ON fr.child_id = c.child_id
              LEFT JOIN tbl_barangay z ON c.zone_id = z.zone_id
              WHERE 1=1";

    $params = [];
    $types  = "";
    $values = [];

    // Apply filters
    if (!empty($_GET['status'])) {
        $query .= " AND fr.flagged_status = ?";
        $types  .= "s";
        $values[] = $_GET['status'];
    }

    if (!empty($_GET['issue_type'])) {
        $query .= " AND fr.issue_type = ?";
        $types  .= "s";
        $values[] = $_GET['issue_type'];
    }

    if (!empty($_GET['zone'])) {
        $query .= " AND z.zone_id = ?";
        $types  .= "s";
        $values[] = $_GET['zone'];
    }

    if (!empty($_GET['search'])) {
        $query .= " AND (CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR c.child_id LIKE ?)";
        $types  .= "ss";
        $values[] = "%" . $_GET['search'] . "%";
        $values[] = "%" . $_GET['search'] . "%";
    }

    if (!empty($_GET['start_date'])) {
        $query .= " AND DATE(fr.date_flagged) >= ?";
        $types  .= "s";
        $values[] = $_GET['start_date'];
    }

    if (!empty($_GET['end_date'])) {
        $query .= " AND DATE(fr.date_flagged) <= ?";
        $types  .= "s";
        $values[] = $_GET['end_date'];
    }

    // Priority filter logic
    if (!empty($_GET['priority'])) {
        $priority = $_GET['priority'];

        if ($priority === 'High') {
            $query .= " AND (
                (fr.issue_type LIKE '%Severely%' AND DATEDIFF(CURDATE(), DATE(fr.date_flagged)) > 7) OR
                (fr.issue_type IN ('Underweight', 'Overweight') AND DATEDIFF(CURDATE(), DATE(fr.date_flagged)) > 14) OR
                DATEDIFF(CURDATE(), DATE(fr.date_flagged)) > 21
            )";
        } elseif ($priority === 'Medium') {
            $query .= " AND (
                (fr.issue_type LIKE '%Severely%' AND DATEDIFF(CURDATE(), DATE(fr.date_flagged)) BETWEEN 1 AND 7) OR
                (fr.issue_type IN ('Underweight', 'Overweight') AND DATEDIFF(CURDATE(), DATE(fr.date_flagged)) BETWEEN 7 AND 14) OR
                (fr.issue_type NOT LIKE '%Severely%' AND fr.issue_type NOT IN ('Underweight', 'Overweight') AND DATEDIFF(CURDATE(), DATE(fr.date_flagged)) BETWEEN 7 AND 21)
            )";
        } elseif ($priority === 'Low') {
            $query .= " AND (
                (fr.issue_type LIKE '%Severely%' AND DATEDIFF(CURDATE(), DATE(fr.date_flagged)) <= 1) OR
                (fr.issue_type IN ('Underweight', 'Overweight') AND DATEDIFF(CURDATE(), DATE(fr.date_flagged)) <= 7) OR
                (fr.issue_type NOT LIKE '%Severely%' AND fr.issue_type NOT IN ('Underweight', 'Overweight') AND DATEDIFF(CURDATE(), DATE(fr.date_flagged)) <= 7)
            )";
        }
    }

    $query .= " ORDER BY fr.date_flagged DESC";

    // Prepare & bind
    $stmt = $conn->prepare($query);
    if ($types && $values) {
        $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result->fetch_all(MYSQLI_ASSOC);

    // Function to calculate age
    function calculateAge($birthdate)
    {
        if (!$birthdate) return 'N/A';
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        return $birth->diff($today)->y;
    }

    // Function to calculate days open
    function calculateDaysOpen($dateFlagged, $status)
    {
        if ($status === 'Resolved') return 0;
        $flagged = new DateTime($dateFlagged);
        $today = new DateTime();
        return $flagged->diff($today)->days;
    }

    // Function to calculate priority
    function calculatePriority($issueType, $daysOpen)
    {
        $baseScore = 0;

        if (stripos($issueType, 'severely') !== false) {
            $baseScore = 3;
        } elseif (in_array($issueType, ['Underweight', 'Overweight'])) {
            $baseScore = 2;
        } else {
            $baseScore = 1;
        }

        if ($daysOpen > 14) $baseScore += 2;
        elseif ($daysOpen > 7) $baseScore += 1;

        if ($baseScore >= 4) return 'High';
        if ($baseScore >= 2) return 'Medium';
        return 'Low';
    }

    // Start Excel output
    echo '<table border="1">';

    // Headers
    echo '<tr>';
    echo '<th>Flagged ID</th>';
    echo '<th>Child ID</th>';
    echo '<th>Child Name</th>';
    echo '<th>Gender</th>';
    echo '<th>Age</th>';
    echo '<th>Zone</th>';
    echo '<th>Issue Type</th>';
    echo '<th>Status</th>';
    echo '<th>Priority</th>';
    echo '<th>Date Flagged</th>';
    echo '<th>Days Open</th>';
    echo '<th>Description</th>';
    echo '<th>Resolution Notes</th>';
    echo '<th>Resolution Date</th>';
    echo '<th>Resolution Type</th>';
    echo '<th>Current Status</th>';
    echo '<th>Follow-up Date</th>';
    echo '</tr>';

    // Data rows
    foreach ($records as $record) {
        $age = calculateAge($record['birthdate']);
        $daysOpen = calculateDaysOpen($record['date_flagged'], $record['flagged_status']);
        $priority = calculatePriority($record['issue_type'], $daysOpen);

        echo '<tr>';
        echo '<td>' . htmlspecialchars($record['flagged_id']) . '</td>';
        echo '<td>' . htmlspecialchars($record['child_id']) . '</td>';
        echo '<td>' . htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) . '</td>';
        echo '<td>' . htmlspecialchars($record['gender'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($age) . '</td>';
        echo '<td>' . htmlspecialchars($record['zone_name'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($record['issue_type']) . '</td>';
        echo '<td>' . htmlspecialchars($record['flagged_status']) . '</td>';
        echo '<td>' . htmlspecialchars($priority) . '</td>';
        echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($record['date_flagged']))) . '</td>';
        echo '<td>' . htmlspecialchars($daysOpen) . '</td>';
        echo '<td>' . htmlspecialchars($record['description'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($record['resolution_notes'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($record['resolution_date'] ? date('M d, Y', strtotime($record['resolution_date'])) : '') . '</td>';
        echo '<td>' . htmlspecialchars($record['resolution_type'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($record['current_status'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($record['follow_up_date'] ? date('M d, Y', strtotime($record['follow_up_date'])) : '') . '</td>';
        echo '</tr>';
    }

    echo '</table>';

    // Summary information
    echo '<br><br>';
    echo '<table border="1">';
    echo '<tr><th colspan="2">Export Summary</th></tr>';
    echo '<tr><td>Total Records</td><td>' . count($records) . '</td></tr>';
    echo '<tr><td>Export Date</td><td>' . date('Y-m-d H:i:s') . '</td></tr>';

    if (!empty($_GET['status'])) {
        echo '<tr><td>Status Filter</td><td>' . htmlspecialchars($_GET['status']) . '</td></tr>';
    }
    if (!empty($_GET['issue_type'])) {
        echo '<tr><td>Issue Type Filter</td><td>' . htmlspecialchars($_GET['issue_type']) . '</td></tr>';
    }
    if (!empty($_GET['priority'])) {
        echo '<tr><td>Priority Filter</td><td>' . htmlspecialchars($_GET['priority']) . '</td></tr>';
    }
    if (!empty($_GET['zone'])) {
        echo '<tr><td>Zone Filter</td><td>' . htmlspecialchars($_GET['zone']) . '</td></tr>';
    }
    if (!empty($_GET['search'])) {
        echo '<tr><td>Search Term</td><td>' . htmlspecialchars($_GET['search']) . '</td></tr>';
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
