<!DOCTYPE html>
<?php
include_once("../backend/config.php");
session_start();

if (!isset($_SESSION['user_id']) && $_SESSION["role_id"] != 2) {
    header("Location: ../components/login.php");
    exit();
}

// Fetch dashboard statistics
$registered_children_query = "SELECT COUNT(*) as count FROM tbl_child";
$registered_children_result = mysqli_query($conn, $registered_children_query);
$registered_children = mysqli_fetch_assoc($registered_children_result)['count'];

$flagged_records_query = "SELECT COUNT(*) as count FROM tbl_flagged_record";
$flagged_records_result = mysqli_query($conn, $flagged_records_query);
$flagged_records = mysqli_fetch_assoc($flagged_records_result)['count'];

$completed_vaccinated_query = "SELECT COUNT(DISTINCT child_id) AS count
FROM tbl_vaccine_record
WHERE vaccine_status = 'Completed';";
$completed_vaccinated_result = mysqli_query($conn, $completed_vaccinated_query);
$completed_vaccinated = mysqli_fetch_assoc($completed_vaccinated_result)['count'];

// Fetch flagged cases data for bar chart - by type and month
$flagged_cases_query = "SELECT 
    DATE_FORMAT(fr.date_flagged, '%Y-%m') as month,
    YEAR(fr.date_flagged) as year,
    MONTH(fr.date_flagged) as month_num,
    MONTHNAME(fr.date_flagged) as month_name,
    ft.flagged_name,
    ft.ft_id,
    COUNT(*) as count 
    FROM tbl_flagged_record fr
    JOIN tbl_flagged_type ft ON fr.issue_type = ft.ft_id
    GROUP BY DATE_FORMAT(fr.date_flagged, '%Y-%m'), ft.ft_id, ft.flagged_name
    ORDER BY fr.date_flagged DESC, ft.flagged_name ASC";
$flagged_cases_result = mysqli_query($conn, $flagged_cases_query);
$flagged_cases_data = [];
while ($row = mysqli_fetch_assoc($flagged_cases_result)) {
    $flagged_cases_data[] = $row;
}

// Get available months for filter
$months_query = "SELECT DISTINCT 
    DATE_FORMAT(date_flagged, '%Y-%m') as month,
    DATE_FORMAT(date_flagged, '%M %Y') as month_display
    FROM tbl_flagged_record 
    ORDER BY month DESC";
$months_result = mysqli_query($conn, $months_query);
$available_months = [];
while ($row = mysqli_fetch_assoc($months_result)) {
    $available_months[] = $row;
}

// Get available case types for filter
$case_types_query = "SELECT ft_id, flagged_name FROM tbl_flagged_type ORDER BY flagged_name ASC";
$case_types_result = mysqli_query($conn, $case_types_query);
$available_case_types = [];
while ($row = mysqli_fetch_assoc($case_types_result)) {
    $available_case_types[] = $row;
}

// Get available years for filter
$years_query = "SELECT DISTINCT YEAR(date_flagged) as year FROM tbl_flagged_record ORDER BY year DESC";
$years_result = mysqli_query($conn, $years_query);
$available_years = [];
while ($row = mysqli_fetch_assoc($years_result)) {
    $available_years[] = $row['year'];
}

// Fetch nutrition status data for pie chart (latest records only)
$nutrition_status_query = "SELECT ns.status_name, COUNT(*) as count 
                          FROM (
                              SELECT DISTINCT nr1.child_id, nr1.status_id
                              FROM tbl_nutrition_record nr1
                              INNER JOIN (
                                  SELECT child_id, MAX(date_recorded) as max_date
                                  FROM tbl_nutrition_record
                                  GROUP BY child_id
                              ) nr2 ON nr1.child_id = nr2.child_id AND nr1.date_recorded = nr2.max_date
                          ) latest_records
                          JOIN tbl_nutrition_status ns ON latest_records.status_id = ns.status_id 
                          GROUP BY ns.status_name";
$nutrition_status_result = mysqli_query($conn, $nutrition_status_query);
$nutrition_data = [];
while ($row = mysqli_fetch_assoc($nutrition_status_result)) {
    $nutrition_data[] = $row;
}

// Fetch upcoming events
$upcoming_events_query = "SELECT * FROM tbl_events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 5";
$upcoming_events_result = mysqli_query($conn, $upcoming_events_query);
$upcoming_events = [];
while ($row = mysqli_fetch_assoc($upcoming_events_result)) {
    $upcoming_events[] = $row;
}

