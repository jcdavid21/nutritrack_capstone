<!DOCTYPE html>
<html lang="en">
<?php
include "../backend/config.php";
session_start();

if (!isset($_SESSION['user_id']) && $_SESSION["role_id"] != 2) {
    header("Location: ../components/login.php");
    exit();
}

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/adminAnc.css">
    <link rel="stylesheet" href="../styles/general.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <title>Reports Management</title>
    <style>
        :root {
            --primary-red: #2d5a3d;
            --light-red: #4a7c59;
            --warning-orange: #fd7e14;
            --success-green: #27ae60;
            --primary-blue: #0d6efd;
            --light-grey: #f8f9fa;
            --medium-grey: #6c757d;
            --dark-grey: #343a40;
            --border-grey: #dee2e6;
            --danger-red: #dc3545;
        }

        .reports-card {
            background: var(--primary-red);
            border-radius: 15px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid var(--border-grey);
            margin-bottom: 15px;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stat-label {
            color: var(--medium-grey);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .report-type-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            max-width: 200px;

            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-grow: 1;
        }

        .report-malnutrition {
            background-color: #f8d7da;
            color: var(--danger-red);
        }

        .report-growth {
            background-color: #cce5ff;
            color: #0066cc;
        }

        .report-nutrition {
            background-color: #d4edda;
            color: var(--success-green);
        }

        .report-vaccination {
            background-color: #fff3cd;
            color: #856404;
        }

        .report-progress {
            background-color: #e2e3e5;
            color: var(--dark-grey);
        }

        .report-health {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .child-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary-red);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--medium-grey);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }

        .btn-gradient {
            background: var(--primary-red);
            border: none;
            color: white;
            transition: all 0.3s;
        }

        .btn-gradient:hover {
            transform: translateY(-1px);
            background: var(--danger-red);
            color: white;
        }

        .modal-header {
            background: var(--primary-red);
            border-radius: 12px 12px 0 0;
        }

        .modal-header .modal-title {
            color: white;
        }

        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-action {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }

        .auto-calculated {
            background-color: #e8f5e8;
            border-color: #27ae60;
        }

        .details-section {
            background: var(--light-grey);
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }

        .child-info-display {
            border: 1px solid var(--border-grey);
            border-radius: 8px;
            padding: 12px 15px;
            background-color: #f8f9fa;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--dark-grey);
        }

        .child-info-display strong {
            color: var(--medium-grey);
            margin-right: 8px;
            font-weight: 500;
        }

        .search-box {
            position: relative;
            display: inline-block;
        }

        .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-grey);
        }

        .search-box input {
            padding-left: 35px;
            border: 1px solid var(--border-grey);
            border-radius: 8px;
            padding-right: 15px;
            height: 38px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .announcements-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .announcements-table th {
            background: var(--light-grey);
            color: var(--dark-grey);
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid var(--border-grey);
        }

        .announcements-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-grey);
            vertical-align: middle;
        }

        .announcements-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table-footer {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 15px 20px;
            background: var(--light-grey);
            border-top: 1px solid var(--border-grey);
        }

        .pagination {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .btn-pagination {
            padding: 8px 12px;
            border: 1px solid var(--border-grey);
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-pagination:hover:not([disabled]) {
            background: var(--primary-red);
            color: white;
            border-color: var(--primary-red);
        }

        .btn-pagination[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-number {
            padding: 8px 12px;
            border: 1px solid var(--border-grey);
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-number.active {
            background: var(--primary-red);
            color: white;
            border-color: var(--primary-red);
        }

        .page-number:hover {
            background: var(--light-grey);
        }

        .no-data {
            text-align: center;
        }

        .empty-state {
            padding: 60px 20px;
            color: var(--medium-grey);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: var(--dark-grey);
        }

        .recent-reports {
            max-height: 400px;
            overflow-y: auto;
        }

        .recent-report-item {
            padding: 12px;
            border: 1px solid var(--border-grey);
            border-radius: 8px;
            margin-bottom: 10px;
            background: white;
            transition: all 0.2s;
        }

        .recent-report-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .child-search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            max-height: 300px;
            overflow-y: auto;
        }

        .child-search-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f8f9fa;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
        }

        .child-search-item:hover {
            background-color: #f8f9fa;
        }

        .child-search-item:last-child {
            border-bottom: none;
        }

        .child-search-item.selected {
            background-color: var(--primary-red);
            color: white;
        }

        .child-avatar-small {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary-red);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .child-search-item.selected .child-avatar-small {
            background: rgba(255, 255, 255, 0.2);
        }

        .child-info {
            flex-grow: 1;
            min-width: 0;
        }

        .child-name {
            font-weight: 600;
            color: var(--dark-grey);
            margin-bottom: 2px;
        }

        .child-search-item.selected .child-name {
            color: white;
        }

        .child-details {
            font-size: 0.85rem;
            color: var(--medium-grey);
            display: flex;
            gap: 10px;
        }

        .child-search-item.selected .child-details {
            color: rgba(255, 255, 255, 0.8);
        }

        .dropdown-loading,
        .no-results {
            padding: 15px;
            text-align: center;
            color: var(--medium-grey);
            font-size: 0.9rem;
        }

        .form-floating.position-relative {
            position: relative;
        }

        /* Highlight matching text */
        .highlight {
            background-color: yellow;
            font-weight: bold;
            padding: 1px 2px;
            border-radius: 2px;
        }

        .child-search-item.selected .highlight {
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
        }
    </style>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="reports-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fa-solid fa-file-medical"></i>
                        Reports Management
                    </h1>
                    <p class="mb-0 opacity-90">Generate, manage and track child health reports</p>
                </div>
                <div class="text-end">
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm" onclick="exportReports()">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addReportModal">
                            <i class="fa-solid fa-plus"></i> Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-primary" id="totalReportsCount">0</div>
                            <div class="stat-label">Total Reports</div>
                        </div>
                        <i class="fa-solid fa-file-medical text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-warning" id="thisMonthCount">0</div>
                            <div class="stat-label">This Month</div>
                        </div>
                        <i class="fa-solid fa-calendar-days text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-success" id="thisWeekCount">0</div>
                            <div class="stat-label">This Week</div>
                        </div>
                        <i class="fa-solid fa-calendar-week text-success fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-info" id="todayCount">0</div>
                            <div class="stat-label">Today</div>
                        </div>
                        <i class="fa-solid fa-calendar-day text-info fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reports and Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="filter-section">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-chart-simple"></i>
                        Report Types Distribution
                    </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fa-solid fa-triangle-exclamation text-danger fa-3x mb-2"></i>
                                <h4 class="text-danger mb-1" id="malnutritionReports">0</h4>
                                <small class="text-muted">Malnutrition Reports</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fa-solid fa-chart-line text-success fa-3x mb-2"></i>
                                <h4 class="text-success mb-1" id="growthReports">0</h4>
                                <small class="text-muted">Growth Reports</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fa-solid fa-syringe text-info fa-3x mb-2"></i>
                                <h4 class="text-info mb-1" id="vaccinationReports">0</h4>
                                <small class="text-muted">Vaccination Reports</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fa-brands fa-nutritionix text-success fa-3x mb-2"></i>
                                <h4 class="text-success mb-1" id="nutritionCount">0</h4>
                                <small class="text-muted">Nutrition Reports</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fa-solid fa-chart-bar text-secondary fa-3x mb-2"></i>
                                <h4 class="text-secondary mb-1" id="othersCount">0</h4>
                                <small class="text-muted">Other Reports</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="filter-section">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-clock"></i>
                        Recent Reports
                    </h5>
                    <div id="recentReports" class="recent-reports">
                        <!-- Recent reports will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <h3 class="mb-0">All Reports</h3>
                <span class="badge bg-secondary" id="totalCount">0 Reports</span>
            </div>
            <div class="d-flex gap-2">
                <!-- button for zone filter -->
                <select name="zoneFilter" id="zoneFilter" class="zoneFilter form-select form-select-sm">
                    <option value="">All Zones</option>
                </select>
                <select class="form-select form-select-sm" id="reportTypeFilter">
                    <option value="">All Report Types</option>
                    <option value="Malnutrition Assessment Report">Malnutrition Assessment</option>
                    <option value="Growth Monitoring Report">Growth Monitoring</option>
                    <option value="Nutrition Status Report">Nutrition Status</option>
                    <option value="Severe Malnutrition Alert Report">Severe Malnutrition Alert</option>
                    <option value="Monthly Progress Report">Monthly Progress</option>
                    <option value="Vaccination Compliance Report">Vaccination Compliance</option>
                    <option value="Health Status Summary">Health Status Summary</option>
                    <option value="Nutrition Improvement Report">Nutrition Improvement</option>
                </select>
                <div class="search-box">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search reports...">
                </div>
            </div>
        </div>

        <div id="tableView" class="table-container">
            <div class="table-wrapper">
                <table class="announcements-table" id="reportsTable">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Child</th>
                            <th>Report Type</th>
                            <th>Generated By</th>
                            <th>Date Generated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="showing-info" id="showingInfo">
                    Showing 0 to 0 of 0 entries
                </div>
                <div class="pagination" id="paginationContainer">
                    <!-- Pagination will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add Report Modal -->
    <div class="modal fade" id="addReportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-file-medical"></i>
                        Generate New Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addReportForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3 position-relative">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="addChildSearch"
                                        placeholder="Type to search for child..."
                                        autocomplete="off"
                                        required
                                        oninput="searchChildrenForReports(this.value)"
                                        onfocus="showChildDropdownForReports()"
                                        onblur="hideChildDropdownForReports()">
                                    <label for="addChildSearch">
                                        <i class="fa-solid fa-child"></i> Search Child
                                    </label>

                                    <!-- Hidden input to store selected child ID -->
                                    <input type="hidden" id="addChildSelect" required>

                                    <!-- Dropdown results -->
                                    <div id="childSearchDropdownReports" class="child-search-dropdown" style="display: none;">
                                        <div class="dropdown-loading" id="childSearchLoadingReports" style="display: none;">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Searching...
                                        </div>
                                        <div id="childSearchResultsReports">
                                            <!-- Search results will appear here -->
                                        </div>
                                        <div class="no-results" id="childSearchNoResultsReports" style="display: none;">
                                            <i class="fa-solid fa-search"></i>
                                            No children found matching your search
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="addReportType" required>
                                        <option value="">Select Report Type</option>
                                        <option value="Malnutrition Assessment Report">Malnutrition Assessment Report</option>
                                        <option value="Growth Monitoring Report">Growth Monitoring Report</option>
                                        <option value="Nutrition Status Report">Nutrition Status Report</option>
                                        <option value="Severe Malnutrition Alert Report">Severe Malnutrition Alert Report</option>
                                        <option value="Monthly Progress Report">Monthly Progress Report</option>
                                        <option value="Vaccination Compliance Report">Vaccination Compliance Report</option>
                                        <option value="Health Status Summary">Health Status Summary</option>
                                        <option value="Nutrition Improvement Report">Nutrition Improvement Report</option>
                                    </select>
                                    <label for="addReportType">
                                        <i class="fa-solid fa-file-alt"></i> Report Type
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Child Info Display -->
                        <div id="addChildInfoDisplay" style="display: none;">
                            <div class="details-section">
                                <h6><i class="fa-solid fa-user"></i> Child Information</h6>
                                <div class="child-info-display">
                                    <strong>Name:</strong> <span id="addChildName">-</span>
                                </div>
                                <div class="child-info-display">
                                    <strong>Age:</strong> <span id="addChildAge">-</span>
                                </div>
                                <div class="child-info-display">
                                    <strong>Zone:</strong> <span id="addChildZone">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="datetime-local" class="form-control auto-calculated" id="addReportDate" readonly required>
                            <label for="addReportDate">
                                <i class="fa-solid fa-calendar"></i> Report Date (Now)
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="addReport()">
                        <i class="fa-solid fa-save"></i> Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Report Modal -->
    <div class="modal fade" id="viewReportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalTitle">
                        <i class="fa-solid fa-eye"></i>
                        Report Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewReportContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editReport()">
                        <i class="fa-solid fa-edit"></i> Edit Report
                    </button>
                    <button type="button" class="btn btn-primary" onclick="downloadReport()">
                        <i class="fa-solid fa-download"></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Report Modal -->
    <div class="modal fade" id="editReportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-edit"></i>
                        Edit Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editReportForm">
                        <input type="hidden" id="editReportId">

                        <!-- Child Info Display (Read-only) -->
                        <div class="details-section">
                            <h6><i class="fa-solid fa-user"></i> Child Information</h6>
                            <div class="child-info-display">
                                <strong>Name:</strong> <span id="editChildName">-</span>
                            </div>
                            <div class="child-info-display">
                                <strong>Age:</strong> <span id="editChildAge">-</span>
                            </div>
                            <div class="child-info-display">
                                <strong>Zone:</strong> <span id="editChildZone">-</span>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <select class="form-select" id="editReportType" required>
                                <option value="">Select Report Type</option>
                                <option value="Malnutrition Assessment Report">Malnutrition Assessment Report</option>
                                <option value="Growth Monitoring Report">Growth Monitoring Report</option>
                                <option value="Nutrition Status Report">Nutrition Status Report</option>
                                <option value="Severe Malnutrition Alert Report">Severe Malnutrition Alert Report</option>
                                <option value="Monthly Progress Report">Monthly Progress Report</option>
                                <option value="Vaccination Compliance Report">Vaccination Compliance Report</option>
                                <option value="Health Status Summary">Health Status Summary</option>
                                <option value="Nutrition Improvement Report">Nutrition Improvement Report</option>
                            </select>
                            <label for="editReportType">
                                <i class="fa-solid fa-file-alt"></i> Report Type
                            </label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="datetime-local" class="form-control" id="editReportDate" required>
                            <label for="editReportDate">
                                <i class="fa-solid fa-calendar"></i> Report Date
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="updateReport()">
                        <i class="fa-solid fa-save"></i> Update Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        class ReportsManager {
            constructor() {
                this.currentPage = 1;
                this.itemsPerPage = 10;
                this.totalRecords = 0;
                this.reports = [];
                this.children = [];
                this.zones = [];
                this.currentReport = null;
                this.init();
            }

            async init() {
                this.setupEventListeners();
                await this.loadInitialData();
                await this.loadReports();
                this.loadTableView();
                this.updateStatistics();
                this.loadRecentReports();
            }

            setupEventListeners() {
                document.getElementById('searchInput').addEventListener('input', (e) => this.searchReports(e.target.value));

                // Set current datetime for new reports
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                document.getElementById('addReportDate').value = now.toISOString().slice(0, 16);

                document.getElementById('zoneFilter').addEventListener('change', () => this.filterReports());

                document.getElementById('reportTypeFilter').addEventListener('change', () => this.filterReports());
            }

            async loadInitialData() {
                try {
                    // Load children for dropdown
                    const childrenResponse = await fetch('./flagged_data/get_children.php');
                    const childrenData = await childrenResponse.json();
                    this.children = childrenData.children || [];

                    // Load zones for dropdown
                    const zonesResponse = await fetch('./flagged_data/get_barangay_zones.php');
                    const zonesData = await zonesResponse.json();
                    this.zones = zonesData.zones || [];

                    this.populateReportsDropdown();
                } catch (error) {
                    console.error('Error loading initial data:', error);
                }
            }

            populateReportsDropdown() {
                const childSelect = document.getElementById('addChildSelect');
                childSelect.innerHTML = '<option value="">Select Child</option>';
                this.children.forEach(child => {
                    const option = document.createElement('option');
                    option.value = child.child_id;
                    option.textContent = `${child.first_name} ${child.last_name}`;
                    childSelect.appendChild(option);
                });

                const zoneFilter = document.getElementById('zoneFilter');
                zoneFilter.innerHTML = '<option value="">All Zones</option>';
                this.zones.forEach(zone => {
                    const option = document.createElement('option');
                    option.value = zone.zone_id;
                    option.textContent = zone.zone_name;
                    zoneFilter.appendChild(option);
                });
            }

            async showAddChildInfo() {
                const childId = document.getElementById('addChildSelect').value;
                const infoDisplay = document.getElementById('addChildInfoDisplay');

                if (!childId) {
                    infoDisplay.style.display = 'none';
                    return;
                }

                const child = this.children.find(c => c.child_id == childId);
                if (!child) return;

                const birthDate = new Date(child.birthdate);
                const age = this.calculateAge(birthDate);

                document.getElementById('addChildName').textContent = `${child.first_name} ${child.last_name}`;
                document.getElementById('addChildAge').textContent = `${age} years`;
                document.getElementById('addChildZone').textContent = child.zone_name || 'N/A';

                infoDisplay.style.display = 'block';
            }

            calculateAge(birthDate) {
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                return age;
            }

            async loadReports() {
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.itemsPerPage,
                        search: document.getElementById('searchInput')?.value || '',
                        report_type: document.getElementById('reportTypeFilter')?.value || '',
                        zone: document.getElementById('zoneFilter')?.value || ''
                    });

                    const response = await fetch(`./reports_data/get_reports.php?${params}`);
                    const data = await response.json();
                    console.log(data);

                    this.reports = data.reports || [];
                    this.totalRecords = data.total || 0;

                    document.getElementById('totalCount').textContent = `${this.totalRecords} Reports`;
                } catch (error) {
                    console.error('Error loading reports:', error);
                }
            }

            loadTableView() {
                const tbody = document.getElementById('reportsTableBody');
                let html = '';

                if (this.reports.length === 0) {
                    html = `<tr class="no-data">
                <td colspan="6">
                    <div class="empty-state">
                        <i class="fa-solid fa-file-medical"></i>
                        <h3>No reports found</h3>
                        <p>No reports match your current filters</p>
                    </div>
                </td>
            </tr>`;
                } else {
                    this.reports.forEach(report => {
                        const reportDate = new Date(report.report_date);
                        const initials = `${report.first_name[0]}${report.last_name[0]}`.toUpperCase();

                        html += `<tr>
                    <td>
                        <div class="fw-medium">#RPT-${String(report.report_id).padStart(4, '0')}</div>
                        <small class="text-muted">ID: ${report.report_id}</small>
                    </td>
                    <td class="name-cell">
                        <div class="d-flex align-items-center">
                            <div class="child-avatar me-3">${initials}</div>
                            <div>
                                <div class="fw-bold">${report.first_name} ${report.last_name}</div>
                                <small class="text-muted">ID: #${report.child_id}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="report-type-badge">${report.report_type}</span>
                    </td>
                    <td>
                        <div class="fw-medium">${report.generated_by_name || 'System'}</div>
                        <small class="text-muted">ID: ${report.generated_by}</small>
                    </td>
                    <td>
                        <div class="date-info">
                            <div class="fw-medium">${this.formatDate(reportDate)}</div>
                            <small class="text-muted">${this.timeAgo(reportDate)}</small>
                        </div>
                    </td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            <button class="btn-action btn-primary" title="View Report" onclick="reportsManager.viewReport(${report.report_id})">
                                <i class="fa-solid fa-eye text-white"></i>
                            </button>
                            <button class="btn-action btn-success" title="Edit Report" onclick="reportsManager.editReport(${report.report_id})">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button class="btn-action btn-danger" title="Delete Report" onclick="reportsManager.deleteReport(${report.report_id})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                    });
                }

                tbody.innerHTML = html;
                this.updatePagination();
                this.updateShowingInfo();
            }

            formatDate(dateString) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ];

                // If it's a string from database, treat as local time
                let d;
                if (typeof dateString === 'string') {
                    // Replace space with 'T' to make it ISO format, but don't add timezone
                    const isoString = dateString.replace(' ', 'T');
                    d = new Date(isoString);
                } else {
                    d = new Date(dateString);
                }

                // Format date part
                const dateStr = `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;

                // Format time part in 12-hour format
                let hours = d.getHours();
                const minutes = d.getMinutes().toString().padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';

                hours = hours % 12;
                hours = hours ? hours : 12; // the hour '0' should be '12'

                const timeStr = `${hours}:${minutes} ${ampm}`;

                return `${dateStr} ${timeStr}`;
            }

            timeAgo(date) {
                const now = new Date();
                const diffTime = Math.abs(now - date);

                const diffMinutes = Math.floor(diffTime / (1000 * 60));
                const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

                if (diffMinutes < 60) return `${diffMinutes} minutes ago`;
                if (diffHours < 24) return `${diffHours} hours ago`;
                if (diffDays === 0) return 'Today';
                if (diffDays === 1) return '1 day ago';
                if (diffDays < 7) return `${diffDays} days ago`;
                if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
                if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
                return `${Math.floor(diffDays / 365)} years ago`;
            }

            async updateStatistics() {
                try {
                    const params = new URLSearchParams({
                        search: document.getElementById('searchInput')?.value || '',
                        report_type: document.getElementById('reportTypeFilter')?.value || '',
                        zone: document.getElementById('zoneFilter')?.value || ''
                    });

                    const response = await fetch(`./reports_data/get_report_statistics.php?${params}`);
                    const stats = await response.json();

                    document.getElementById('totalReportsCount').textContent = stats.total || 0;
                    document.getElementById('thisMonthCount').textContent = stats.this_month || 0;
                    document.getElementById('thisWeekCount').textContent = stats.this_week || 0;
                    document.getElementById('todayCount').textContent = stats.today || 0;

                    // Update report type distribution
                    document.getElementById('malnutritionReports').textContent = stats.malnutrition || 0;
                    document.getElementById('growthReports').textContent = stats.growth || 0;
                    document.getElementById('vaccinationReports').textContent = stats.vaccination || 0;
                    document.getElementById('nutritionCount').textContent = stats.nutrition || 0;
                    document.getElementById('othersCount').textContent = stats.others || 0;
                } catch (error) {
                    console.error('Error updating statistics:', error);
                }
            }

            async loadRecentReports() {
                try {
                    const response = await fetch('./reports_data/get_recent_reports.php');
                    const data = await response.json();

                    const container = document.getElementById('recentReports');
                    let html = '';

                    if (data.reports && data.reports.length > 0) {
                        data.reports.forEach(report => {
                            const reportDate = new Date(report.report_date);
                            html += `
                        <div class="recent-report-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${report.first_name} ${report.last_name}</h6>
                                    <p class="mb-1 small">${report.report_type}</p>
                                    <small class="text-muted">By: ${report.generated_by_name || 'System'}</small>
                                </div>
                                <small class="text-muted">${this.timeAgo(reportDate)}</small>
                            </div>
                        </div>
                    `;
                        });
                    } else {
                        html = '<p class="text-muted text-center">No recent reports</p>';
                    }

                    container.innerHTML = html;
                } catch (error) {
                    console.error('Error loading recent reports:', error);
                }
            }

            updatePagination() {
                const totalPages = Math.ceil(this.totalRecords / this.itemsPerPage);
                const container = document.getElementById('paginationContainer');

                if (totalPages <= 1) {
                    container.innerHTML = '';
                    return;
                }

                let html = `
            <button class="btn-pagination" ${this.currentPage <= 1 ? 'disabled' : ''} 
                    onclick="reportsManager.changePage(${this.currentPage - 1})">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        `;

                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                     onclick="reportsManager.changePage(${i})">${i}</span>`;
                }

                html += `
            <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                    onclick="reportsManager.changePage(${this.currentPage + 1})">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        `;

                container.innerHTML = html;
            }

            updateShowingInfo() {
                const startItem = (this.currentPage - 1) * this.itemsPerPage + 1;
                const endItem = Math.min(this.currentPage * this.itemsPerPage, this.totalRecords);
                document.getElementById('showingInfo').textContent =
                    `Showing ${startItem} to ${endItem} of ${this.totalRecords} entries`;
            }

            async changePage(page) {
                const totalPages = Math.ceil(this.totalRecords / this.itemsPerPage);
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    await this.loadReports();
                    this.loadTableView();
                }
            }

            async searchReports(query) {
                this.currentPage = 1;
                await this.loadReports();
                this.loadTableView();
                this.updateStatistics();
            }

            async filterReports() {
                this.currentPage = 1;
                await this.loadReports();
                this.loadTableView();
                this.updateStatistics();
            }

            async viewReport(reportId) {
                try {
                    const response = await fetch(`./reports_data/get_report.php?report_id=${reportId}`);
                    const data = await response.json();

                    if (data.report) {
                        this.currentReport = data.report;
                        this.showViewModal(data.report);
                    }
                } catch (error) {
                    console.error('Error loading report:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load report details.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            showViewModal(report) {
                const reportDate = new Date(report.report_date);
                const child = this.children.find(c => c.child_id == report.child_id);
                const age = child ? this.calculateAge(new Date(child.birthdate)) : 'N/A';

                const content = `
            <div class="row">
                <div class="col-md-6">
                    <div class="details-section">
                        <h6><i class="fa-solid fa-user"></i> Child Information</h6>
                        <div class="child-info-display">
                            <strong>Name:</strong> ${report.first_name} ${report.last_name}
                        </div>
                        <div class="child-info-display">
                            <strong>Age:</strong> ${age} years
                        </div>
                        <div class="child-info-display">
                            <strong>Gender:</strong> ${child?.gender || 'N/A'}
                        </div>
                        <div class="child-info-display">
                            <strong>Zone:</strong> ${child?.zone_name || 'N/A'}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="details-section">
                        <h6><i class="fa-solid fa-file-medical"></i> Report Information</h6>
                        <div class="child-info-display">
                            <strong>Report ID:</strong> #RPT-${String(report.report_id).padStart(4, '0')}
                        </div>
                        <div class="child-info-display">
                            <strong>Report Type:</strong> ${report.report_type}
                        </div>
                        <div class="child-info-display">
                            <strong>Generated By:</strong> ${report.generated_by_name || 'System'}
                        </div>
                        <div class="child-info-display">
                            <strong>Date Generated:</strong> ${this.formatDate(reportDate)}
                        </div>
                    </div>
                </div>
            </div>
        `;

                document.getElementById('viewReportContent').innerHTML = content;
                document.getElementById('viewModalTitle').innerHTML = `
            <i class="fa-solid fa-eye"></i>
            ${report.report_type} - ${report.first_name} ${report.last_name}
        `;

                const modal = new bootstrap.Modal(document.getElementById('viewReportModal'));
                modal.show();
            }

            async editReport(reportId) {
                try {
                    const response = await fetch(`./reports_data/get_report.php?report_id=${reportId}`);
                    const data = await response.json();

                    if (data.report) {
                        this.currentReport = data.report;
                        this.showEditModal(data.report);
                    }
                } catch (error) {
                    console.error('Error loading report for editing:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load report for editing.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            showEditModal(report) {
                const child = this.children.find(c => c.child_id == report.child_id);
                const age = child ? this.calculateAge(new Date(child.birthdate)) : 'N/A';
                let reportDate;

                if (typeof report.report_date === 'string') {
                    // Replace space with 'T' to make it ISO format, but don't add timezone
                    const isoString = report.report_date.replace(' ', 'T');
                    reportDate = new Date(isoString);
                } else {
                    reportDate = new Date(report.report_date);
                }

                // Populate form
                document.getElementById('editReportId').value = report.report_id;
                document.getElementById('editChildName').textContent = `${report.first_name} ${report.last_name}`;
                document.getElementById('editChildAge').textContent = `${age} years`;
                document.getElementById('editChildZone').textContent = child?.zone_name || 'N/A';
                document.getElementById('editReportType').value = report.report_type;

                // Format datetime for input - adjust for timezone to show accurate local time
                const year = reportDate.getFullYear();
                const month = String(reportDate.getMonth() + 1).padStart(2, '0');
                const day = String(reportDate.getDate()).padStart(2, '0');
                const hours = String(reportDate.getHours()).padStart(2, '0');
                const minutes = String(reportDate.getMinutes()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;
                document.getElementById('editReportDate').value = formattedDate;

                const modal = new bootstrap.Modal(document.getElementById('editReportModal'));
                modal.show();
            }

            async deleteReport(reportId) {
                const result = await Swal.fire({
                    title: 'Delete Report?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (result.isConfirmed) {
                    try {
                        $.ajax({
                            url: '../backend/admin/reports/delete_report.php',
                            type: 'POST',
                            data: {
                                report_id: reportId
                            },
                            success: async (response) => {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Report has been deleted.',
                                        confirmButtonColor: '#27ae60'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Failed to delete report.',
                                        confirmButtonColor: '#dc3545'
                                    });
                                }
                            },
                            error: (xhr, status, error) => {
                                console.error('AJAX Error:', status, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while deleting the report.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        });
                    } catch (error) {
                        console.error('Error deleting report:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the report.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }
            }
        }

        // Initialize reports manager
        let reportsManager;
        document.addEventListener('DOMContentLoaded', function() {
            reportsManager = new ReportsManager();
        });

        // Global functions
        function showAddChildInfo() {
            reportsManager.showAddChildInfo();
        }

        // Update the addReport function to clear form on success
        async function addReport() {
            const childId = document.getElementById('addChildSelect').value;
            const reportType = document.getElementById('addReportType').value;
            const reportDate = document.getElementById('addReportDate').value;

            if (!childId || !reportType || !reportDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Validate report date is not in the future
            const currentDateTime = new Date();
            const selectedDateTime = new Date(reportDate);
            if (selectedDateTime > currentDateTime) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Report date cannot be in the future.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            try {
                $.ajax({
                    url: '../backend/admin/reports/add_report.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        child_id: childId,
                        report_type: reportType,
                        report_date: reportDate
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Generated!',
                                text: 'Report generated successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
                                // Clear the form before hiding modal
                                clearAddReportForm();

                                bootstrap.Modal.getInstance(document.getElementById('addReportModal')).hide();
                                reportsManager.loadReports().then(() => {
                                    reportsManager.loadTableView();
                                    reportsManager.updateStatistics();
                                    reportsManager.loadRecentReports();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to generate report.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error generating report:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while generating the report.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            } catch (error) {
                console.error('Error generating report:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while generating the report.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        async function updateReport() {
            const reportId = document.getElementById('editReportId').value;
            const reportType = document.getElementById('editReportType').value;
            const reportDate = document.getElementById('editReportDate').value;

            if (!reportId || !reportType || !reportDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }


            const currentDateTime = new Date();
            const selectedDateTime = new Date(reportDate);
            if (selectedDateTime > currentDateTime) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Report date cannot be in the future.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            try {
                $.ajax({
                    url: '../backend/admin/reports/update_report.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        report_id: reportId,
                        report_type: reportType,
                        report_date: reportDate
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'Report updated successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
                                bootstrap.Modal.getInstance(document.getElementById('editReportModal')).hide();
                                reportsManager.loadReports().then(() => {
                                    reportsManager.loadTableView();
                                    reportsManager.updateStatistics();
                                    reportsManager.loadRecentReports();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to update report.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating report:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating the report.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            } catch (error) {
                console.error('Error updating report:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the report.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        function editReport() {
            if (reportsManager.currentReport) {
                bootstrap.Modal.getInstance(document.getElementById('viewReportModal')).hide();
                setTimeout(() => {
                    reportsManager.editReport(reportsManager.currentReport.report_id);
                }, 300);
            }
        }

        function downloadReport() {
            if (reportsManager.currentReport) {
                window.open(`./reports_data/download_report.php?report_id=${reportsManager.currentReport.report_id}`, '_blank');
            }
        }

        // Fixed export function
        function exportReports() {
            Swal.fire({
                title: 'Export Reports',
                html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">Report Type Filter:</label>
                    <select id="exportReportTypeFilter" class="form-select">
                        <option value="">All Report Types</option>
                        <option value="Malnutrition Assessment Report">Malnutrition Assessment</option>
                        <option value="Growth Monitoring Report">Growth Monitoring</option>
                        <option value="Nutrition Status Report">Nutrition Status</option>
                        <option value="Severe Malnutrition Alert Report">Severe Malnutrition Alert</option>
                        <option value="Monthly Progress Report">Monthly Progress</option>
                        <option value="Vaccination Compliance Report">Vaccination Compliance</option>
                        <option value="Health Status Summary">Health Status Summary</option>
                        <option value="Nutrition Improvement Report">Nutrition Improvement</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Zone Filter:</label>
                    <select id="exportZoneFilter" class="form-select">
                        <option value="">All Zones</option>
                        ${reportsManager.zones.map(zone =>
                            `<option value="${zone.zone_id}">${zone.zone_name}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date Range:</label>
                    <div class="row">
                        <div class="col-6">
                            <input type="datetime-local" id="exportStartDate" class="form-control" placeholder="Start Date">
                        </div>
                        <div class="col-6">
                            <input type="datetime-local" id="exportEndDate" class="form-control" placeholder="End Date">
                        </div>
                    </div>
                </div>
            </div>
            `,
                showCancelButton: true,
                confirmButtonText: 'Export',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#27ae60',
                cancelButtonColor: '#6c757d',
                width: '500px',
                preConfirm: () => {
                    const reportType = document.getElementById('exportReportTypeFilter').value;
                    const zone = document.getElementById('exportZoneFilter').value;
                    const startDate = document.getElementById('exportStartDate').value;
                    const endDate = document.getElementById('exportEndDate').value;

                    // Validate date range
                    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                        Swal.showValidationMessage('Start date must be before end date');
                        return false;
                    }

                    return {
                        report_type: reportType,
                        zone: zone,
                        start_date: startDate,
                        end_date: endDate
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const filters = result.value;

                    // Build query parameters
                    const params = new URLSearchParams();
                    if (filters.report_type) params.append('report_type', filters.report_type);
                    if (filters.zone) params.append('zone', filters.zone);
                    if (filters.start_date) params.append('start_date', filters.start_date);
                    if (filters.end_date) params.append('end_date', filters.end_date);

                    // Open export URL with filters
                    const exportUrl = `./reports_data/export_reports.php?${params.toString()}`;
                    window.open(exportUrl, '_blank');

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Export Started',
                        text: 'Your filtered reports are being exported.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        // Global variables for child search in reports
        let childSearchTimeoutReports = null;
        let selectedChildIndexReports = -1;
        let filteredChildrenReports = [];
        let childDropdownVisibleReports = false;

        // Enhanced search functionality for reports
        function searchChildrenForReports(query) {
            clearTimeout(childSearchTimeoutReports);

            const dropdown = document.getElementById('childSearchDropdownReports');
            const resultsContainer = document.getElementById('childSearchResultsReports');
            const loadingIndicator = document.getElementById('childSearchLoadingReports');
            const noResults = document.getElementById('childSearchNoResultsReports');

            if (!query || query.length < 2) {
                dropdown.style.display = 'none';
                document.getElementById('addChildSelect').value = '';
                hideChildInfoReports();
                return;
            }

            // Show dropdown and loading
            dropdown.style.display = 'block';
            loadingIndicator.style.display = 'block';
            noResults.style.display = 'none';
            resultsContainer.innerHTML = '';

            childSearchTimeoutReports = setTimeout(() => {
                performChildSearchReports(query);
                loadingIndicator.style.display = 'none';
            }, 300);
        }

        function performChildSearchReports(query) {
            const searchQuery = query.toLowerCase().trim();

            // Filter children based on name, age, or zone
            filteredChildrenReports = reportsManager.children.filter(child => {
                const fullName = `${child.first_name} ${child.last_name}`.toLowerCase();
                const age = reportsManager.calculateAge(new Date(child.birthdate));
                const zone = (child.zone_name || '').toLowerCase();
                const childId = child.child_id.toString();

                return fullName.includes(searchQuery) ||
                    age.toString().includes(searchQuery) ||
                    zone.includes(searchQuery) ||
                    childId.includes(searchQuery);
            });

            selectedChildIndexReports = -1;
            displaySearchResultsReports(searchQuery);
        }

        function displaySearchResultsReports(searchQuery) {
            const resultsContainer = document.getElementById('childSearchResultsReports');
            const noResults = document.getElementById('childSearchNoResultsReports');

            if (filteredChildrenReports.length === 0) {
                noResults.style.display = 'block';
                resultsContainer.innerHTML = '';
                return;
            }

            noResults.style.display = 'none';
            let html = '';

            // Limit results to prevent overwhelming UI
            const displayChildren = filteredChildrenReports.slice(0, 10);

            displayChildren.forEach((child, index) => {
                const birthDate = new Date(child.birthdate);
                const age = reportsManager.calculateAge(birthDate);
                const initials = `${child.first_name[0]}${child.last_name[0]}`.toUpperCase();
                const fullName = `${child.first_name} ${child.last_name}`;

                // Highlight matching text
                const highlightedName = highlightMatchReports(fullName, searchQuery);
                const highlightedZone = highlightMatchReports(child.zone_name || 'N/A', searchQuery);

                html += `
            <div class="child-search-item" 
                 data-child-id="${child.child_id}" 
                 data-index="${index}"
                 onmousedown="selectChildForReports(${child.child_id}, '${fullName}', ${age}, '${child.zone_name || 'N/A'}')"
                 onmouseover="highlightSearchItemReports(${index})">
                <div class="child-avatar-small">${initials}</div>
                <div class="child-info">
                    <div class="child-name">${highlightedName}</div>
                    <div class="child-details">
                        <span>${age} years old</span>
                        <span>Zone: ${highlightedZone}</span>
                        <span>ID: #${child.child_id}</span>
                    </div>
                </div>
            </div>
        `;
            });

            if (filteredChildrenReports.length > 10) {
                html += `
            <div class="dropdown-loading">
                <i class="fa-solid fa-info-circle"></i>
                Showing first 10 results. Refine your search for more specific results.
            </div>
        `;
            }

            resultsContainer.innerHTML = html;
        }

        function highlightMatchReports(text, query) {
            if (!text || !query) return text;

            const regex = new RegExp(`(${escapeRegExpReports(query)})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        }

        function escapeRegExpReports(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function selectChildForReports(childId, childName, age, zone) {
            // Set values
            document.getElementById('addChildSearch').value = childName;
            document.getElementById('addChildSelect').value = childId;

            // Update child info display
            document.getElementById('addChildName').textContent = childName;
            document.getElementById('addChildAge').textContent = `${age} years`;
            document.getElementById('addChildZone').textContent = zone;

            // Show child info
            document.getElementById('addChildInfoDisplay').style.display = 'block';

            // Hide dropdown
            hideChildDropdownForReports();
        }

        function showChildDropdownForReports() {
            childDropdownVisibleReports = true;
            const query = document.getElementById('addChildSearch').value;
            if (query && query.length >= 2) {
                document.getElementById('childSearchDropdownReports').style.display = 'block';
            }
        }

        function hideChildDropdownForReports() {
            // Small delay to allow for click events
            setTimeout(() => {
                if (!childDropdownVisibleReports) return;
                document.getElementById('childSearchDropdownReports').style.display = 'none';
                childDropdownVisibleReports = false;
            }, 150);
        }

        function hideChildInfoReports() {
            document.getElementById('addChildInfoDisplay').style.display = 'none';
        }

        function highlightSearchItemReports(index) {
            // Remove previous highlights
            document.querySelectorAll('.child-search-item.selected').forEach(item => {
                item.classList.remove('selected');
            });

            // Highlight current item
            const items = document.querySelectorAll('.child-search-item');
            if (items[index]) {
                items[index].classList.add('selected');
                selectedChildIndexReports = index;
            }
        }

        // Enhanced keyboard navigation for reports
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('addChildSearch');

            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    const dropdown = document.getElementById('childSearchDropdownReports');

                    if (dropdown.style.display === 'none') return;

                    const items = document.querySelectorAll('.child-search-item');

                    switch (e.key) {
                        case 'ArrowDown':
                            e.preventDefault();
                            selectedChildIndexReports = Math.min(selectedChildIndexReports + 1, items.length - 1);
                            updateSelectedItemReports(items);
                            break;

                        case 'ArrowUp':
                            e.preventDefault();
                            selectedChildIndexReports = Math.max(selectedChildIndexReports - 1, -1);
                            updateSelectedItemReports(items);
                            break;

                        case 'Enter':
                            e.preventDefault();
                            if (selectedChildIndexReports >= 0 && items[selectedChildIndexReports]) {
                                const selectedItem = items[selectedChildIndexReports];
                                const childId = selectedItem.dataset.childId;
                                const child = reportsManager.children.find(c => c.child_id == childId);
                                if (child) {
                                    const age = reportsManager.calculateAge(new Date(child.birthdate));
                                    selectChildForReports(child.child_id, `${child.first_name} ${child.last_name}`, age, child.zone_name || 'N/A');
                                }
                            }
                            break;

                        case 'Escape':
                            hideChildDropdownForReports();
                            break;
                    }
                });
            }
        });

        function updateSelectedItemReports(items) {
            // Remove all selections
            items.forEach(item => item.classList.remove('selected'));

            // Add selection to current item
            if (selectedChildIndexReports >= 0 && items[selectedChildIndexReports]) {
                items[selectedChildIndexReports].classList.add('selected');

                // Scroll into view if needed
                items[selectedChildIndexReports].scrollIntoView({
                    block: 'nearest',
                    behavior: 'smooth'
                });
            }
        }

        // Clear form function for reports
        function clearAddReportForm() {
            // Reset form fields
            document.getElementById('addChildSearch').value = '';
            document.getElementById('addChildSelect').value = '';
            document.getElementById('addReportType').value = '';

            // Reset current datetime
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('addReportDate').value = now.toISOString().slice(0, 16);

            // Hide child info display and dropdown
            document.getElementById('addChildInfoDisplay').style.display = 'none';
            document.getElementById('childSearchDropdownReports').style.display = 'none';

            // Reset search state
            selectedChildIndexReports = -1;
            filteredChildrenReports = [];
            childDropdownVisibleReports = false;
        }

        // Update the showAddChildInfo function to work with the new search system
        function showAddChildInfo() {
            const childId = document.getElementById('addChildSelect').value;
            const infoDisplay = document.getElementById('addChildInfoDisplay');

            if (!childId) {
                infoDisplay.style.display = 'none';
                return;
            }

            const child = reportsManager.children.find(c => c.child_id == childId);
            if (!child) return;

            const birthDate = new Date(child.birthdate);
            const age = reportsManager.calculateAge(birthDate);

            document.getElementById('addChildName').textContent = `${child.first_name} ${child.last_name}`;
            document.getElementById('addChildAge').textContent = `${age} years`;
            document.getElementById('addChildZone').textContent = child.zone_name || 'N/A';

            infoDisplay.style.display = 'block';
        }





        // Add event listener to clear form when modal is hidden
        document.addEventListener('DOMContentLoaded', function() {
            const addReportModal = document.getElementById('addReportModal');
            if (addReportModal) {
                addReportModal.addEventListener('hidden.bs.modal', function() {
                    clearAddReportForm();
                });
            }
        });
    </script>
</body>

</html>