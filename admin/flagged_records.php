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

        .modal-header .modal-title{
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
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm" onclick="exportFlaggedRecords()">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addFlaggedRecordModal">
                            <i class="fa-solid fa-plus"></i> Add Flagged Record
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
                    <option value="">All Issues</option>
                    <option value="Underweight">Underweight</option>
                    <option value="Overweight">Overweight</option>
                    <option value="Severely Underweight">Severely Underweight</option>
                    <option value="Incomplete Vaccination">Incomplete Vaccination</option>
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
        <div class="modal-dialog modal-lg">
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
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="addChildSelect" required onchange="showAddChildInfo()">
                                        <option value="">Select Child</option>
                                    </select>
                                    <label for="addChildSelect">
                                        <i class="fa-solid fa-child"></i> Child
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="addIssueType" required>
                                        <option value="">Select Issue Type</option>
                                        <option value="Underweight">Underweight</option>
                                        <option value="Overweight">Overweight</option>
                                        <option value="Severely Underweight">Severely Underweight</option>
                                        <option value="Incomplete Vaccination">Incomplete Vaccination</option>
                                        <option value="Growth Concerns">Growth Concerns</option>
                                        <option value="Behavioral Issues">Behavioral Issues</option>
                                        <option value="Medical Concerns">Medical Concerns</option>
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
                    <button type="button" class="btn btn-success" onclick="resolveFlaggedRecord()">
                        <i class="fa-solid fa-check"></i> Mark as Resolved
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Flagged Record Modal -->
    <div class="modal fade" id="editFlaggedModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
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
                                        <option value="Underweight">Underweight</option>
                                        <option value="Overweight">Overweight</option>
                                        <option value="Severely Underweight">Severely Underweight</option>
                                        <option value="Incomplete Vaccination">Incomplete Vaccination</option>
                                        <option value="Growth Concerns">Growth Concerns</option>
                                        <option value="Behavioral Issues">Behavioral Issues</option>
                                        <option value="Medical Concerns">Medical Concerns</option>
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

                        <div id="resolutionSection" style="display: none;">
                            <div class="resolution-notes">
                                <h6><i class="fa-solid fa-check-circle"></i> Resolution Details</h6>
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="resolutionNotes" style="height: 80px" placeholder="Enter resolution details..."></textarea>
                                    <label for="resolutionNotes">Resolution Notes</label>
                                </div>
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="resolutionDate">
                                    <label for="resolutionDate">Date Resolved</label>
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
                document.getElementById('addDateFlagged').valueAsDate = new Date();

                // Show resolution section when status is set to Resolved
                document.getElementById('editFlaggedStatus').addEventListener('change', (e) => {
                    const resolutionSection = document.getElementById('resolutionSection');
                    if (e.target.value === 'Resolved') {
                        resolutionSection.style.display = 'block';
                        document.getElementById('resolutionDate').valueAsDate = new Date();
                    } else {
                        resolutionSection.style.display = 'none';
                    }
                });
            }

            async loadInitialData() {
                try {
                    // Load children for dropdown
                    const childrenResponse = await fetch('./flagged_data/get_children.php');
                    const childrenData = await childrenResponse.json();
                    this.children = childrenData.children || [];
                    
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
                        const issueClass = this.getIssueClass(record.issue_type);
                        const flaggedDate = new Date(record.date_flagged);
                        const daysOpen = this.calculateDaysOpen(flaggedDate, record.flagged_status);
                        const priority = this.calculatePriority(record.issue_type, daysOpen);

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
                                <span class="issue-badge ${issueClass}">${record.issue_type}</span>
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
                const diffTime = Math.abs(today - flaggedDate);
                return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
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

                if (baseScore >= 4) return { text: 'High', class: 'priority-high' };
                if (baseScore >= 2) return { text: 'Medium', class: 'priority-medium' };
                return { text: 'Low', class: 'priority-low' };
            }

            formatDate(date) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                const d = new Date(date);
                return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
            }

            timeAgo(date) {
                const now = new Date();
                const diffTime = Math.abs(now - date);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (diffDays === 1) return '1 day ago';
                if (diffDays < 7) return `${diffDays} days ago`;
                if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
                if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
                return `${Math.floor(diffDays / 365)} years ago`;
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
                                            <p class="mb-1 small">${activity.issue_type}</p>
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
                const priority = this.calculatePriority(record.issue_type, daysOpen);

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
                                    <strong>Issue Type:</strong> <span class="issue-badge ${this.getIssueClass(record.issue_type)}">${record.issue_type}</span>
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

                    ${record.resolution_notes ? `
                        <div class="resolution-notes">
                            <h6><i class="fa-solid fa-check-circle"></i> Resolution Details</h6>
                            <p class="mb-2">${record.resolution_notes}</p>
                            <small class="text-muted">Resolved on: ${record.resolution_date ? this.formatDate(new Date(record.resolution_date)) : 'N/A'}</small>
                        </div>
                    ` : ''}
                `;

                document.getElementById('viewFlaggedContent').innerHTML = content;
                document.getElementById('viewModalTitle').innerHTML = `
                    <i class="fa-solid fa-flag"></i>
                    ${record.first_name} ${record.last_name} - ${record.issue_type}
                `;

                const modal = new bootstrap.Modal(document.getElementById('viewFlaggedModal'));
                modal.show();
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

                // Populate form
                document.getElementById('editFlaggedId').value = record.flagged_id;
                document.getElementById('editChildName').textContent = `${record.first_name} ${record.last_name}`;
                document.getElementById('editChildAge').textContent = `${age} years`;
                document.getElementById('editChildZone').textContent = record.zone_name || 'N/A';
                document.getElementById('editIssueType').value = record.issue_type;
                document.getElementById('editFlaggedStatus').value = record.flagged_status;
                document.getElementById('editDateFlagged').value = record.date_flagged.split(' ')[0]; // Extract date part
                document.getElementById('editDescription').value = record.description || '';

                // Handle resolution section
                const resolutionSection = document.getElementById('resolutionSection');
                if (record.flagged_status === 'Resolved') {
                    resolutionSection.style.display = 'block';
                    document.getElementById('resolutionNotes').value = record.resolution_notes || '';
                    document.getElementById('resolutionDate').value = record.resolution_date ? record.resolution_date.split(' ')[0] : '';
                } else {
                    resolutionSection.style.display = 'none';
                }

                const modal = new bootstrap.Modal(document.getElementById('editFlaggedModal'));
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
                            data: { flagged_id: flaggedId },
                            success: async (response) => {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Flagged record has been deleted.',
                                        confirmButtonColor: '#27ae60'
                                    }).then((result) => {
                                        if(result){
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
                        description: description
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Added!',
                                text: 'Flagged record added successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
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

            if (!flaggedId || !issueType || !status) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            try {
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
                        resolution_date: resolutionDate
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'Flagged record updated successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
                                bootstrap.Modal.getInstance(document.getElementById('editFlaggedModal')).hide();
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
                                text: result.message || 'Failed to update flagged record.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating flagged record:', error);
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

        function exportFlaggedRecords() {
            window.open('./flagged_data/export_flagged_records.php', '_blank');
        }
    </script>
</body>

</html>