// Fetch recent announcements (using as educational resources)
$education_resources_query = "SELECT announcement_id, content as title, content as description, 'announcement' as type, post_date as created_at FROM tbl_announcements ORDER BY post_date DESC LIMIT 3";
$education_resources_result = mysqli_query($conn, $education_resources_query);
$education_resources = [];
while ($row = mysqli_fetch_assoc($education_resources_result)) {
    $education_resources[] = $row;
}

// Fetch reports data for line chart
$reports_query = "SELECT DATE_FORMAT(report_date, '%Y-%m') as month, COUNT(*) as count FROM tbl_report GROUP BY DATE_FORMAT(report_date, '%Y-%m') ORDER BY month DESC LIMIT 6";
$reports_result = mysqli_query($conn, $reports_query);
$reports_data = [];
while ($row = mysqli_fetch_assoc($reports_result)) {
    $reports_data[] = $row;
}

// Fetch zone data for filters
$zones_query = "SELECT DISTINCT zone_id, zone_name FROM tbl_barangay ORDER BY zone_name";
$zones_result = mysqli_query($conn, $zones_query);
$zones = [];
while ($row = mysqli_fetch_assoc($zones_result)) {
    $zones[] = $row;
}

// Fetch vaccination status data for doughnut chart - count unique children with at least 1 completed vaccine
$vaccination_status_query = "SELECT 
    CASE 
        WHEN completed_children.child_id IS NOT NULL THEN 'Completed'
        ELSE 'Not Completed'
    END as vaccine_status,
    COUNT(*) as count
FROM (
    SELECT DISTINCT c.child_id
    FROM tbl_child c
    LEFT JOIN (
        SELECT DISTINCT child_id
        FROM tbl_vaccine_record 
        WHERE vaccine_status = 'Completed'
    ) completed_children ON c.child_id = completed_children.child_id
) all_children_with_status
LEFT JOIN (
    SELECT DISTINCT child_id
    FROM tbl_vaccine_record 
    WHERE vaccine_status = 'Completed'
) completed_children ON all_children_with_status.child_id = completed_children.child_id
GROUP BY vaccine_status";
$vaccination_status_result = mysqli_query($conn, $vaccination_status_query);
$vaccination_data = [];
while ($row = mysqli_fetch_assoc($vaccination_status_result)) {
    $vaccination_data[] = $row;
}
?>


