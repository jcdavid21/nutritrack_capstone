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

$completed_vaccinated_query = "SELECT COUNT(*) as count FROM tbl_vaccine_record WHERE vaccine_status = 'Completed'";
$completed_vaccinated_result = mysqli_query($conn, $completed_vaccinated_query);
$completed_vaccinated = mysqli_fetch_assoc($completed_vaccinated_result)['count'];

// Fetch flagged cases data for bar chart
$flagged_cases_query = "SELECT DATE_FORMAT(date_flagged, '%Y-%m') as month, COUNT(*) as count FROM tbl_flagged_record GROUP BY DATE_FORMAT(date_flagged, '%Y-%m') ORDER BY month DESC LIMIT 6";
$flagged_cases_result = mysqli_query($conn, $flagged_cases_query);
$flagged_cases_data = [];
while ($row = mysqli_fetch_assoc($flagged_cases_result)) {
    $flagged_cases_data[] = $row;
}

// Fetch nutrition status data for pie chart (latest records only)
$nutrition_status_query = "SELECT ns.status_name, COUNT(*) as count 
                          FROM (
                              SELECT DISTINCT nr1.child_id, nr1.status_id
                              FROM tbl_nutritrion_record nr1
                              INNER JOIN (
                                  SELECT child_id, MAX(date_recorded) as max_date
                                  FROM tbl_nutritrion_record
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

// Fetch vaccination status data for doughnut chart
$vaccination_status_query = "SELECT vaccine_status, COUNT(*) as count FROM tbl_vaccine_record GROUP BY vaccine_status";
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
                        <h3>Completed Vaccinated</h3>
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
                    <p class="chart-subtitle">Monthly flagged cases over the last 6 months</p>
                    <div class="chart-filters">
                        <select id="flaggedCasesFilter" class="filter-select">
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

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Reports Generated Over Time</h3>
                    <p class="chart-subtitle">Monthly reports generated in the last 6 months</p>
                </div>
                <div class="chart-container">
                    <canvas id="reportsChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Vaccination Status Overview</h3>
                    <p class="chart-subtitle">Overall vaccination completion status</p>
                </div>
                <div class="pie-chart-container">
                    <canvas id="vaccinationChart"></canvas>
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
        let flaggedChart, nutritionChart, reportsChart;

        // Original data
        const originalFlaggedData = <?php echo json_encode(array_reverse($flagged_cases_data)); ?>;
        const originalNutritionData = <?php echo json_encode($nutrition_data); ?>;
        const originalReportsData = <?php echo json_encode(array_reverse($reports_data)); ?>;
        // Add this line after the existing data variables
        const originalVaccinationData = <?php echo json_encode($vaccination_data); ?>;
        let vaccinationChart;

        function initializeCharts() {
            initializeFlaggedChart(originalFlaggedData);
            initializeNutritionChart(originalNutritionData);
            initializeVaccinationChart(originalVaccinationData);
            initializeReportsChart(originalReportsData);
        }

        function initializeFlaggedChart(data) {
            const flaggedLabels = data.map(item => {
                if (!item.month) return 'No Data';
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    year: 'numeric'
                });
            });
            const flaggedValues = data.map(item => parseInt(item.count || 0));

            if (flaggedLabels.length === 0) {
                flaggedLabels.push('Jan 2024', 'Feb 2024', 'Mar 2024', 'Apr 2024', 'May 2024', 'Jun 2024');
                flaggedValues.push(0, 0, 0, 0, 0, 0);
            }

            const flaggedCtx = document.getElementById('flaggedCasesChart').getContext('2d');

            if (flaggedChart) {
                flaggedChart.destroy();
            }

            flaggedChart = new Chart(flaggedCtx, {
                type: 'bar',
                data: {
                    labels: flaggedLabels,
                    datasets: [{
                        label: 'Flagged Cases',
                        data: flaggedValues,
                        backgroundColor: [
                            '#ff5722',
                            '#ff9800',
                            '#ffc107',
                            '#4caf50',
                            '#2196f3',
                            '#3f51b5',
                            '#9c27b0',
                            '#c2185b'
                        ],
                        borderColor: '#d84315',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
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
                // Make labels more user-friendly
                switch (item.vaccine_status) {
                    case 'Completed':
                        return 'Fully Vaccinated';
                    case 'Ongoing':
                        return 'In Progress';
                    case 'Incomplete':
                        return 'Incomplete';
                    default:
                        return item.vaccine_status;
                }
            });
            const vaccinationValues = data.map(item => parseInt(item.count || 0));

            if (vaccinationLabels.length === 0) {
                vaccinationLabels.push('Fully Vaccinated', 'In Progress', 'Incomplete');
                vaccinationValues.push(0, 0, 0);
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
                            '#4CAF50', // Green for Completed
                            '#FF9800', // Orange for Ongoing
                            '#f44336', // Red for Incomplete
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
                                    const percentage = ((context.parsed * 100) / total).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // Filter functions
        function filterFlaggedCases() {
            const filterValue = document.getElementById('flaggedCasesFilter').value;

            if (filterValue === 'all') {
                initializeFlaggedChart(originalFlaggedData);
                return;
            }

            // Fetch filtered data via AJAX
            fetch(`../backend/filter_flagged_cases.php?zone_id=${filterValue}`)
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

        // Event listeners
        document.getElementById('flaggedCasesFilter').addEventListener('change', filterFlaggedCases);
        document.getElementById('nutritionFilter').addEventListener('change', filterNutrition);

        // Initialize all charts on page load
        initializeCharts();
    </script>
</body>

</html>