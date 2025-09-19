<!DOCTYPE html>
<html lang="en">
<?php
include "../backend/config.php";
session_start();

if (!isset($_SESSION['user_id']) && $_SESSION["role_id"] != 2) {
    header("Location: ../components/login.php");
    exit();
}

$all_types = "SELECT * FROM tbl_flagged_type";
$types_result = mysqli_query($conn, $all_types);
$flagged_types = [];
if ($types_result) {
    while ($row = mysqli_fetch_assoc($types_result)) {
        $flagged_types[] = $row;
    }
} else {
    die("Error fetching flagged types: " . mysqli_error($conn));
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

    <title>Flagged Records Management</title>
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

        .flagged-card {
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

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-active {
            background-color: var(--light-red);
            color: white;
        }

        .status-under-review {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .status-resolved {
            background-color: #d4edda;
            color: var(--success-green);
            border: 1px solid var(--success-green);
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

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .issue-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .issue-underweight {
            background-color: #fff3cd;
            color: #856404;
        }

        .issue-overweight {
            background-color: var(--light-red);
            color: white;
        }

        .issue-severely-underweight {
            background-color: #f8d7da;
            color: var(--danger-red);
        }

        .issue-vaccination {
            background-color: #cce5ff;
            color: #0066cc;
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

        .priority-high {
            color: var(--danger-red);
        }

        .priority-medium {
            color: var(--warning-orange);
        }

        .priority-low {
            color: var(--success-green);
        }

        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .chart-filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .chart-filter-btn {
            padding: 8px 16px;
            border: 1px solid var(--border-grey);
            background: white;
            border-radius: 8px;
            color: var(--dark-grey);
            cursor: pointer;
            transition: all 0.2s;
        }

        .chart-filter-btn.active {
            background: var(--primary-red);
            color: white;
            border-color: var(--primary-red);
        }

        .chart-filter-btn:hover {
            background: var(--light-grey);
        }

        .timeline-item {
            border-left: 3px solid var(--border-grey);
            padding-left: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary-red);
        }

        .timeline-active::before {
            background: var(--danger-red);
        }

        .timeline-resolved::before {
            background: var(--success-green);
        }

        .timeline-review::before {
            background: var(--warning-orange);
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

        .resolution-notes {
            background: var(--light-grey);
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
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

        .resolution-type-card {
            border: 2px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            background: white;
        }

        .resolution-type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .resolution-type-card.selected {
            border-color: var(--primary-red);
            background-color: rgba(45, 90, 61, 0.1);
        }

        .resolution-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
            color: white;
        }

        .improved-icon {
            background-color: var(--success-green);
        }

        .transferred-icon {
            background-color: var(--primary-blue);
        }

        .referral-icon {
            background-color: var(--warning-orange);
        }

        .other-icon {
            background-color: var(--primary-red);
        }

        .verification-section {
            background: #4a7c59;
            border: 1px solid #4a7c59;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .verification-section h6 {
            color: white;
            font-weight: 500;
        }

        .current-measurements {
            background: #e8f5e8;
            border: 1px solid var(--success-green);
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }

        .form-select.disabled,
        .form-control.disabled {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }

        .form-select:disabled,
        .form-control:disabled {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }

        .type-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            border: 1px solid var(--border-grey);
            border-radius: 8px;
            margin-bottom: 8px;
            background: white;
            transition: all 0.2s;
        }

        .type-item:hover {
            background: var(--light-grey);
            transform: translateY(-1px);
        }

        .type-name {
            flex-grow: 1;
            font-weight: 500;
            color: var(--dark-grey);
        }

        .type-actions {
            display: flex;
            gap: 5px;
        }

        .type-count {
            background: var(--primary-red);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
            margin-right: 10px;
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
        <div class="flagged-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fa-solid fa-flag"></i>
                        Flagged Records Management
                    </h1>
                    <p class="mb-0 opacity-90">Monitor and manage flagged health concerns and issues</p>
                </div>
                <div class="text-end">
                    <div class="d-flex gap-2 align-items-start">
                        <button class="btn btn-light btn-md" onclick="exportFlaggedRecords()">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                        <div class="d-flex flex-column gap-2">
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addFlaggedRecordModal">
                                <i class="fa-solid fa-plus"></i> Add Flagged Record
                            </button>
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addFlaggedTypeModal">
                                <i class="fa-solid fa-cog"></i>
                                Manage Issue Types
                            </button>
                        </div>
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
                            <div class="stat-number text-danger" id="activeFlagsCount">0</div>
                            <div class="stat-label">Active Flags</div>
                        </div>
                        <i class="fa-solid fa-exclamation-triangle text-danger fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-warning" id="underReviewCount">0</div>
                            <div class="stat-label">Under Review</div>
                        </div>
                        <i class="fa-solid fa-clock text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-success" id="resolvedCount">0</div>
                            <div class="stat-label">Resolved</div>
                        </div>
                        <i class="fa-solid fa-check-circle text-success fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-info" id="thisWeekCount">0</div>
                            <div class="stat-label">This Week</div>
                        </div>
                        <i class="fa-solid fa-calendar-week text-info fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Filters Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="filter-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-chart-pie"></i>
                            Flagged Records Analytics
                        </h5>
                        <div class="chart-filter-buttons">
                            <button class="chart-filter-btn active" onclick="updateChart('status')" data-filter="status">By Status</button>
                            <button class="chart-filter-btn" onclick="updateChart('issue')" data-filter="issue">By Issue Type</button>
                            <button class="chart-filter-btn" onclick="updateChart('trend')" data-filter="trend">Trends</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="flaggedChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="filter-section" style="height: 520px;">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-filter"></i>
                        Recent Activity
                    </h5>
                    <div id="recentActivity" style="max-height: 450px; overflow-y: auto;">
                        <!-- Recent activity will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <h3 class="mb-0">Flagged Records</h3>
                <span class="badge bg-secondary" id="totalFlaggedCount">0 Records</span>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="statusFilter" onchange="filterRecords()">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Under Review">Under Review</option>
                    <option value="Resolved">Resolved</option>
                </select>
                <select class="form-select form-select-sm" id="issueFilter" onchange="filterRecords()">
                    <option value="">All Issue Types</option>
                    <?php foreach ($flagged_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type['ft_id']); ?>">
                            <?php echo htmlspecialchars($type['flagged_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="search-box">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search children or issues...">
                </div>
            </div>
        </div>

        <div id="tableView" class="table-container">
            <div class="table-wrapper">
                <table class="announcements-table" id="flaggedTable">
                    <thead>
                        <tr>
                            <th>Child</th>
                            <th>Zone</th>
                            <th>Issue Type</th>
                            <th>Status</th>
                            <th>Date Flagged</th>
                            <th>Priority</th>
                            <th>Days Open</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="flaggedTableBody">
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

    <!-- Add Flagged Record Modal -->
    <div class="modal fade" id="addFlaggedRecordModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-flag"></i>
                        Add Flagged Record
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addFlaggedForm">
                        <div class="row">
                            <!-- Replace the existing child select dropdown in the Add Flagged Record Modal -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3 position-relative">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="addChildSearch"
                                        placeholder="Type to search for child..."
                                        autocomplete="off"
                                        required
                                        oninput="searchChildren(this.value)"
                                        onfocus="showChildDropdown()"
                                        onblur="hideChildDropdown()">
                                    <label for="addChildSearch">
                                        <i class="fa-solid fa-child"></i> Search Child
                                    </label>

                                    <!-- Hidden input to store selected child ID -->
                                    <input type="hidden" id="addChildSelect" required>

                                    <!-- Dropdown results -->
                                    <div id="childSearchDropdown" class="child-search-dropdown" style="display: none;">
                                        <div class="dropdown-loading" id="childSearchLoading" style="display: none;">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Searching...
                                        </div>
                                        <div id="childSearchResults">
                                            <!-- Search results will appear here -->
                                        </div>
                                        <div class="no-results" id="childSearchNoResults" style="display: none;">
                                            <i class="fa-solid fa-search"></i>
                                            No children found matching your search
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="addIssueType" required>
                                        <option value="">Select Issue Type</option>
                                        <?php foreach ($flagged_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type['ft_id']); ?>">
                                                <?php echo htmlspecialchars($type['flagged_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="addIssueType">
                                        <i class="fa-solid fa-exclamation-triangle"></i> Issue Type
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control auto-calculated" id="addDateFlagged" readonly required>
                                    <label for="addDateFlagged">
                                        <i class="fa-solid fa-calendar"></i> Date Flagged (Today)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="addFlaggedStatus" required>
                                        <option value="Active">Active</option>
                                        <option value="Under Review">Under Review</option>
                                    </select>
                                    <label for="addFlaggedStatus">
                                        <i class="fa-solid fa-flag"></i> Status
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="addDescription" style="height: 100px" placeholder="Additional details about the issue..."></textarea>
                            <label for="addDescription">
                                <i class="fa-solid fa-comment"></i> Description (Optional)
                            </label>
                        </div>

                        <!-- Medicine Administration Section -->
                        <div class="medicine-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6><i class="fa-solid fa-pills"></i> Medicine Administration (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-success" onclick="addMedicineEntry()">
                                    <i class="fa-solid fa-plus"></i> Add Medicine
                                </button>
                            </div>
                            <div id="addMedicineEntries">
                                <!-- Medicine entries will be added here -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="addFlaggedRecord()">
                        <i class="fa-solid fa-save"></i> Flag Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View/Edit Flagged Record Modal -->
    <div class="modal fade" id="viewFlaggedModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalTitle">
                        <i class="fa-solid fa-eye"></i>
                        Flagged Record Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewFlaggedContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editFlaggedRecord()">
                        <i class="fa-solid fa-edit"></i> Edit Record
                    </button>
                    <button type="button" id="markResolved" class="btn btn-success" onclick="resolveFlaggedRecord()">
                        <i class="fa-solid fa-check"></i> Mark as Resolved
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Flagged Record Modal -->
    <div class="modal fade" id="editFlaggedModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-edit"></i>
                        Edit Flagged Record
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editFlaggedForm">
                        <input type="hidden" id="editFlaggedId">

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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="editIssueType" required>
                                        <option value="">Select Issue Type</option>
                                        <?php foreach ($flagged_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type['ft_id']); ?>">
                                                <?php echo htmlspecialchars($type['flagged_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="editIssueType">
                                        <i class="fa-solid fa-exclamation-triangle"></i> Issue Type
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="editFlaggedStatus" required>
                                        <option value="Active">Active</option>
                                        <option value="Under Review">Under Review</option>
                                        <option value="Resolved">Resolved</option>
                                    </select>
                                    <label for="editFlaggedStatus">
                                        <i class="fa-solid fa-flag"></i> Status
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="editDateFlagged" readonly>
                            <label for="editDateFlagged">
                                <i class="fa-solid fa-calendar"></i> Date Flagged
                            </label>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="editDescription" style="height: 100px" placeholder="Additional details about the issue..."></textarea>
                            <label for="editDescription">
                                <i class="fa-solid fa-comment"></i> Description
                            </label>
                        </div>

                        <!-- Medicine Administration Section -->
                        <div class="medicine-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6><i class="fa-solid fa-pills"></i> Medicine Administration</h6>
                                <button type="button" class="btn btn-sm btn-success" onclick="addEditMedicineEntry()">
                                    <i class="fa-solid fa-plus"></i> Add Medicine
                                </button>
                            </div>

                            <!-- Existing Medicine Records -->
                            <div id="editExistingMedicines">
                                <!-- Existing medicine records will be loaded here -->
                            </div>

                            <!-- New Medicine Entries -->
                            <div id="editMedicineEntries">
                                <!-- New medicine entries will be added here -->
                            </div>
                        </div>

                        <div id="resolutionSection" style="display: none;">
                            <div class="resolution-notes">
                                <h6><i class="fa-solid fa-check-circle"></i> Resolution Details</h6>

                                <!-- Resolution Type Selection -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">How was this issue resolved?</label>
                                    <div class="resolution-type-card" onclick="selectResolutionType('improved')" data-type="improved">
                                        <div class="d-flex align-items-center">
                                            <div class="resolution-icon improved-icon">
                                                <i class="fa-solid fa-heart-pulse"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Child's Condition Improved</h6>
                                                <small class="text-muted">Child has recovered or condition normalized</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resolution-type-card" onclick="selectResolutionType('transferred')" data-type="transferred">
                                        <div class="d-flex align-items-center">
                                            <div class="resolution-icon transferred-icon">
                                                <i class="fa-solid fa-exchange-alt"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Transferred to Another Program</h6>
                                                <small class="text-muted">Child moved to different barangay or program</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resolution-type-card" onclick="selectResolutionType('referral')" data-type="referral">
                                        <div class="d-flex align-items-center">
                                            <div class="resolution-icon referral-icon">
                                                <i class="fa-solid fa-hospital"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Referred to Medical Facility</h6>
                                                <small class="text-muted">Case referred to hospital or clinic for specialized care</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resolution-type-card" onclick="selectResolutionType('other')" data-type="other">
                                        <div class="d-flex align-items-center">
                                            <div class="resolution-icon other-icon">
                                                <i class="fa-solid fa-ellipsis-h"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Other Reason</h6>
                                                <small class="text-muted">Different reason for resolution</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id="resolutionType" required>

                                <!-- Verification Section (shows for improved cases) -->
                                <div id="verificationSection" style="display: none;">
                                    <div class="verification-section">
                                        <h6><i class="fa-solid fa-clipboard-check"></i> Verification Required</h6>
                                        <p class="mb-3 text-warning">
                                            <i class="fa-solid fa-exclamation-triangle"></i>
                                            Please confirm the current status of the child
                                        </p>
                                        <div class="form-floating mb-3">
                                            <select class="form-control" id="currentStatus" required>
                                                <option value="">Select Current Status</option>
                                                <option value="Normal Weight">Normal Weight</option>
                                                <option value="Improved but Still Monitoring">Improved but Still Monitoring</option>
                                                <option value="Vaccination Completed">Vaccination Completed</option>
                                                <option value="Issue Fully Resolved">Issue Fully Resolved</option>
                                            </select>
                                            <label for="currentStatus">Current Status *</label>
                                        </div>
                                        <div class="current-measurements" id="statusConfirmation" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="fa-solid fa-info-circle text-info me-2"></i>
                                                <span>Status updated and verified</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="resolutionNotes" style="height: 100px" placeholder="Enter detailed resolution notes..." required></textarea>
                                    <label for="resolutionNotes">Resolution Notes *</label>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="resolutionDate" required>
                                            <label for="resolutionDate">Date Resolved *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="followUpSection" style="display: none;">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="followUpDate">
                                            <label for="followUpDate">Follow-up Date</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="updateFlaggedRecord()">
                        <i class="fa-solid fa-save"></i> Update Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Flagged Type Modal -->
    <div class="modal fade" id="addFlaggedTypeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="flaggedTypeModalTitle">
                        <i class="fa-solid fa-cog"></i>
                        Manage Issue Types
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Add New Type Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="details-section">
                                <h6><i class="fa-solid fa-plus"></i> Add New Issue Type</h6>
                                <form id="addTypeForm" class="row g-3">
                                    <div class="col-md-8">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="newTypeName" placeholder="Issue Type Name" required>
                                            <label for="newTypeName">Issue Type Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-gradient w-100 h-100" onclick="addFlaggedType()">
                                            <i class="fa-solid fa-plus"></i> Add Type
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Types List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="details-section">
                                <h6><i class="fa-solid fa-list"></i> Current Issue Types</h6>
                                <div id="flaggedTypesList" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Types will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Flagged Type Modal -->
    <div class="modal fade" id="editFlaggedTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color:#2d5a3d;">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-edit"></i>
                        Edit Issue Type
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editTypeForm">
                        <input type="hidden" id="editTypeId">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="editTypeName" placeholder="Issue Type Name" required>
                            <label for="editTypeName">Issue Type Name</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateFlaggedType()">
                        <i class="fa-solid fa-save"></i> Update
                    </button>
                </div>
            </div>
        </div>

        <script>
            class FlaggedRecordsManager {
                constructor() {
                    this.currentPage = 1;
                    this.itemsPerPage = 10;
                    this.totalRecords = 0;
                    this.records = [];
                    this.children = [];
                    this.currentChartType = 'status';
                    this.chart = null;
                    this.currentRecord = null;
                    this.flaggedTypes = <?php echo json_encode($flagged_types); ?>;
                    this.zones = [];
                    this.init();
                }

                async init() {
                    this.setupEventListeners();
                    await this.loadInitialData();
                    await this.loadRecords();
                    this.loadTableView();
                    this.updateStatistics();
                    this.loadRecentActivity();
                    this.initializeChart();
                }

                setupEventListeners() {
                    document.getElementById('searchInput').addEventListener('input', (e) => this.searchRecords(e.target.value));
                    const addDateInput = document.getElementById('addDateFlagged');
                    addDateInput.type = 'datetime-local';
                    addDateInput.value = this.getLocalDateTimeString(new Date());

                    // Show resolution section when status is set to Resolved
                    document.getElementById('editFlaggedStatus').addEventListener('change', (e) => {
                        const resolutionSection = document.getElementById('resolutionSection');
                        if (e.target.value === 'Resolved') {
                            resolutionSection.style.display = 'block';
                            const resolutionDateInput = document.getElementById('resolutionDate');
                            resolutionDateInput.type = 'datetime-local';
                            resolutionDateInput.value = this.getLocalDateTimeString(new Date());
                        } else {
                            resolutionSection.style.display = 'none';
                        }
                    });

                    // Add current status change listener (only once)
                    const currentStatusElement = document.getElementById('currentStatus');
                    if (currentStatusElement) {
                        currentStatusElement.addEventListener('change', function() {
                            const confirmationElement = document.getElementById('statusConfirmation');
                            if (confirmationElement) {
                                confirmationElement.style.display = this.value ? 'block' : 'none';
                            }
                        });
                    }
                }

                getLocalDateTimeString(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');

                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                }

                async loadInitialData() {
                    try {
                        // Load children for dropdown
                        const childrenResponse = await fetch('./flagged_data/get_children.php');
                        const childrenData = await childrenResponse.json();
                        this.children = childrenData.children || [];

                        // Load zones for add child modal
                        const zonesResponse = await fetch('./flagged_data/get_barangay_zones.php');
                        const zonesData = await zonesResponse.json();
                        this.zones = zonesData.zones || [];



                        this.populateChildrenDropdown();
                    } catch (error) {
                        console.error('Error loading initial data:', error);
                    }
                }

                populateChildrenDropdown() {
                    const childSelect = document.getElementById('addChildSelect');
                    childSelect.innerHTML = '<option value="">Select Child</option>';
                    this.children.forEach(child => {
                        const option = document.createElement('option');
                        option.value = child.child_id;
                        option.textContent = `${child.first_name} ${child.last_name}`;
                        childSelect.appendChild(option);
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

                async loadRecords() {
                    try {
                        const params = new URLSearchParams({
                            page: this.currentPage,
                            limit: this.itemsPerPage,
                            search: document.getElementById('searchInput')?.value || '',
                            status: document.getElementById('statusFilter')?.value || '',
                            issue: document.getElementById('issueFilter')?.value || ''
                        });

                        const response = await fetch(`./flagged_data/get_flagged_records.php?${params}`);
                        const data = await response.json();

                        this.records = data.records || [];
                        this.totalRecords = data.total || 0;

                        document.getElementById('totalFlaggedCount').textContent = `${this.totalRecords} Records`;
                    } catch (error) {
                        console.error('Error loading records:', error);
                    }
                }

                loadTableView() {
                    const tbody = document.getElementById('flaggedTableBody');
                    let html = '';

                    if (this.records.length === 0) {
                        html = `<tr class="no-data">
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fa-solid fa-flag"></i>
                                <h3>No flagged records found</h3>
                                <p>No flagged records match your current filters</p>
                            </div>
                        </td>
                    </tr>`;
                    } else {
                        this.records.forEach(record => {
                            const birthDate = new Date(record.birthdate);
                            const age = this.calculateAge(birthDate);
                            const initials = `${record.first_name[0]}${record.last_name[0]}`.toUpperCase();
                            const statusClass = this.getStatusClass(record.flagged_status);
                            const issueClass = this.getIssueClass(record.flagged_name);
                            const flaggedDate = new Date(record.date_flagged);
                            const daysOpen = this.calculateDaysOpen(flaggedDate, record.flagged_status);
                            const priority = this.calculatePriority(record.flagged_name, daysOpen);

                            html += `<tr>
                            <td class="name-cell">
                                <div class="d-flex align-items-center">
                                    <div class="child-avatar me-3">${initials}</div>
                                    <div>
                                        <div class="fw-bold">${record.first_name} ${record.last_name}</div>
                                        <small class="text-muted">ID: #${record.child_id}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium">${record.zone_name || 'N/A'}</span>
                            </td>
                            <td>
                                <span class="issue-badge ${issueClass}">${record.flagged_name}</span>
                            </td>
                            <td>
                                <span class="status-badge ${statusClass}">${record.flagged_status}</span>
                            </td>
                            <td>
                                <div class="date-info">
                                    <div class="fw-medium">${this.formatDate(flaggedDate)}</div>
                                    <small class="text-muted">${this.timeAgo(flaggedDate)}</small>
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium ${priority.class}">${priority.text}</span>
                            </td>
                            <td class="fw-medium">${daysOpen} days</td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-action btn-primary" title="View Details" onclick="flaggedManager.viewRecord(${record.flagged_id})">
                                        <i class="fa-solid fa-eye text-white"></i>
                                    </button>
                                    <button class="btn-action btn-success" title="Edit Record" onclick="flaggedManager.editRecord(${record.flagged_id})">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-danger" title="Delete Record" onclick="flaggedManager.deleteRecord(${record.flagged_id})">
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

                getStatusClass(status) {
                    if (!status) return '';
                    const statusLower = status.toLowerCase();
                    if (statusLower.includes('active')) return 'status-active';
                    if (statusLower.includes('review')) return 'status-under-review';
                    if (statusLower.includes('resolved')) return 'status-resolved';
                    return '';
                }

                getIssueClass(issueType) {
                    if (!issueType) return '';
                    const issue = issueType.toLowerCase();
                    if (issue.includes('underweight')) return 'issue-underweight';
                    if (issue.includes('overweight')) return 'issue-overweight';
                    if (issue.includes('severely')) return 'issue-severely-underweight';
                    if (issue.includes('vaccination')) return 'issue-vaccination';
                    return 'issue-underweight'; // default
                }

                calculateDaysOpen(flaggedDate, status) {
                    if (status === 'Resolved') return 0;
                    const today = new Date();
                    const flagged = new Date(flaggedDate);

                    // Reset time to start of day for accurate day calculation
                    today.setHours(0, 0, 0, 0);
                    flagged.setHours(0, 0, 0, 0);

                    const diffTime = today - flagged;
                    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

                    return Math.max(0, diffDays); // Ensure non-negative
                }

                calculatePriority(issueType, daysOpen) {
                    const issue = issueType.toLowerCase();
                    let baseScore = 0;

                    // Issue severity
                    if (issue.includes('severely')) baseScore = 3;
                    else if (issue.includes('underweight') || issue.includes('overweight')) baseScore = 2;
                    else baseScore = 1;

                    // Time factor
                    if (daysOpen > 14) baseScore += 2;
                    else if (daysOpen > 7) baseScore += 1;

                    if (baseScore >= 4) return {
                        text: 'High',
                        class: 'priority-high'
                    };
                    if (baseScore >= 2) return {
                        text: 'Medium',
                        class: 'priority-medium'
                    };
                    return {
                        text: 'Low',
                        class: 'priority-low'
                    };
                }

                formatDate(date) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                    ];

                    const d = new Date(date);
                    return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
                }

                timeAgo(date) {
                    const now = new Date();
                    const past = new Date(date);
                    const diffTime = Math.abs(now - past);
                    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    const diffHours = Math.floor(diffTime / (1000 * 60 * 60));

                    if (diffDays === 0) {
                        if (diffHours === 0) {
                            const diffMinutes = Math.floor(diffTime / (1000 * 60));
                            if (diffMinutes === 0) return 'Just now';
                            return `${diffMinutes} minute${diffMinutes > 1 ? 's' : ''} ago`;
                        }
                        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
                    }
                    if (diffDays === 1) return '1 day ago';
                    if (diffDays < 7) return `${diffDays} days ago`;
                    if (diffDays < 30) return `${Math.floor(diffDays / 7)} week${Math.floor(diffDays / 7) > 1 ? 's' : ''} ago`;
                    if (diffDays < 365) return `${Math.floor(diffDays / 30)} month${Math.floor(diffDays / 30) > 1 ? 's' : ''} ago`;
                    return `${Math.floor(diffDays / 365)} year${Math.floor(diffDays / 365) > 1 ? 's' : ''} ago`;
                }

                async updateStatistics() {
                    try {
                        const response = await fetch('./flagged_data/get_flagged_statistics.php');
                        const stats = await response.json();

                        document.getElementById('activeFlagsCount').textContent = stats.active || 0;
                        document.getElementById('underReviewCount').textContent = stats.under_review || 0;
                        document.getElementById('resolvedCount').textContent = stats.resolved || 0;
                        document.getElementById('thisWeekCount').textContent = stats.this_week || 0;
                    } catch (error) {
                        console.error('Error updating statistics:', error);
                    }
                }

                async loadRecentActivity() {
                    try {
                        const response = await fetch('./flagged_data/get_recent_flagged_activity.php');
                        const data = await response.json();

                        const container = document.getElementById('recentActivity');
                        let html = '';

                        if (data.activities && data.activities.length > 0) {
                            data.activities.forEach(activity => {
                                const timelineClass = this.getTimelineClass(activity.flagged_status);
                                html += `
                                <div class="timeline-item ${timelineClass}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${activity.first_name} ${activity.last_name}</h6>
                                            <p class="mb-1 small">${activity.flagged_name}</p>
                                            <span class="status-badge ${this.getStatusClass(activity.flagged_status)}">${activity.flagged_status}</span>
                                        </div>
                                        <small class="text-muted">${this.timeAgo(new Date(activity.date_flagged))}</small>
                                    </div>
                                </div>
                            `;
                            });
                        } else {
                            html = '<p class="text-muted text-center">No recent activity</p>';
                        }

                        container.innerHTML = html;
                    } catch (error) {
                        console.error('Error loading recent activity:', error);
                    }
                }

                getTimelineClass(status) {
                    if (!status) return '';
                    const statusLower = status.toLowerCase();
                    if (statusLower.includes('active')) return 'timeline-active';
                    if (statusLower.includes('review')) return 'timeline-review';
                    if (statusLower.includes('resolved')) return 'timeline-resolved';
                    return '';
                }

                initializeChart() {
                    const ctx = document.getElementById('flaggedChart');
                    if (!ctx) return;

                    this.chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Active', 'Under Review', 'Resolved'],
                            datasets: [{
                                data: [0, 0, 0],
                                backgroundColor: [
                                    '#dc3545',
                                    '#ffc107',
                                    '#27ae60'
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff'
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
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });

                    this.updateChart('status');
                }

                async updateChart(type) {
                    // Update button states
                    document.querySelectorAll('.chart-filter-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    document.querySelector(`[data-filter="${type}"]`).classList.add('active');

                    this.currentChartType = type;

                    try {
                        const response = await fetch(`./flagged_data/get_flagged_chart_data.php?type=${type}`);
                        const data = await response.json();

                        if (type === 'status') {
                            this.chart.data.labels = ['Active', 'Under Review', 'Resolved'];
                            this.chart.data.datasets[0].data = [
                                data.active || 0,
                                data.under_review || 0,
                                data.resolved || 0
                            ];
                            this.chart.data.datasets[0].backgroundColor = ['#dc3545', '#ffc107', '#27ae60'];
                        } else if (type === 'issue') {
                            this.chart.data.labels = data.labels || [];
                            this.chart.data.datasets[0].data = data.values || [];
                            this.chart.data.datasets[0].backgroundColor = [
                                '#ffc107', '#dc3545', '#fd7e14', '#6f42c1', '#20c997'
                            ];
                        } else if (type === 'trend') {
                            this.chart.destroy();
                            const ctx = document.getElementById('flaggedChart');
                            this.chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: data.labels || [],
                                    datasets: [{
                                        label: 'New Flags',
                                        data: data.values || [],
                                        borderColor: '#2d5a3d',
                                        backgroundColor: 'rgba(45, 90, 61, 0.1)',
                                        tension: 0.4,
                                        fill: true
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
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                            return;
                        }

                        this.chart.update();
                    } catch (error) {
                        console.error('Error updating chart:', error);
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
                            onclick="flaggedManager.changePage(${this.currentPage - 1})">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                `;

                    const startPage = Math.max(1, this.currentPage - 2);
                    const endPage = Math.min(totalPages, this.currentPage + 2);

                    for (let i = startPage; i <= endPage; i++) {
                        html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                             onclick="flaggedManager.changePage(${i})">${i}</span>`;
                    }

                    html += `
                    <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                            onclick="flaggedManager.changePage(${this.currentPage + 1})">
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
                        await this.loadRecords();
                        this.loadTableView();
                    }
                }

                async searchRecords(query) {
                    this.currentPage = 1;
                    await this.loadRecords();
                    this.loadTableView();
                }

                async viewRecord(flaggedId) {
                    try {
                        const response = await fetch(`./flagged_data/get_flagged_record.php?flagged_id=${flaggedId}`);
                        const data = await response.json();

                        if (data.record) {
                            this.currentRecord = data.record;
                            this.showViewModal(data.record);
                        }
                    } catch (error) {
                        console.error('Error loading flagged record:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load record details.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }

                showViewModal(record) {
                    const birthDate = new Date(record.birthdate);
                    const age = this.calculateAge(birthDate);
                    const flaggedDate = new Date(record.date_flagged);
                    const daysOpen = this.calculateDaysOpen(flaggedDate, record.flagged_status);
                    const priority = this.calculatePriority(record.flagged_name, daysOpen);
                    const isResolved = record.flagged_status === 'Resolved';

                    // Replace the content variable in showViewModal function
                    const content = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="details-section">
                                    <h6><i class="fa-solid fa-user"></i> Child Information</h6>
                                    <div class="child-info-display">
                                        <strong>Name:</strong> ${record.first_name} ${record.last_name}
                                    </div>
                                    <div class="child-info-display">
                                        <strong>Age:</strong> ${age} years
                                    </div>
                                    <div class="child-info-display">
                                        <strong>Gender:</strong> ${record.gender || 'N/A'}
                                    </div>
                                    <div class="child-info-display">
                                        <strong>Zone:</strong> ${record.zone_name || 'N/A'}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="details-section">
                                    <h6><i class="fa-solid fa-flag"></i> Flag Information</h6>
                                    <div class="child-info-display">
                                        <strong>Issue Type:</strong> <span class="issue-badge ${this.getIssueClass(record.flagged_name)}">${record.flagged_name}</span>
                                    </div>
                                    <div class="child-info-display">
                                        <strong>Status:</strong> <span class="status-badge ${this.getStatusClass(record.flagged_status)}">${record.flagged_status}</span>
                                    </div>
                                    <div class="child-info-display">
                                        <strong>Priority:</strong> <span class="fw-medium ${priority.class}">${priority.text}</span>
                                    </div>
                                    <div class="child-info-display">
                                        <strong>Date Flagged:</strong> ${this.formatDate(flaggedDate)}
                                    </div>
                                    <div class="child-info-display">
                                        <strong>Days Open:</strong> ${daysOpen} days
                                    </div>
                                </div>
                            </div>
                        </div>

                        ${record.description ? `
                            <div class="details-section">
                                <h6><i class="fa-solid fa-comment"></i> Description</h6>
                                <p class="mb-0">${record.description}</p>
                            </div>
                        ` : ''}

                        <!-- Medicine Administration History -->
                        <div class="details-section" id="medicineHistorySection">
                            <h6><i class="fa-solid fa-pills"></i> Medicine Administration History</h6>
                            <div id="medicineHistoryContent">Loading...</div>
                        </div>

                        ${record.resolution_notes ? `
                            <div class="resolution-notes">
                                <h6><i class="fa-solid fa-check-circle"></i> Resolution Details</h6>
                                <p class="mb-2">${record.resolution_notes}</p>
                                <small class="text-muted">Resolved on: ${record.resolution_date ? this.formatDate(new Date(record.resolution_date)) : 'N/A'}</small>
                            </div>
                        ` : ''}
                        
                        ${record.resolution_type ? `
                            <div class="details-section">
                                <h6><i class="fa-solid fa-cogs"></i> Resolution Details</h6>
                                <div class="child-info-display">
                                    <strong>Resolution Type:</strong> ${record.resolution_type.charAt(0).toUpperCase() + record.resolution_type.slice(1).replace('_', ' ')}
                                </div>
                                ${record.current_status ? `
                                    <div class="child-info-display">
                                        <strong>Current Status:</strong> ${record.current_status}
                                    </div>
                                ` : ''}
                                ${record.follow_up_date ? `
                                    <div class="child-info-display">
                                        <strong>Follow-up Date:</strong> ${flaggedManager.formatDate(new Date(record.follow_up_date))}
                                    </div>
                                ` : ''}
                            </div>
                        ` : ''}
                    `;

                    document.getElementById('viewFlaggedContent').innerHTML = content;
                    document.getElementById('viewModalTitle').innerHTML = `
                    <i class="fa-solid fa-flag"></i>
                    ${record.first_name} ${record.last_name} - ${record.flagged_name}
                `;

                    // Disable buttons if resolved
                    const editBtn = document.querySelector('#viewFlaggedModal .btn-success');
                    const resolveBtn = document.querySelector('#viewFlaggedModal .btn-warning');

                    if (editBtn) {
                        editBtn.disabled = isResolved;
                        if (isResolved) {
                            editBtn.classList.add('disabled');
                            editBtn.title = 'Cannot edit resolved records';
                        } else {
                            editBtn.classList.remove('disabled');
                            editBtn.title = 'Edit Record';
                        }
                    }

                    if (resolveBtn) {
                        resolveBtn.disabled = isResolved;
                        if (isResolved) {
                            resolveBtn.classList.add('disabled');
                            resolveBtn.title = 'Record already resolved';
                            resolveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Resolved';
                        } else {
                            resolveBtn.classList.remove('disabled');
                            resolveBtn.title = 'Edit Record';
                            resolveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Edit Record';
                        }
                    }

                    const modal = new bootstrap.Modal(document.getElementById('viewFlaggedModal'));
                    loadMedicineHistory(record.flagged_id);
                    modal.show();
                }

                clearAddForm() {
                    // Reset form fields
                    document.getElementById('addChildSelect').value = '';
                    document.getElementById('addIssueType').value = '';
                    const addDateInput = document.getElementById('addDateFlagged');
                    addDateInput.type = 'datetime-local';
                    addDateInput.value = this.getLocalDateTimeString(new Date());

                    document.getElementById('addFlaggedStatus').value = 'Active';
                    document.getElementById('addDescription').value = '';

                    // Hide child info display
                    document.getElementById('addChildInfoDisplay').style.display = 'none';
                }

                async editRecord(flaggedId) {
                    try {
                        const response = await fetch(`./flagged_data/get_flagged_record.php?flagged_id=${flaggedId}`);
                        const data = await response.json();

                        if (data.record) {
                            this.currentRecord = data.record;
                            this.showEditModal(data.record);
                        }
                    } catch (error) {
                        console.error('Error loading flagged record:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load record for editing.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }

                showEditModal(record) {
                    const birthDate = new Date(record.birthdate);
                    const age = this.calculateAge(birthDate);
                    const isResolved = record.flagged_status === 'Resolved';

                    // Populate form
                    document.getElementById('editFlaggedId').value = record.flagged_id;
                    document.getElementById('editChildName').textContent = `${record.first_name} ${record.last_name}`;
                    document.getElementById('editChildAge').textContent = `${age} years`;
                    document.getElementById('editChildZone').textContent = record.zone_name || 'N/A';
                    document.getElementById('editIssueType').value = record.ft_id
                    document.getElementById('editFlaggedStatus').value = record.flagged_status;

                    // Set datetime-local and format the date
                    const editDateInput = document.getElementById('editDateFlagged');
                    editDateInput.type = 'datetime-local';
                    const flaggedDateTime = new Date(record.date_flagged);
                    editDateInput.value = this.getLocalDateTimeString(flaggedDateTime);

                    document.getElementById('editDescription').value = record.description || '';

                    // Clear all resolution-related fields first
                    document.getElementById('resolutionNotes').value = '';
                    const resolutionDateInput = document.getElementById('resolutionDate');
                    resolutionDateInput.type = 'datetime-local';
                    resolutionDateInput.value = '';

                    // Clear resolution type selection
                    document.querySelectorAll('.resolution-type-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    document.getElementById('resolutionType').value = '';

                    // Clear verification fields
                    document.getElementById('currentStatus').value = '';
                    const followUpInput = document.getElementById('followUpDate');
                    followUpInput.type = 'datetime-local';
                    followUpInput.value = '';

                    // Hide verification and follow-up sections
                    document.getElementById('verificationSection').style.display = 'none';
                    document.getElementById('followUpSection').style.display = 'none';
                    document.getElementById('statusConfirmation').style.display = 'none';

                    // Disable fields if record is resolved
                    const issueTypeSelect = document.getElementById('editIssueType');
                    const statusSelect = document.getElementById('editFlaggedStatus');

                    if (isResolved) {
                        issueTypeSelect.disabled = true;
                        statusSelect.disabled = true;
                        issueTypeSelect.classList.add('disabled');
                        statusSelect.classList.add('disabled');
                    } else {
                        issueTypeSelect.disabled = false;
                        statusSelect.disabled = false;
                        issueTypeSelect.classList.remove('disabled');
                        statusSelect.classList.remove('disabled');
                    }

                    // Only populate resolution fields if the record actually has resolution data
                    if (record.resolution_type) {
                        // Auto-select the resolution type if it exists
                        setTimeout(() => {
                            selectResolutionType(record.resolution_type);
                            if (record.current_status) {
                                document.getElementById('currentStatus').value = record.current_status;
                                document.getElementById('statusConfirmation').style.display = 'block';
                            }
                            if (record.follow_up_date) {
                                const followUpInput = document.getElementById('followUpDate');
                                followUpInput.type = 'datetime-local';
                                const followUpDateTime = new Date(record.follow_up_date);
                                followUpInput.value = this.getLocalDateTimeString(followUpDateTime);
                            }
                        }, 100);
                    }

                    // Handle resolution section
                    const resolutionSection = document.getElementById('resolutionSection');
                    if (record.flagged_status === 'Resolved') {
                        resolutionSection.style.display = 'block';
                        // Only set resolution notes and date if they exist for this record
                        document.getElementById('resolutionNotes').value = record.resolution_notes || '';

                        if (record.resolution_date) {
                            const resolutionDateTime = new Date(record.resolution_date);
                            resolutionDateInput.value = this.getLocalDateTimeString(resolutionDateTime);
                        } else {
                            resolutionDateInput.value = this.getLocalDateTimeString(new Date());
                        }
                    } else {
                        resolutionSection.style.display = 'none';
                    }

                    const modal = new bootstrap.Modal(document.getElementById('editFlaggedModal'));
                    loadExistingMedicines(record.flagged_id);
                    modal.show();
                }


                async deleteRecord(flaggedId) {
                    const result = await Swal.fire({
                        title: 'Delete Flagged Record?',
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
                                url: '../backend/admin/flagged/delete_flagged_record.php',
                                type: 'POST',
                                data: {
                                    flagged_id: flaggedId
                                },
                                success: async (response) => {
                                    if (response.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted!',
                                            text: 'Flagged record has been deleted.',
                                            confirmButtonColor: '#27ae60'
                                        }).then((result) => {
                                            if (result) {
                                                window.location.reload();
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: res.message || 'Failed to delete flagged record.',
                                            confirmButtonColor: '#dc3545'
                                        });
                                    }
                                },
                                error: (xhr, status, error) => {
                                    console.error('AJAX Error:', status, error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'An error occurred while deleting the record.',
                                        confirmButtonColor: '#dc3545'
                                    });
                                }
                            });
                        } catch (error) {
                            console.error('Error deleting flagged record:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while deleting the record.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    }
                }
            }

            // Initialize flagged records manager
            let flaggedManager;
            document.addEventListener('DOMContentLoaded', function() {
                flaggedManager = new FlaggedRecordsManager();
            });

            // Global functions
            function showAddChildInfo() {
                flaggedManager.showAddChildInfo();
            }

            function selectResolutionType(type) {
                // Remove selected class from all cards
                document.querySelectorAll('.resolution-type-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // Add selected class to clicked card
                document.querySelector(`[data-type="${type}"]`).classList.add('selected');

                // Set hidden input value
                document.getElementById('resolutionType').value = type;

                // Show/hide verification section based on type
                const verificationSection = document.getElementById('verificationSection');
                const followUpSection = document.getElementById('followUpSection');

                if (type === 'improved') {
                    verificationSection.style.display = 'block';
                    followUpSection.style.display = 'block';
                } else {
                    verificationSection.style.display = 'none';
                    if (type === 'referral') {
                        followUpSection.style.display = 'block';
                    } else {
                        followUpSection.style.display = 'none';
                    }
                }
            }


            async function addFlaggedRecord() {
                const childId = document.getElementById('addChildSelect').value;
                const issueType = document.getElementById('addIssueType').value;
                const dateFlagged = document.getElementById('addDateFlagged').value;
                const status = document.getElementById('addFlaggedStatus').value;
                const description = document.getElementById('addDescription').value;

                if (!childId || !issueType || !dateFlagged || !status) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Required Fields Missing',
                        text: 'Please fill in all required fields.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                // Collect medicine entries
                const medicineEntries = collectMedicineEntries();

                try {
                    $.ajax({
                        url: '../backend/admin/flagged/add_flagged_record.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            child_id: childId,
                            issue_type: issueType,
                            date_flagged: dateFlagged,
                            flagged_status: status,
                            description: description,
                            medicine_entries: JSON.stringify(medicineEntries)
                        },
                        success: function(result) {
                            if (result.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Added!',
                                    text: 'Flagged record added successfully.',
                                    confirmButtonColor: '#27ae60'
                                }).then(() => {
                                    // Clear the form before hiding modal
                                    flaggedManager.clearAddForm();
                                    clearMedicineEntries();

                                    bootstrap.Modal.getInstance(document.getElementById('addFlaggedRecordModal')).hide();
                                    flaggedManager.loadRecords().then(() => {
                                        flaggedManager.loadTableView();
                                        flaggedManager.updateStatistics();
                                        flaggedManager.loadRecentActivity();
                                        flaggedManager.updateChart(flaggedManager.currentChartType);
                                    });
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message || 'Failed to add flagged record.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error adding flagged record:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while adding the flagged record.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                } catch (error) {
                    console.error('Error adding flagged record:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while adding the flagged record.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            async function updateFlaggedRecord() {
                const flaggedId = document.getElementById('editFlaggedId').value;
                const issueType = document.getElementById('editIssueType').value;
                const status = document.getElementById('editFlaggedStatus').value;
                const description = document.getElementById('editDescription').value;
                const resolutionNotes = document.getElementById('resolutionNotes').value;
                const resolutionDate = document.getElementById('resolutionDate').value;

                // Get resolution type from form
                const resolutionType = document.getElementById('resolutionType')?.value || '';
                const currentStatus = document.getElementById('currentStatus')?.value || '';
                const followUpDate = document.getElementById('followUpDate')?.value || '';

                // Collect new medicine entries
                const medicineEntries = collectEditMedicineEntries();

                // Validation
                if (!flaggedId || !issueType || !status) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Required Fields Missing',
                        text: 'Please fill in all required fields.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                // Additional validation for resolved status
                if (status === 'Resolved') {
                    if (!resolutionNotes) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Resolution Required',
                            text: 'Please provide resolution notes when marking as resolved.',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }

                    if (resolutionType === 'improved' && !currentStatus) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Status Verification Required',
                            text: 'Please select the current status to verify improvement.',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }
                }

                // Date validation
                const now = new Date();
                if (resolutionDate && new Date(resolutionDate) > now) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Resolution Date',
                        text: 'Resolution date cannot be in the future.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                if (followUpDate && new Date(followUpDate) < now) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Follow-Up Date',
                        text: 'Follow-up date cannot be in the past.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                try {
                    const areYouSure = await Swal.fire({
                        title: 'Confirm Update',
                        text: "Are you sure you want to update this flagged record?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, update it!'
                    });

                    if (!areYouSure.isConfirmed) {
                        return;
                    }

                    // Show loading indicator
                    const loadingSwal = Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait while we update the record.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '../backend/admin/flagged/update_flagged_record.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            flagged_id: flaggedId,
                            issue_type: issueType,
                            flagged_status: status,
                            description: description,
                            resolution_notes: resolutionNotes,
                            resolution_date: resolutionDate,
                            resolution_type: resolutionType,
                            current_status: currentStatus,
                            follow_up_date: followUpDate,
                            medicine_entries: JSON.stringify(medicineEntries)
                        },
                        success: function(result) {
                            loadingSwal.close();

                            if (result.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: 'Flagged record updated successfully.',
                                    confirmButtonColor: '#27ae60'
                                }).then(() => {
                                    // Close modal
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('editFlaggedModal'));
                                    if (modal) {
                                        modal.hide();
                                    }

                                    // Clear medicine entries
                                    clearMedicineEntries();

                                    // Refresh data
                                    refreshFlaggedData();
                                });
                            } else {
                                console.error('Error updating flagged record:', result);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message || 'Failed to update flagged record.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            loadingSwal.close();
                            console.error('Error updating flagged record:', error);
                            console.error('Response:', xhr.responseText);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while updating the flagged record.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                } catch (error) {
                    console.error('Error updating flagged record:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating the flagged record.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            // Separate function to refresh data - prevents circular calls
            async function refreshFlaggedData() {
                try {
                    await flaggedManager.loadRecords();
                    flaggedManager.loadTableView();
                    flaggedManager.updateStatistics();
                    flaggedManager.loadRecentActivity();
                    flaggedManager.updateChart(flaggedManager.currentChartType);
                } catch (error) {
                    console.error('Error refreshing data:', error);
                }
            }

            // Separate function to refresh data - prevents circular calls
            async function refreshFlaggedData() {
                try {
                    await flaggedManager.loadRecords();
                    flaggedManager.loadTableView();
                    flaggedManager.updateStatistics();
                    flaggedManager.loadRecentActivity();
                    flaggedManager.updateChart(flaggedManager.currentChartType);
                } catch (error) {
                    console.error('Error refreshing data:', error);
                }
            }

            function editFlaggedRecord() {
                if (flaggedManager.currentRecord) {
                    bootstrap.Modal.getInstance(document.getElementById('viewFlaggedModal')).hide();
                    setTimeout(() => {
                        flaggedManager.editRecord(flaggedManager.currentRecord.flagged_id);
                    }, 300);
                }
            }

            function resolveFlaggedRecord() {
                if (flaggedManager.currentRecord) {
                    bootstrap.Modal.getInstance(document.getElementById('viewFlaggedModal')).hide();
                    setTimeout(() => {
                        flaggedManager.editRecord(flaggedManager.currentRecord.flagged_id);
                        setTimeout(() => {
                            document.getElementById('editFlaggedStatus').value = 'Resolved';
                            document.getElementById('editFlaggedStatus').dispatchEvent(new Event('change'));
                        }, 100);
                    }, 300);
                }
            }

            function updateChart(type) {
                flaggedManager.updateChart(type);
            }

            async function filterRecords() {
                flaggedManager.currentPage = 1;
                await flaggedManager.loadRecords();
                flaggedManager.loadTableView();
            }

            // Add these functions to your existing JavaScript

            // Load flagged types in the modal
            async function loadFlaggedTypes() {
                try {
                    const response = await fetch('./flagged_data/get_flagged_types.php');
                    const data = await response.json();
                    console.log('Flagged Types Response:', data);

                    const container = document.getElementById('flaggedTypesList');
                    let html = '';

                    if (data.types && data.types.length > 0) {
                        data.types.forEach(type => {
                            html += `
                    <div class="type-item">
                        <div class="type-name">${type.flagged_name}</div>
                        <div class="d-flex align-items-center">
                            <span class="type-count" title="Records using this type">${type.usage_count || 0}</span>
                            <div class="type-actions">
                                <button class="btn-action btn-warning" title="Edit Type" onclick="editFlaggedType(${type.ft_id}, '${type.flagged_name}')">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button class="btn-action btn-danger" title="Delete Type" onclick="deleteFlaggedType(${type.ft_id}, '${type.flagged_name}', ${type.usage_count || 0})">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                        });
                    } else {
                        html = '<p class="text-muted text-center py-3">No issue types found</p>';
                    }

                    container.innerHTML = html;
                } catch (error) {
                    console.error('Error loading flagged types:', error);
                }
            }

            // Add new flagged type
            async function addFlaggedType() {
                const typeName = document.getElementById('newTypeName').value.trim();

                if (!typeName) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Required Field Missing',
                        text: 'Please enter an issue type name.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                try {
                    $.ajax({
                        url: '../backend/admin/flagged/add_flagged_type.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            flagged_name: typeName
                        },
                        success: function(result) {
                            if (result.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Added!',
                                    text: 'Issue type added successfully.',
                                    confirmButtonColor: '#27ae60',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Clear form
                                document.getElementById('newTypeName').value = '';

                                // Reload types list
                                loadFlaggedTypes();

                                // Update dropdowns in main form
                                updateFlaggedTypeDropdowns();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message || 'Failed to add issue type.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error adding flagged type:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while adding the issue type.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                } catch (error) {
                    console.error('Error adding flagged type:', error);
                }
            }

            // Edit flagged type
            function editFlaggedType(typeId, typeName) {
                document.getElementById('editTypeId').value = typeId;
                document.getElementById('editTypeName').value = typeName;

                const modal = new bootstrap.Modal(document.getElementById('editFlaggedTypeModal'));
                modal.show();
            }

            // Update flagged type
            async function updateFlaggedType() {
                const typeId = document.getElementById('editTypeId').value;
                const typeName = document.getElementById('editTypeName').value.trim();

                if (!typeName) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Required Field Missing',
                        text: 'Please enter an issue type name.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                try {
                    $.ajax({
                        url: '../backend/admin/flagged/update_flagged_type.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            ft_id: typeId,
                            flagged_name: typeName
                        },
                        success: function(result) {
                            if (result.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: 'Issue type updated successfully.',
                                    confirmButtonColor: '#27ae60',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then((result) => {
                                    if (result) {
                                        window.location.reload();
                                    }
                                })


                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message || 'Failed to update issue type.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error updating flagged type:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while updating the issue type.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                } catch (error) {
                    console.error('Error updating flagged type:', error);
                }
            }

            // Delete flagged type
            async function deleteFlaggedType(typeId, typeName, usageCount) {
                let confirmText = `Are you sure you want to delete "${typeName}"?`;
                let warningText = '';

                if (usageCount > 0) {
                    warningText = `This issue type is currently used in ${usageCount} flagged record${usageCount > 1 ? 's' : ''}. `;
                    confirmText = warningText + 'Deleting it will affect these records. Continue?';
                }

                const result = await Swal.fire({
                    title: 'Delete Issue Type?',
                    text: confirmText,
                    icon: usageCount > 0 ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (result.isConfirmed) {
                    try {
                        $.ajax({
                            url: '../backend/admin/flagged/delete_flagged_type.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                ft_id: typeId
                            },
                            success: function(result) {
                                if (result.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Issue type deleted successfully.',
                                        confirmButtonColor: '#27ae60',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    // Reload types list
                                    loadFlaggedTypes();

                                    // Update dropdowns in main form
                                    updateFlaggedTypeDropdowns();

                                    // Refresh the main table if needed
                                    if (usageCount > 0) {
                                        refreshFlaggedData();
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: result.message || 'Failed to delete issue type.',
                                        confirmButtonColor: '#dc3545'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error deleting flagged type:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while deleting the issue type.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        });
                    } catch (error) {
                        console.error('Error deleting flagged type:', error);
                    }
                }
            }

            // Update all flagged type dropdowns
            async function updateFlaggedTypeDropdowns() {
                try {
                    const response = await fetch('./flagged_data/get_flagged_types.php');
                    const data = await response.json();

                    if (data.types) {
                        // Update add form dropdown
                        const addSelect = document.getElementById('addIssueType');
                        const editSelect = document.getElementById('editIssueType');
                        const filterSelect = document.getElementById('issueFilter');

                        // Clear and repopulate dropdowns
                        [addSelect, editSelect].forEach(select => {
                            if (select) {
                                const selectedValue = select.value;
                                select.innerHTML = '<option value="">Select Issue Type</option>';
                                data.types.forEach(type => {
                                    const option = document.createElement('option');
                                    option.value = type.ft_id;
                                    option.textContent = type.flagged_name;
                                    select.appendChild(option);
                                });
                                select.value = selectedValue; // Restore selection
                            }
                        });

                        // Update filter dropdown
                        if (filterSelect) {
                            const selectedValue = filterSelect.value;
                            filterSelect.innerHTML = '<option value="">All Issue Types</option>';
                            data.types.forEach(type => {
                                const option = document.createElement('option');
                                option.value = type.ft_id;
                                option.textContent = type.flagged_name;
                                filterSelect.appendChild(option);
                            });
                            filterSelect.value = selectedValue; // Restore selection
                        }
                    }
                } catch (error) {
                    console.error('Error updating dropdowns:', error);
                }
            }

            // Add event listener to load types when modal is shown
            document.getElementById('addFlaggedTypeModal').addEventListener('shown.bs.modal', function() {
                loadFlaggedTypes();
            });

            function exportFlaggedRecords() {
                Swal.fire({
                    title: 'Export Flagged Records',
                    html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="form-label">Status Filter:</label>
                            <select id="exportStatusFilter" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Active">Active</option>
                                <option value="Under Review">Under Review</option>
                                <option value="Resolved">Resolved</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Issue Type Filter:</label>
                            <select id="exportIssueTypeFilter" class="form-select">
                                <option value="">All Issue Types</option>
                                ${flaggedManager.flaggedTypes.map(type => `
                                    <option value="${type.ft_id}">${type.flagged_name}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority Filter:</label>
                            <select id="exportPriorityFilter" class="form-select">
                                <option value="">All Priorities</option>
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Zone Filter:</label>
                            <select id="exportZoneFilter" class="form-select">
                                <option value="">All Zones</option>
                                ${flaggedManager.zones.map(zone => `
                                    <option value="${zone.zone_id}">${zone.zone_name}</option>
                                `).join('')}
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
                        const status = document.getElementById('exportStatusFilter').value;
                        const issueType = document.getElementById('exportIssueTypeFilter').value;
                        const priority = document.getElementById('exportPriorityFilter').value;
                        const zone = document.getElementById('exportZoneFilter').value;
                        const startDate = document.getElementById('exportStartDate').value;
                        const endDate = document.getElementById('exportEndDate').value;

                        return {
                            status: status,
                            issue_type: issueType,
                            priority: priority,
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
                        if (filters.status) params.append('status', filters.status);
                        if (filters.flagged_name) params.append('issue_type', filters.flagged_name);
                        if (filters.priority) params.append('priority', filters.priority);
                        if (filters.zone) params.append('zone', filters.zone);
                        if (filters.search) params.append('search', filters.search);
                        if (filters.start_date) params.append('start_date', filters.start_date);
                        if (filters.end_date) params.append('end_date', filters.end_date);

                        // Open export URL with filters
                        const exportUrl = `./flagged_data/export_flagged_records.php?${params.toString()}`;
                        window.open(exportUrl, '_blank');

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Export Started',
                            text: 'Your filtered flagged records are being exported.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            }

            // Global variables for child search
            let childSearchTimeout = null;
            let selectedChildIndex = -1;
            let filteredChildren = [];
            let childDropdownVisible = false;

            // Enhanced search functionality
            function searchChildren(query) {
                clearTimeout(childSearchTimeout);

                const dropdown = document.getElementById('childSearchDropdown');
                const resultsContainer = document.getElementById('childSearchResults');
                const loadingIndicator = document.getElementById('childSearchLoading');
                const noResults = document.getElementById('childSearchNoResults');

                if (!query || query.length < 2) {
                    dropdown.style.display = 'none';
                    document.getElementById('addChildSelect').value = '';
                    hideChildInfo();
                    return;
                }

                // Show dropdown and loading
                dropdown.style.display = 'block';
                loadingIndicator.style.display = 'block';
                noResults.style.display = 'none';
                resultsContainer.innerHTML = '';

                childSearchTimeout = setTimeout(() => {
                    performChildSearch(query);
                    loadingIndicator.style.display = 'none';
                }, 300);
            }

            function performChildSearch(query) {
                const searchQuery = query.toLowerCase().trim();

                // Filter children based on name, age, or zone
                filteredChildren = flaggedManager.children.filter(child => {
                    const fullName = `${child.first_name} ${child.last_name}`.toLowerCase();
                    const age = flaggedManager.calculateAge(new Date(child.birthdate));
                    const zone = (child.zone_name || '').toLowerCase();
                    const childId = child.child_id.toString();

                    return fullName.includes(searchQuery) ||
                        age.toString().includes(searchQuery) ||
                        zone.includes(searchQuery) ||
                        childId.includes(searchQuery);
                });

                selectedChildIndex = -1;
                displaySearchResults(searchQuery);
            }

            function displaySearchResults(searchQuery) {
                const resultsContainer = document.getElementById('childSearchResults');
                const noResults = document.getElementById('childSearchNoResults');

                if (filteredChildren.length === 0) {
                    noResults.style.display = 'block';
                    resultsContainer.innerHTML = '';
                    return;
                }

                noResults.style.display = 'none';
                let html = '';

                // Limit results to prevent overwhelming UI
                const displayChildren = filteredChildren.slice(0, 10);

                displayChildren.forEach((child, index) => {
                    const birthDate = new Date(child.birthdate);
                    const age = flaggedManager.calculateAge(birthDate);
                    const initials = `${child.first_name[0]}${child.last_name[0]}`.toUpperCase();
                    const fullName = `${child.first_name} ${child.last_name}`;

                    // Highlight matching text
                    const highlightedName = highlightMatch(fullName, searchQuery);
                    const highlightedZone = highlightMatch(child.zone_name || 'N/A', searchQuery);

                    html += `
            <div class="child-search-item" 
                 data-child-id="${child.child_id}" 
                 data-index="${index}"
                 onmousedown="selectChild(${child.child_id}, '${fullName}', ${age}, '${child.zone_name || 'N/A'}')"
                 onmouseover="highlightSearchItem(${index})">
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

                if (filteredChildren.length > 10) {
                    html += `
            <div class="dropdown-loading">
                <i class="fa-solid fa-info-circle"></i>
                Showing first 10 results. Refine your search for more specific results.
            </div>
        `;
                }

                resultsContainer.innerHTML = html;
            }

            function highlightMatch(text, query) {
                if (!text || !query) return text;

                const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            }

            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function selectChild(childId, childName, age, zone) {
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
                hideChildDropdown();
            }

            function showChildDropdown() {
                childDropdownVisible = true;
                const query = document.getElementById('addChildSearch').value;
                if (query && query.length >= 2) {
                    document.getElementById('childSearchDropdown').style.display = 'block';
                }
            }

            function hideChildDropdown() {
                // Small delay to allow for click events
                setTimeout(() => {
                    if (!childDropdownVisible) return;
                    document.getElementById('childSearchDropdown').style.display = 'none';
                    childDropdownVisible = false;
                }, 150);
            }

            function hideChildInfo() {
                document.getElementById('addChildInfoDisplay').style.display = 'none';
            }

            function highlightSearchItem(index) {
                // Remove previous highlights
                document.querySelectorAll('.child-search-item.selected').forEach(item => {
                    item.classList.remove('selected');
                });

                // Highlight current item
                const items = document.querySelectorAll('.child-search-item');
                if (items[index]) {
                    items[index].classList.add('selected');
                    selectedChildIndex = index;
                }
            }

            // Enhanced keyboard navigation
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('addChildSearch');

                if (searchInput) {
                    searchInput.addEventListener('keydown', function(e) {
                        const dropdown = document.getElementById('childSearchDropdown');

                        if (dropdown.style.display === 'none') return;

                        const items = document.querySelectorAll('.child-search-item');

                        switch (e.key) {
                            case 'ArrowDown':
                                e.preventDefault();
                                selectedChildIndex = Math.min(selectedChildIndex + 1, items.length - 1);
                                updateSelectedItem(items);
                                break;

                            case 'ArrowUp':
                                e.preventDefault();
                                selectedChildIndex = Math.max(selectedChildIndex - 1, -1);
                                updateSelectedItem(items);
                                break;

                            case 'Enter':
                                e.preventDefault();
                                if (selectedChildIndex >= 0 && items[selectedChildIndex]) {
                                    const selectedItem = items[selectedChildIndex];
                                    const childId = selectedItem.dataset.childId;
                                    const child = flaggedManager.children.find(c => c.child_id == childId);
                                    if (child) {
                                        const age = flaggedManager.calculateAge(new Date(child.birthdate));
                                        selectChild(child.child_id, `${child.first_name} ${child.last_name}`, age, child.zone_name || 'N/A');
                                    }
                                }
                                break;

                            case 'Escape':
                                hideChildDropdown();
                                break;
                        }
                    });
                }
            });

            function updateSelectedItem(items) {
                // Remove all selections
                items.forEach(item => item.classList.remove('selected'));

                // Add selection to current item
                if (selectedChildIndex >= 0 && items[selectedChildIndex]) {
                    items[selectedChildIndex].classList.add('selected');

                    // Scroll into view if needed
                    items[selectedChildIndex].scrollIntoView({
                        block: 'nearest',
                        behavior: 'smooth'
                    });
                }
            }

            // Update the clearAddForm function to also clear the search
            function clearAddForm() {
                // Reset form fields
                document.getElementById('addChildSearch').value = '';
                document.getElementById('addChildSelect').value = '';
                document.getElementById('addIssueType').value = '';
                const addDateInput = document.getElementById('addDateFlagged');
                addDateInput.type = 'datetime-local';
                addDateInput.value = flaggedManager.getLocalDateTimeString(new Date());

                document.getElementById('addFlaggedStatus').value = 'Active';
                document.getElementById('addDescription').value = '';

                // Hide child info display and dropdown
                document.getElementById('addChildInfoDisplay').style.display = 'none';
                document.getElementById('childSearchDropdown').style.display = 'none';

                // Reset search state
                selectedChildIndex = -1;
                filteredChildren = [];
                childDropdownVisible = false;
            }

            // Update the showAddChildInfo function to work with the new search system
            function showAddChildInfo() {
                const childId = document.getElementById('addChildSelect').value;
                const infoDisplay = document.getElementById('addChildInfoDisplay');

                if (!childId) {
                    infoDisplay.style.display = 'none';
                    return;
                }

                const child = flaggedManager.children.find(c => c.child_id == childId);
                if (!child) return;

                const birthDate = new Date(child.birthdate);
                const age = flaggedManager.calculateAge(birthDate);

                document.getElementById('addChildName').textContent = `${child.first_name} ${child.last_name}`;
                document.getElementById('addChildAge').textContent = `${age} years`;
                document.getElementById('addChildZone').textContent = child.zone_name || 'N/A';

                infoDisplay.style.display = 'block';
            }


            // Medicine Administration Functions
            let medicineEntryCount = 0;
            let editMedicineEntryCount = 0;

            // Load available medicines for dropdowns
            async function loadMedicines() {
                try {
                    const response = await fetch('./flagged_data/get_medicines.php');
                    const data = await response.json();
                    console.log('Medicines loaded:', data.medicines);
                    return data.medicines || [];
                } catch (error) {
                    console.error('Error loading medicines:', error);
                    return [];
                }
            }

            // Add medicine entry in Add modal
            async function addMedicineEntry() {
                const medicines = await loadMedicines();
                const container = document.getElementById('addMedicineEntries');
                medicineEntryCount++;

                const medicineOptions = medicines.map(med =>
                    `<option value="${med.medicine_id}">${med.medicine_name} - ${med.brand || med.generic_name || 'No brand'}</option>`
                ).join('');

                const entryHtml = `
                    <div class="medicine-entry" id="medicineEntry${medicineEntryCount}">
                        <div class="medicine-entry-header">
                            <h6><i class="fa-solid fa-pills"></i> Medicine Entry ${medicineEntryCount}</h6>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeMedicineEntry(${medicineEntryCount})">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-control medicine-select" id="medicine${medicineEntryCount}" required onchange="updateMedicineInfo(${medicineEntryCount})">
                                        <option value="">Select Medicine</option>
                                        ${medicineOptions}
                                    </select>
                                    <label for="medicine${medicineEntryCount}">Medicine *</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="quantity${medicineEntryCount}" step="0.01" min="0.01" required>
                                    <label for="quantity${medicineEntryCount}">Quantity *</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="dosage${medicineEntryCount}" placeholder="e.g., 1 tablet every 8 hours">
                                    <label for="dosage${medicineEntryCount}">Dosage Instructions</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="datetime-local" class="form-control" id="dateAdministered${medicineEntryCount}" required>
                                    <label for="dateAdministered${medicineEntryCount}">Date Administered *</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="frequency${medicineEntryCount}" placeholder="e.g., 3 times daily">
                                    <label for="frequency${medicineEntryCount}">Frequency</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="duration${medicineEntryCount}" placeholder="e.g., 7 days">
                                    <label for="duration${medicineEntryCount}">Duration</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="medicine-info" id="medicineInfo${medicineEntryCount}" style="display: none;">
                                    <small class="text-muted">
                                        <strong>Available:</strong> <span class="stock-quantity">0</span> <span class="unit"></span><br>
                                        <strong>Form:</strong> <span class="dosage-form">-</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="notes${medicineEntryCount}" style="height: 80px" placeholder="Additional notes about administration..."></textarea>
                            <label for="notes${medicineEntryCount}">Notes</label>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', entryHtml);

                // Set current datetime
                const dateInput = document.getElementById(`dateAdministered${medicineEntryCount}`);
                dateInput.value = flaggedManager.getLocalDateTimeString(new Date());
            }

            // Remove medicine entry
            function removeMedicineEntry(entryId) {
                const entry = document.getElementById(`medicineEntry${entryId}`);
                if (entry) {
                    entry.remove();
                }
            }

            // Update medicine info when medicine is selected
            async function updateMedicineInfo(entryId) {
                const medicineSelect = document.getElementById(`medicine${entryId}`);
                const medicineInfo = document.getElementById(`medicineInfo${entryId}`);

                if (!medicineSelect.value) {
                    medicineInfo.style.display = 'none';
                    return;
                }

                try {
                    const response = await fetch(`./flagged_data/get_medicine_details.php?medicine_id=${medicineSelect.value}`);
                    const data = await response.json();

                    if (data.medicine) {
                        const med = data.medicine;
                        medicineInfo.querySelector('.stock-quantity').textContent = med.stock_quantity;
                        medicineInfo.querySelector('.unit').textContent = med.unit || '';
                        medicineInfo.querySelector('.dosage-form').textContent = med.dosage_form || '-';
                        medicineInfo.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error loading medicine details:', error);
                }
            }

            // Add medicine entry in Edit modal
            async function addEditMedicineEntry() {
                const medicines = await loadMedicines();
                const container = document.getElementById('editMedicineEntries');
                editMedicineEntryCount++;

                const medicineOptions = medicines.map(med =>
                    `<option value="${med.medicine_id}">${med.medicine_name} - ${med.brand || med.generic_name || 'No brand'}</option>`
                ).join('');

                const entryHtml = `
                    <div class="medicine-entry" id="editMedicineEntry${editMedicineEntryCount}">
                        <div class="medicine-entry-header">
                            <h6><i class="fa-solid fa-pills"></i> New Medicine Entry ${editMedicineEntryCount}</h6>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeEditMedicineEntry(${editMedicineEntryCount})">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-control medicine-select" id="editMedicine${editMedicineEntryCount}" required onchange="updateEditMedicineInfo(${editMedicineEntryCount})">
                                        <option value="">Select Medicine</option>
                                        ${medicineOptions}
                                    </select>
                                    <label for="editMedicine${editMedicineEntryCount}">Medicine *</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="editQuantity${editMedicineEntryCount}" step="0.01" min="0.01" required>
                                    <label for="editQuantity${editMedicineEntryCount}">Quantity *</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editDosage${editMedicineEntryCount}" placeholder="e.g., 1 tablet every 8 hours">
                                    <label for="editDosage${editMedicineEntryCount}">Dosage Instructions</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="datetime-local" class="form-control" id="editDateAdministered${editMedicineEntryCount}" required>
                                    <label for="editDateAdministered${editMedicineEntryCount}">Date Administered *</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editFrequency${editMedicineEntryCount}" placeholder="e.g., 3 times daily">
                                    <label for="editFrequency${editMedicineEntryCount}">Frequency</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editDuration${editMedicineEntryCount}" placeholder="e.g., 7 days">
                                    <label for="editDuration${editMedicineEntryCount}">Duration</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="medicine-info" id="editMedicineInfo${editMedicineEntryCount}" style="display: none;">
                                    <small class="text-muted">
                                        <strong>Available:</strong> <span class="stock-quantity">0</span> <span class="unit"></span><br>
                                        <strong>Form:</strong> <span class="dosage-form">-</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="editNotes${editMedicineEntryCount}" style="height: 80px" placeholder="Additional notes about administration..."></textarea>
                            <label for="editNotes${editMedicineEntryCount}">Notes</label>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', entryHtml);

                // Set current datetime
                const dateInput = document.getElementById(`editDateAdministered${editMedicineEntryCount}`);
                dateInput.value = flaggedManager.getLocalDateTimeString(new Date());
            }

            // Remove edit medicine entry
            function removeEditMedicineEntry(entryId) {
                const entry = document.getElementById(`editMedicineEntry${entryId}`);
                if (entry) {
                    entry.remove();
                }
            }

            // Update medicine info for edit modal
            async function updateEditMedicineInfo(entryId) {
                const medicineSelect = document.getElementById(`editMedicine${entryId}`);
                const medicineInfo = document.getElementById(`editMedicineInfo${entryId}`);

                if (!medicineSelect.value) {
                    medicineInfo.style.display = 'none';
                    return;
                }

                try {
                    const response = await fetch(`./flagged_data/get_medicine_details.php?medicine_id=${medicineSelect.value}`);
                    const data = await response.json();

                    if (data.medicine) {
                        const med = data.medicine;
                        medicineInfo.querySelector('.stock-quantity').textContent = med.stock_quantity;
                        medicineInfo.querySelector('.unit').textContent = med.unit || '';
                        medicineInfo.querySelector('.dosage-form').textContent = med.dosage_form || '-';
                        medicineInfo.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error loading medicine details:', error);
                }
            }

            // Collect medicine entries from add form
            function collectMedicineEntries() {
                const entries = [];
                const medicineEntries = document.querySelectorAll('#addMedicineEntries .medicine-entry');

                medicineEntries.forEach(entry => {
                    const entryId = entry.id.replace('medicineEntry', '');
                    const medicineId = document.getElementById(`medicine${entryId}`).value;
                    const quantity = document.getElementById(`quantity${entryId}`).value;
                    const dosage = document.getElementById(`dosage${entryId}`).value;
                    const dateAdministered = document.getElementById(`dateAdministered${entryId}`).value;
                    const frequency = document.getElementById(`frequency${entryId}`).value;
                    const duration = document.getElementById(`duration${entryId}`).value;
                    const notes = document.getElementById(`notes${entryId}`).value;

                    if (medicineId && quantity && dateAdministered) {
                        entries.push({
                            medicine_id: medicineId,
                            quantity: parseFloat(quantity),
                            dosage_instructions: dosage,
                            date_administered: dateAdministered,
                            frequency: frequency,
                            duration: duration,
                            notes: notes
                        });
                    }
                });

                return entries;
            }

            // Collect medicine entries from edit form (new entries only)
            function collectEditMedicineEntries() {
                const entries = [];
                const medicineEntries = document.querySelectorAll('#editMedicineEntries .medicine-entry');

                medicineEntries.forEach(entry => {
                    const entryId = entry.id.replace('editMedicineEntry', '');
                    const medicineId = document.getElementById(`editMedicine${entryId}`).value;
                    const quantity = document.getElementById(`editQuantity${entryId}`).value;
                    const dosage = document.getElementById(`editDosage${entryId}`).value;
                    const dateAdministered = document.getElementById(`editDateAdministered${entryId}`).value;
                    const frequency = document.getElementById(`editFrequency${entryId}`).value;
                    const duration = document.getElementById(`editDuration${entryId}`).value;
                    const notes = document.getElementById(`editNotes${entryId}`).value;

                    if (medicineId && quantity && dateAdministered) {
                        entries.push({
                            medicine_id: medicineId,
                            quantity: parseFloat(quantity),
                            dosage_instructions: dosage,
                            date_administered: dateAdministered,
                            frequency: frequency,
                            duration: duration,
                            notes: notes
                        });
                    }
                });

                return entries;
            }

            // Load medicine history for view/edit modal
            async function loadMedicineHistory(flaggedId) {
                try {
                    const response = await fetch(`./flagged_data/get_medicine_history.php?flagged_id=${flaggedId}`);
                    const data = await response.json();

                    const container = document.getElementById('medicineHistoryContent');
                    let html = '';

                    if (data.medicines && data.medicines.length > 0) {
                        html = `
                <div class="medicine-history-list">
                    ${data.medicines.map(med => `
                        <div class="medicine-history-item mb-2 border p-3 rounded shadow-sm">
                            <div class="medicine-header">
                                <h6><i class="fa-solid fa-pills"></i> ${med.medicine_name}</h6>
                                <span class="medicine-date">${flaggedManager.formatDate(new Date(med.date_administered))}</span>
                            </div>
                            <div class="medicine-details">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Quantity:</strong> ${med.quantity_given} ${med.unit || ''}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Dosage:</strong> ${med.dosage_instructions || 'N/A'}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Frequency:</strong> ${med.frequency || 'N/A'}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Duration:</strong> ${med.duration || 'N/A'}
                                    </div>
                                </div>
                                ${med.notes ? `
                                    <div class="medicine-notes mt-2">
                                        <strong>Notes:</strong> ${med.notes}
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
                    } else {
                        html = '<p class="text-muted">No medicine administration records found.</p>';
                    }

                    container.innerHTML = html;
                } catch (error) {
                    console.error('Error loading medicine history:', error);
                    document.getElementById('medicineHistoryContent').innerHTML = '<p class="text-danger">Error loading medicine history.</p>';
                }
            }

            // Load existing medicines for edit modal
            async function loadExistingMedicines(flaggedId) {
                try {
                    const response = await fetch(`./flagged_data/get_medicine_history.php?flagged_id=${flaggedId}`);
                    const data = await response.json();

                    const container = document.getElementById('editExistingMedicines');
                    let html = '';

                    if (data.medicines && data.medicines.length > 0) {
                        html = `
                <div class="existing-medicines-section">
                    <h6 class="mb-3"><i class="fa-solid fa-history"></i> Existing Medicine Records</h6>
                    ${data.medicines.map(med => `
                        <div class="existing-medicine-item">
                            <div class="medicine-summary">
                                <span class="medicine-name">${med.medicine_name}</span>
                                <span class="medicine-quantity">${med.quantity_given} ${med.unit || ''}</span>
                                <span class="medicine-date">${flaggedManager.formatDate(new Date(med.date_administered))}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteMedicineEntry(${med.log_id})">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <hr>
            `;
                    }

                    container.innerHTML = html;
                } catch (error) {
                    console.error('Error loading existing medicines:', error);
                }
            }

            // Delete medicine entry
            async function deleteMedicineEntry(logId) {
                const result = await Swal.fire({
                    title: 'Delete Medicine Record?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await fetch('./flagged_data/delete_medicine_entry.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                log_id: logId
                            })
                        });

                        const data = await response.json();

                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Medicine record has been deleted.',
                                confirmButtonColor: '#27ae60',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Reload existing medicines
                            const flaggedId = document.getElementById('editFlaggedId').value;
                            loadExistingMedicines(flaggedId);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to delete medicine record.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    } catch (error) {
                        console.error('Error deleting medicine entry:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the medicine record.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }
            }

            // Clear medicine entries
            function clearMedicineEntries() {
                document.getElementById('addMedicineEntries').innerHTML = '';
                document.getElementById('editMedicineEntries').innerHTML = '';
                document.getElementById('editExistingMedicines').innerHTML = '';
                medicineEntryCount = 0;
                editMedicineEntryCount = 0;
            }
        </script>
</body>

</html>