<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/general.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Admin Dashboard</title>
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #81C784;
            --accent-color: #ffffff;
            --text-color: #1B5E20;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            margin-top: 80px;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }

        body.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }

        .main-content {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .dashboard-subtitle {
            font-size: 16px;
            color: #666;
            font-weight: 400;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 28px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e9ecef;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.children::before {
            background: #4CAF50;
        }

        .stat-card.flagged::before {
            background: #ff5722;
        }

        .stat-card.vaccinated::before {
            background: #2196F3;
        }

        .stat-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-info h3 {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-color);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.children {
            background: #4CAF50;
        }

        .stat-icon.flagged {
            background: #ff5722;
        }

        .stat-icon.vaccinated {
            background: #2196F3;
        }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e9ecef;
        }

        .chart-header {
            margin-bottom: 25px;
        }

        .chart-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .chart-subtitle {
            font-size: 14px;
            color: #666;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .pie-chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }

        /* Info Sections */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .info-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e9ecef;
        }

        .info-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }

        .info-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .event-item,
        .resource-item {
            padding: 20px 0;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .event-item:last-child,
        .resource-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .event-date,
        .resource-type {
            background: var(--primary-color);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            min-width: 80px;
            text-align: center;
        }

        .event-details,
        .resource-details {
            flex: 1;
        }

        .event-title,
        .resource-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .event-description,
        .resource-description {
            font-size: 14px;
            color: #666;
            line-height: 1.4;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 16px;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .empty-state small {
            font-size: 14px;
            color: #999;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            body {
                margin-top: var(--header-height);
                margin-left: 0 !important;
            }

            body.sidebar-collapsed {
                margin-left: 0 !important;
            }

            .main-content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .dashboard-title {
                font-size: 24px;
            }

            .stat-card {
                padding: 20px;
            }

            .chart-card,
            .info-card {
                padding: 20px;
            }

            .stat-number {
                font-size: 28px;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .chart-container {
                height: 250px;
            }

            .pie-chart-container {
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }

            .stats-grid {
                gap: 12px;
            }

            .stat-card {
                padding: 16px;
            }

            .chart-card,
            .info-card {
                padding: 16px;
            }

            .event-item,
            .resource-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .event-date,
            .resource-type {
                align-self: flex-start;
            }
        }

        .chart-filters {
            margin-top: 15px;
        }

        .filter-select {
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            background: white;
            font-size: 14px;
            color: var(--text-color);
            cursor: pointer;
            outline: none;
            transition: border-color 0.3s ease;
            min-width: 150px;
        }

        .filter-select:focus {
            border-color: var(--primary-color);
        }

        .filter-select:hover {
            border-color: var(--secondary-color);
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-filters {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-input {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            min-width: 200px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .filter-input:focus {
            border-color: var(--primary-color);
        }

        .modal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .modal-table th {
            background: #f8f9fa;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: var(--text-color);
            border-bottom: 2px solid #e9ecef;
        }

        .modal-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .modal-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        #childrenTableContainer,
        #flaggedTableContainer,
        #vaccinatedTableContainer {
            max-height: 400px;
            overflow-y: auto;
            margin: 0 30px;
        }

        .modal-pagination {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pagination-btn {
            padding: 8px 16px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .pagination-btn:hover {
            background: var(--text-color);
        }

        .pagination-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Make stat cards clickable */
        .stat-card {
            cursor: pointer;
            user-select: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 5% auto;
                max-height: 85vh;
            }

            .modal-filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-input {
                min-width: auto;
                width: 100%;
            }

            #childrenTableContainer,
            #flaggedTableContainer,
            #vaccinatedTableContainer {
                margin: 0 15px;
                max-height: 300px;
            }

            .modal-header,
            .modal-filters,
            .modal-pagination {
                padding: 15px 20px;
            }

            .modal-table {
                font-size: 12px;
            }

            .modal-table th,
            .modal-table td {
                padding: 8px 6px;
            }
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-under-review {
            background: #ffebee;
            color: #c62828;
        }

        .status-active {
            background: #fff3e0;
            color: #ef6c00;
        }

        .status-resolved {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-completed {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-ongoing {
            background: #fff3e0;
            color: #ef6c00;
        }

        .status-incomplete {
            background: #ffebee;
            color: #c62828;
        }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard Overview
            </h1>
            <p class="dashboard-subtitle">Welcome to your admin dashboard. Monitor key metrics and manage your system efficiently.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card children">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>Registered Children</h3>
                        <div class="stat-number"><?php echo $registered_children; ?></div>
                    </div>
                    <div class="stat-icon children">
                        <i class="fas fa-child"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card flagged">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>Flagged Records</h3>
                        <div class="stat-number"><?php echo $flagged_records; ?></div>
                    </div>
                    <div class="stat-icon flagged">
                        <i class="fas fa-flag"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card vaccinated">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>Vaccinated Children</h3>
                        <div class="stat-number"><?php echo $completed_vaccinated; ?></div>
                    </div>
                    <div class="stat-icon vaccinated">
                        <i class="fas fa-syringe"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Flagged Cases Trend</h3>
                    <p class="chart-subtitle">Monthly flagged cases by year</p>
                    <div class="chart-filters" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <select id="flaggedCasesYearFilter" class="filter-select">
                            <option value="all" selected>All Years</option>
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="flaggedCasesMonthFilter" class="filter-select">
                            <option value="all">All Months</option>
                            <?php foreach ($available_months as $month): ?>
                                <option value="<?php echo $month['month']; ?>"><?php echo htmlspecialchars($month['month_display']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="flaggedCaseTypeFilter" class="filter-select">
                            <option value="all">All Case Types</option>
                            <?php foreach ($available_case_types as $case_type): ?>
                                <option value="<?php echo $case_type['ft_id']; ?>"><?php echo htmlspecialchars($case_type['flagged_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="flaggedCasesZoneFilter" class="filter-select">
                            <option value="all">All Zones</option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?php echo $zone['zone_id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="flaggedCasesChart"></canvas>
                </div>
            </div>
        </div>


        <div class="charts-grid">

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Vaccination Status Overview</h3>
                    <p class="chart-subtitle">Children with at least 1 completed vaccination</p>
                    <div class="chart-filters">
                        <select id="vaccinationZoneFilter" class="filter-select">
                            <option value="all">All Zones</option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?php echo $zone['zone_id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="pie-chart-container">
                    <canvas id="vaccinationChart"></canvas>
                </div>
            </div>


            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Nutrition Status Distribution</h3>
                    <p class="chart-subtitle">Current nutrition status of registered children</p>
                    <div class="chart-filters">
                        <select id="nutritionFilter" class="filter-select">
                            <option value="all">All Zones</option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?php echo $zone['zone_id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="pie-chart-container">
                    <canvas id="nutritionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-header">
                    <h3 class="info-title">Upcoming Events</h3>
                    <div class="info-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="info-content">
                    <?php if (count($upcoming_events) > 0): ?>
                        <?php foreach ($upcoming_events as $event): ?>
                            <div class="event-item">
                                <div class="event-date">
                                    <?php echo date('M d', strtotime($event['event_date'])); ?>
                                </div>
                                <div class="event-details">
                                    <h4 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>No upcoming events</p>
                            <small>Events will appear here when scheduled</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-card">
                <div class="info-header">
                    <h3 class="info-title">Recent Announcements</h3>
                    <div class="info-icon">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="info-content">
                    <?php if (count($education_resources) > 0): ?>
                        <?php foreach ($education_resources as $resource): ?>
                            <div class="resource-item">
                                <div class="resource-type">
                                    <?php echo strtoupper($resource['type']); ?>
                                </div>
                                <div class="resource-details">
                                    <h4 class="resource-title"><?php echo htmlspecialchars(substr($resource['title'], 0, 50)) . (strlen($resource['title']) > 50 ? '...' : ''); ?></h4>
                                    <p class="resource-description"><?php echo htmlspecialchars(substr($resource['description'], 0, 100)) . (strlen($resource['description']) > 100 ? '...' : ''); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <p>No announcements</p>
                            <small>Announcements will appear here when posted</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for each card -->
    <div id="childrenModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Registered Children</h2>
                <span class="close" data-modal="childrenModal">&times;</span>
            </div>
            <div class="modal-filters">
                <input type="text" id="childrenSearch" placeholder="Search children..." class="filter-input">
                <select id="childrenZoneFilter" class="filter-select">
                    <option value="">All Zones</option>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?php echo $zone['zone_id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="childrenGenderFilter" class="filter-select">
                    <option value="">All Genders</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div id="childrenTableContainer">
                <table class="modal-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Birthdate</th>
                            <th>Age</th>
                            <th>Zone</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody id="childrenTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-pagination">
                <button id="childrenPrevPage" class="pagination-btn">Previous</button>
                <span id="childrenPageInfo"></span>
                <button id="childrenNextPage" class="pagination-btn">Next</button>
            </div>
        </div>
    </div>

    <div id="flaggedModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Flagged Records</h2>
                <span class="close" data-modal="flaggedModal">&times;</span>
            </div>
            <div class="modal-filters">
                <input type="text" id="flaggedSearch" placeholder="Search flagged records..." class="filter-input">
                <select id="flaggedStatusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Under review">Under review</option>
                    <option value="Resolved">Resolved</option>
                </select>
                <select id="flaggedIssueFilter" class="filter-select">
                    <option value="">All Issues</option>
                    <option value="Underweight">Underweight</option>
                    <option value="Overweight">Overweight</option>
                    <option value="Severely Underweight">Severely Underweight</option>
                    <option value="Incomplete Vaccination">Incomplete Vaccination</option>
                    <option value="Growth Concerns">Growth Concerns</option>
                    <option value="Behavioral Issues">Behavioral Issues</option>
                    <option value="Medical Concerns">Medical Concerns</option>
                </select>
            </div>
            <div id="flaggedTableContainer">
                <table class="modal-table">
                    <thead>
                        <tr>
                            <th>Child Name</th>
                            <th>Issue Type</th>
                            <th>Date Flagged</th>
                            <th>Status</th>
                            <th>Zone</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="flaggedTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-pagination">
                <button id="flaggedPrevPage" class="pagination-btn">Previous</button>
                <span id="flaggedPageInfo"></span>
                <button id="flaggedNextPage" class="pagination-btn">Next</button>
            </div>
        </div>
    </div>

    <div id="vaccinatedModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Vaccinated Children</h2>
                <span class="close" data-modal="vaccinatedModal">&times;</span>
            </div>
            <div class="modal-filters">
                <input type="text" id="vaccinatedSearch" placeholder="Search vaccinated children..." class="filter-input">
                <select id="vaccinatedStatusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Completed">Completed</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Incomplete">Incomplete</option>
                </select>
                <select id="vaccinatedVaccine_typeFilter" class="filter-select">
                    <option value="">All Vaccines</option>
                    <?php
                    $select_vaccine_names = "SELECT DISTINCT vaccine_name FROM tbl_vaccine_record ORDER BY vaccine_name ASC";
                    $vaccine_result = $conn->query($select_vaccine_names);
                    $vaccine_results = [];
                    if ($vaccine_result && $vaccine_result->num_rows > 0) {
                        while ($vaccine_row = $vaccine_result->fetch_assoc()) {
                            $vaccine_name = htmlspecialchars($vaccine_row['vaccine_name']);
                            $vaccine_results[] = $vaccine_name;
                            echo "<option value=\"$vaccine_name\">$vaccine_name</option>";
                        }
                    }
                    ?>
                </select>
                <select id="vaccinatedZoneFilter" class="filter-select">
                    <option value="">All Zones</option>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?php echo $zone['zone_id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="vaccinatedTableContainer">
                <table class="modal-table">
                    <thead>
                        <tr>
                            <th>Child Name</th>
                            <th>Vaccine</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Zone</th>
                            <th>Administered By</th>
                        </tr>
                    </thead>
                    <tbody id="vaccinatedTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-pagination">
                <button id="vaccinatedPrevPage" class="pagination-btn">Previous</button>
                <span id="vaccinatedPageInfo"></span>
                <button id="vaccinatedNextPage" class="pagination-btn">Next</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('adminSidebar');
            const body = document.body;

            function updateBodyClass() {
                if (sidebar && sidebar.classList.contains('collapsed')) {
                    body.classList.add('sidebar-collapsed');
                } else {
                    body.classList.remove('sidebar-collapsed');
                }
            }

            updateBodyClass();

            if (sidebar) {
                const observer = new MutationObserver(updateBodyClass);
                observer.observe(sidebar, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
        });

        // Global chart variables
        let flaggedChart, nutritionChart, vaccinationChart;

        // Original data
        const originalFlaggedData = <?php echo json_encode($flagged_cases_data); ?>;
        const originalNutritionData = <?php echo json_encode($nutrition_data); ?>;
        const originalVaccinationData = <?php echo json_encode($vaccination_data); ?>;

        function initializeCharts() {
            initializeFlaggedChart(originalFlaggedData);
            initializeNutritionChart(originalNutritionData);
            initializeVaccinationChart(originalVaccinationData);
        }


        function initializeFlaggedChart(data) {
            const yearFilter = document.getElementById('flaggedCasesYearFilter').value;
            const monthFilter = document.getElementById('flaggedCasesMonthFilter').value;
            const caseTypeFilter = document.getElementById('flaggedCaseTypeFilter').value;

            // Process data to create datasets for each case type
            const caseTypes = {};
            const months = new Set();

            // Group data by case type and collect all months
            data.forEach(item => {
                if (!item.month || !item.flagged_name) return;

                const monthKey = item.month;
                const caseType = item.flagged_name;

                months.add(monthKey);

                if (!caseTypes[caseType]) {
                    caseTypes[caseType] = {};
                }
                caseTypes[caseType][monthKey] = parseInt(item.count || 0);
            });

            // Convert months set to sorted array
            const sortedMonths = Array.from(months).sort().slice(-12); // Last 12 months

            // Create labels from months
            const flaggedLabels = sortedMonths.map(month => {
                if (!month) return 'No Data';
                const date = new Date(month + '-01');
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    year: 'numeric'
                });
            });

            // If no data, create default structure
            if (flaggedLabels.length === 0) {
                const currentDate = new Date();
                for (let i = 11; i >= 0; i--) {
                    const monthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
                    const monthKey = monthDate.getFullYear() + '-' + String(monthDate.getMonth() + 1).padStart(2, '0');
                    sortedMonths.push(monthKey);
                    flaggedLabels.push(monthDate.toLocaleDateString('en-US', {
                        month: 'short',
                        year: 'numeric'
                    }));
                }
            }

            // Color palette for different case types
            const colors = [
                '#d32f2f', '#f57c00', '#388e3c', '#1976d2', '#7b1fa2',
                '#c2185b', '#00796b', '#f57f17', '#5d4037', '#455a64'
            ];

            // Create datasets for each case type
            const datasets = Object.keys(caseTypes).map((caseType, index) => {
                const caseData = sortedMonths.map(month => caseTypes[caseType][month] || 0);

                return {
                    label: caseType,
                    data: caseData,
                    backgroundColor: colors[index % colors.length],
                    borderColor: '#ffffff',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                };
            });

            const flaggedCtx = document.getElementById('flaggedCasesChart').getContext('2d');

            if (flaggedChart) {
                flaggedChart.destroy();
            }

            flaggedChart = new Chart(flaggedCtx, {
                type: 'bar',
                data: {
                    labels: flaggedLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 11
                                },
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                color: '#f1f3f4'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }

        function initializeNutritionChart(data) {
            const nutritionLabels = data.map(item => item.status_name);
            const nutritionValues = data.map(item => parseInt(item.count || 0));

            if (nutritionLabels.length === 0) {
                nutritionLabels.push('Healthy', 'Underweight', 'Severely Underweight');
                nutritionValues.push(0, 0, 0);
            }

            const nutritionCtx = document.getElementById('nutritionChart').getContext('2d');

            if (nutritionChart) {
                nutritionChart.destroy();
            }

            nutritionChart = new Chart(nutritionCtx, {
                type: 'pie',
                data: {
                    labels: nutritionLabels,
                    datasets: [{
                        data: nutritionValues,
                        backgroundColor: [
                            '#4CAF50',
                            '#FF9800',
                            '#f44336',
                            '#9C27B0',
                            '#2196F3'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }

        function initializeReportsChart(data) {
            const reportsLabels = data.map(item => {
                if (!item.month) return 'No Data';
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    year: 'numeric'
                });
            });
            const reportsValues = data.map(item => parseInt(item.count || 0));

            if (reportsLabels.length === 0) {
                reportsLabels.push('Jan 2024', 'Feb 2024', 'Mar 2024', 'Apr 2024', 'May 2024', 'Jun 2024');
                reportsValues.push(0, 0, 0, 0, 0, 0);
            }

            const reportsCtx = document.getElementById('reportsChart').getContext('2d');

            if (reportsChart) {
                reportsChart.destroy();
            }

            reportsChart = new Chart(reportsCtx, {
                type: 'line',
                data: {
                    labels: reportsLabels,
                    datasets: [{
                        label: 'Reports Generated',
                        data: reportsValues,
                        borderColor: '#2E7D32',
                        backgroundColor: 'rgba(46, 125, 50, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#2E7D32',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                color: '#f1f3f4'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function initializeVaccinationChart(data) {
            const vaccinationLabels = data.map(item => {
                return item.vaccine_status === 'Completed' ? 'Vaccinated' : 'No Completed Vaccines';
            });
            const vaccinationValues = data.map(item => parseInt(item.count || 0));

            if (vaccinationLabels.length === 0) {
                vaccinationLabels.push('Vaccinated', 'No Completed Vaccines');
                vaccinationValues.push(0, 0);
            }

            const vaccinationCtx = document.getElementById('vaccinationChart').getContext('2d');

            if (vaccinationChart) {
                vaccinationChart.destroy();
            }

            vaccinationChart = new Chart(vaccinationCtx, {
                type: 'doughnut',
                data: {
                    labels: vaccinationLabels,
                    datasets: [{
                        data: vaccinationValues,
                        backgroundColor: [
                            '#4CAF50', // Green for completed
                            '#f44336', // Red for not completed
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed * 100) / total).toFixed(1) : 0;
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        function filterFlaggedCasesByFilters() {
            const yearValue = document.getElementById('flaggedCasesYearFilter').value;
            const monthValue = document.getElementById('flaggedCasesMonthFilter').value;
            const caseTypeValue = document.getElementById('flaggedCaseTypeFilter').value;
            const zoneValue = document.getElementById('flaggedCasesZoneFilter').value;

            // If all filters are 'all', use original data
            if (yearValue === 'all' && monthValue === 'all' && caseTypeValue === 'all' && zoneValue === 'all') {
                initializeFlaggedChart(originalFlaggedData);
                return;
            }

            const params = new URLSearchParams({
                year: yearValue !== 'all' ? yearValue : '',
                month: monthValue !== 'all' ? monthValue : '',
                case_type: caseTypeValue !== 'all' ? caseTypeValue : '',
                zone_id: zoneValue !== 'all' ? zoneValue : ''
            });

            fetch(`../backend/filter_flagged_cases_by_filters.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    initializeFlaggedChart(data);
                })
                .catch(error => {
                    console.error('Error filtering flagged cases:', error);
                    initializeFlaggedChart([]);
                });
        }

        function filterNutrition() {
            const filterValue = document.getElementById('nutritionFilter').value;

            if (filterValue === 'all') {
                initializeNutritionChart(originalNutritionData);
                return;
            }

            // Fetch filtered data via AJAX
            fetch(`../backend/filter_nutrition_data.php?zone_id=${filterValue}`)
                .then(response => response.json())
                .then(data => {
                    initializeNutritionChart(data);
                })
                .catch(error => {
                    console.error('Error filtering nutrition data:', error);
                    initializeNutritionChart([]);
                });
        }

        function filterVaccinationByZone() {
            const zoneValue = document.getElementById('vaccinationZoneFilter').value;

            if (zoneValue === 'all') {
                initializeVaccinationChart(originalVaccinationData);
                return;
            }

            fetch(`../backend/filter_vaccination_by_zone.php?zone_id=${zoneValue}`)
                .then(response => response.json())
                .then(data => {
                    initializeVaccinationChart(data);
                })
                .catch(error => {
                    console.error('Error filtering vaccination data:', error);
                    initializeVaccinationChart([]);
                });
        }


        // Event listeners
        document.getElementById('flaggedCasesYearFilter').addEventListener('change', filterFlaggedCasesByFilters);
        document.getElementById('flaggedCasesMonthFilter').addEventListener('change', filterFlaggedCasesByFilters);
        document.getElementById('flaggedCaseTypeFilter').addEventListener('change', filterFlaggedCasesByFilters);
        document.getElementById('flaggedCasesZoneFilter').addEventListener('change', filterFlaggedCasesByFilters);
        document.getElementById('vaccinationZoneFilter').addEventListener('change', filterVaccinationByZone);
        document.getElementById('nutritionFilter').addEventListener('change', filterNutrition);

        // Initialize all charts on page load
        initializeCharts();

        // Modal Management
        class ModalManager {
            constructor() {
                this.currentPage = {
                    children: 1,
                    flagged: 1,
                    vaccinated: 1
                };
                this.filters = {
                    children: {
                        search: '',
                        zone: '',
                        gender: ''
                    },
                    flagged: {
                        search: '',
                        status: '',
                        issue: ''
                    },
                    vaccinated: {
                        search: '',
                        status: '',
                        vaccine_type: '',
                        zone: ''
                    }
                };
                this.init();
            }

            init() {
                // Add click events to stat cards
                document.querySelector('.stat-card.children').addEventListener('click', () => this.openModal('children'));
                document.querySelector('.stat-card.flagged').addEventListener('click', () => this.openModal('flagged'));
                document.querySelector('.stat-card.vaccinated').addEventListener('click', () => this.openModal('vaccinated'));

                // Add close events
                document.querySelectorAll('.close').forEach(closeBtn => {
                    closeBtn.addEventListener('click', (e) => {
                        this.closeModal(e.target.dataset.modal);
                    });
                });

                // Close modal when clicking outside
                window.addEventListener('click', (e) => {
                    if (e.target.classList.contains('modal')) {
                        this.closeModal(e.target.id);
                    }
                });

                // Add filter events
                this.addFilterEvents();
                this.addPaginationEvents();
            }

            openModal(type) {
                const modal = document.getElementById(`${type}Modal`);
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                this.loadData(type);
            }

            closeModal(modalId) {
                const modal = document.getElementById(modalId);
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            addFilterEvents() {
                // Children filters
                ['childrenSearch', 'childrenZoneFilter', 'childrenGenderFilter'].forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        const eventType = element.type === 'text' ? 'input' : 'change';
                        element.addEventListener(eventType, () => {
                            this.updateFilter('children', id, element.value);
                            this.loadData('children');
                        });
                    }
                });

                // Flagged filters
                ['flaggedSearch', 'flaggedStatusFilter', 'flaggedIssueFilter'].forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        const eventType = element.type === 'text' ? 'input' : 'change';
                        element.addEventListener(eventType, () => {
                            this.updateFilter('flagged', id, element.value);
                            this.loadData('flagged');
                        });
                    }
                });

                // Vaccinated filters
                ['vaccinatedSearch', 'vaccinatedStatusFilter', 'vaccinatedVaccine_typeFilter', 'vaccinatedZoneFilter'].forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        const eventType = element.type === 'text' ? 'input' : 'change';
                        element.addEventListener(eventType, () => {
                            this.updateFilter('vaccinated', id, element.value);
                            this.loadData('vaccinated');
                        });
                    }
                });
            }

            addPaginationEvents() {
                ['children', 'flagged', 'vaccinated'].forEach(type => {
                    document.getElementById(`${type}PrevPage`).addEventListener('click', () => {
                        if (this.currentPage[type] > 1) {
                            this.currentPage[type]--;
                            this.loadData(type);
                        }
                    });

                    document.getElementById(`${type}NextPage`).addEventListener('click', () => {
                        this.currentPage[type]++;
                        this.loadData(type);
                    });
                });
            }

            updateFilter(type, filterId, value) {
                const filterKey = filterId.replace(`${type}`, '').replace('Filter', '').toLowerCase();
                this.filters[type][filterKey] = value;
                this.currentPage[type] = 1; // Reset to first page when filtering
            }

            loadData(type) {
                const endpoints = {
                    children: './child_data/get_dashboard_children.php',
                    flagged: './flagged_data/get_flagged_records.php',
                    vaccinated: './vaccine_data/get_vaccines.php'
                };

                const params = new URLSearchParams({
                    page: this.currentPage[type],
                    limit: 10,
                    ...this.filters[type]
                });

                fetch(`${endpoints[type]}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' || data.records || data.vaccines) {
                            this.renderTable(type, data);
                            this.updatePagination(type, data);
                        } else {
                            console.error('Error loading data:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });
            }

            renderTable(type, data) {
                const tbody = document.getElementById(`${type}TableBody`);
                tbody.innerHTML = '';

                let records;
                if (type === 'children') {
                    records = data.records || [];
                } else if (type === 'flagged') {
                    records = data.records || [];
                } else if (type === 'vaccinated') {
                    records = data.vaccines || [];
                }

                if (records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No data found</td></tr>';
                    return;
                }

                records.forEach(record => {
                    const row = document.createElement('tr');

                    if (type === 'children') {
                        const age = this.calculateAge(record.birthdate);
                        row.innerHTML = `
                    <td>${record.first_name} ${record.last_name}</td>
                    <td>${record.gender || 'N/A'}</td>
                    <td>${this.formatDate(record.birthdate)}</td>
                    <td>${age}</td>
                    <td>${record.zone_name || 'N/A'}</td>
                    <td>${this.formatDate(record.created_at)}</td>
                `;
                    } else if (type === 'flagged') {
                        row.innerHTML = `
                    <td>${record.first_name} ${record.last_name}</td>
                    <td>${record.issue_type}</td>
                    <td>${this.formatDate(record.date_flagged)}</td>
                    <td><span class="status-badge status-${record.flagged_status.toLowerCase().replace(' ', '-')}">${record.flagged_status}</span></td>
                    <td>${record.zone_name || 'N/A'}</td>
                    <td>${record.description ? record.description.substring(0, 50) + '...' : 'N/A'}</td>
                `;
                    } else if (type === 'vaccinated') {
                        row.innerHTML = `
                    <td>${record.first_name} ${record.last_name}</td>
                    <td>${record.vaccine_name}</td>
                    <td><span class="status-badge status-${record.vaccine_status.toLowerCase()}">${record.vaccine_status}</span></td>
                    <td>${this.formatDate(record.vaccine_date)}</td>
                    <td>${record.zone_name || 'N/A'}</td>
                    <td>${record.administered_by_name}</td>
                `;
                    }

                    tbody.appendChild(row);
                });
            }

            updatePagination(type, data) {
                const total = data.total || 0;
                const page = data.page || this.currentPage[type];
                const totalPages = data.total_pages || data.totalPages || Math.ceil(total / 10);

                document.getElementById(`${type}PageInfo`).textContent =
                    `Page ${page} of ${totalPages} (${total} total)`;

                document.getElementById(`${type}PrevPage`).disabled = page <= 1;
                document.getElementById(`${type}NextPage`).disabled = page >= totalPages;
            }

            calculateAge(birthdate) {
                if (!birthdate) return 'N/A';
                const today = new Date();
                const birth = new Date(birthdate);
                const age = Math.floor((today - birth) / (365.25 * 24 * 60 * 60 * 1000));
                return `${age} years`;
            }

            formatDate(dateString) {
                if (!dateString) return 'N/A';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        }

        // Initialize modal manager after DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            new ModalManager();
        });
    </script>
</body>

</html>