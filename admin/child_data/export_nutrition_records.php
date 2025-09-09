<?php
include "../../backend/config.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../components/login.php");
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=nutrition_records_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Child ID',
    'First Name',
    'Last Name',
    'Gender',
    'Birthdate',
    'Age',
    'Zone',
    'Weight (kg)',
    'Height (cm)',
    'BMI',
    'Nutritional Status',
    'Date Recorded',
    'Recorded By'
]);

// Query to get all nutrition records with child details
$query = "SELECT tc.child_id, tc.first_name, tc.last_name, tc.gender, tc.birthdate,
                 tb.zone_name,
                 nr.weight, nr.height, nr.bmi, nr.date_recorded,
                 tns.status_name,
                 tud.full_name as recorded_by_name
          FROM tbl_child tc
          INNER JOIN tbl_nutritrion_record nr ON tc.child_id = nr.child_id
          INNER JOIN tbl_nutrition_status tns ON nr.status_id = tns.status_id
          LEFT JOIN tbl_barangay tb ON tc.zone_id = tb.zone_id
          LEFT JOIN tbl_user_details tud ON nr.recorded_by = tud.user_id
          ORDER BY tc.last_name, tc.first_name, nr.date_recorded DESC";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate age
        $birthDate = new DateTime($row['birthdate']);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthDate)->y;
        
        fputcsv($output, [
            $row['child_id'],
            $row['first_name'],
            $row['last_name'],
            $row['gender'],
            $row['birthdate'],
            $age,
            $row['zone_name'] ?: 'N/A',
            $row['weight'],
            $row['height'],
            $row['bmi'],
            $row['status_name'],
            $row['date_recorded'],
            $row['recorded_by_name'] ?: 'N/A'
        ]);
    }
}

fclose($output);
?>