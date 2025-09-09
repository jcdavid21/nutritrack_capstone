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

    <title>Vaccine Records Management</title>
    <style>
        :root {
            --primary-green: #2d5a3d;
            --light-green: #4a7c59;
            --warning-orange: #fd7e14;
            --success-green: #27ae60;
            --primary-blue: #0d6efd;
            --light-grey: #f8f9fa;
            --medium-grey: #6c757d;
            --dark-grey: #343a40;
            --border-grey: #dee2e6;
            --danger-red: #dc3545;
        }

        .vaccine-card {
            background: var(--primary-green);
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
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-completed {
            background-color: #d4edda;
            color: var(--success-green);
        }

        .status-ongoing {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-incomplete {
            background-color: #f8d7da;
            color: var(--danger-red);
        }

        .vaccine-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .vaccine-bcg {
            background-color: #e7f3ff;
            color: #0066cc;
        }

        .vaccine-hepatitis {
            background-color: #fff0e6;
            color: #cc6600;
        }

        .vaccine-dpt {
            background-color: #f0e6ff;
            color: #6600cc;
        }

        .vaccine-other {
            background-color: #f0f0f0;
            color: #666666;
        }

        .child-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary-green);
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
            background: var(--primary-green);
            border: none;
            color: white;
            transition: all 0.3s;
        }

        .btn-gradient:hover {
            transform: translateY(-1px);
            background: var(--light-green);
            color: white;
        }

        .modal-header {
            background: var(--primary-green);
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

        .vaccine-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .vaccine-table th {
            background: #2E7D32;
            color: var(--light-grey);
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid var(--border-grey);
        }

        .vaccine-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-grey);
            vertical-align: middle;
        }

        .vaccine-table tbody tr:hover {
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
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
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
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
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

        .recent-vaccines {
            max-height: 400px;
            overflow-y: auto;
        }

        .recent-vaccine-item {
            padding: 12px;
            border: 1px solid var(--border-grey);
            border-radius: 8px;
            margin-bottom: 10px;
            background: white;
            transition: all 0.2s;
        }

        .recent-vaccine-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .vaccine-timeline {
            position: relative;
            padding-left: 30px;
        }

        .vaccine-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-grey);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-green);
        }
    </style>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="vaccine-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fa-solid fa-syringe"></i>
                        Vaccine Records Management
                    </h1>
                    <p class="mb-0 opacity-90">Track and manage child vaccination records</p>
                </div>
                <div class="text-end">
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm" onclick="exportVaccines()">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addVaccineModal">
                            <i class="fa-solid fa-plus"></i> Add Vaccine Record
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
                            <div class="stat-number text-primary" id="totalVaccinesCount">0</div>
                            <div class="stat-label">Total Vaccines</div>
                        </div>
                        <i class="fa-solid fa-syringe text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-success" id="completedCount">0</div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <i class="fa-solid fa-check-circle text-success fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-warning" id="ongoingCount">0</div>
                            <div class="stat-label">Ongoing</div>
                        </div>
                        <i class="fa-solid fa-clock text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-danger" id="incompleteCount">0</div>
                            <div class="stat-label">Incomplete</div>
                        </div>
                        <i class="fa-solid fa-exclamation-triangle text-danger fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <h3 class="mb-0">All Vaccine Records</h3>
                <span class="badge bg-secondary" id="totalCount">0 Records</span>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="statusFilter" onchange="filterVaccines()">
                    <option value="">All Status</option>
                    <option value="Completed">Completed</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Incomplete">Incomplete</option>
                </select>
                <select class="form-select form-select-sm" id="vaccineTypeFilter" onchange="filterVaccines()">
                    <option value="">All Vaccines</option>
                    <option value="BCG">BCG</option>
                    <option value="Hepatitis B">Hepatitis B</option>
                    <option value="DPT-1">DPT-1</option>
                    <option value="DPT-2">DPT-2</option>
                    <option value="DPT-3">DPT-3</option>
                </select>
                <div class="search-box">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search vaccines...">
                </div>
            </div>
        </div>

        <div id="tableView" class="table-container">
            <div class="table-wrapper">
                <table class="vaccine-table" id="vaccineTable">
                    <thead>
                        <tr>
                            <th>Vaccine ID</th>
                            <th>Child</th>
                            <th>Vaccine Name</th>
                            <th>Status</th>
                            <th>Administered By</th>
                            <th>Date Administered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="vaccineTableBody">
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

        <!-- Vaccine Distribution and Recent Records -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="filter-section">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-clock"></i>
                        Recent Vaccinations
                    </h5>
                    <div id="recentVaccines" class="recent-vaccines">
                        <!-- Recent vaccines will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Vaccine Modal -->
    <div class="modal fade" id="addVaccineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-syringe"></i>
                        Add Vaccine Record
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addVaccineForm">
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
                                    <select class="form-select" id="addAdministeredBy" required>
                                        <option value="">Select Administrator</option>
                                    </select>
                                    <label for="addAdministeredBy">
                                        <i class="fa-solid fa-user-md"></i> Administered By
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
                                    <input type="text" class="form-control" id="addVaccineName" required placeholder="Vaccine Name">
                                    <label for="addVaccineName">
                                        <i class="fa-solid fa-vial"></i> Vaccine Name
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="addVaccineStatus" required>
                                        <option value="">Select Status</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Ongoing">Ongoing</option>
                                        <option value="Incomplete">Incomplete</option>
                                    </select>
                                    <label for="addVaccineStatus">
                                        <i class="fa-solid fa-check-circle"></i> Status
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="datetime-local" class="form-control auto-calculated" id="addVaccineDate" readonly required>
                            <label for="addVaccineDate">
                                <i class="fa-solid fa-calendar"></i> Date Administered (Now)
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="addVaccine()">
                        <i class="fa-solid fa-save"></i> Save Vaccine Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Vaccine Modal -->
    <div class="modal fade" id="viewVaccineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalTitle">
                        <i class="fa-solid fa-eye"></i>
                        Vaccine Record Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewVaccineContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editVaccineFromView()">
                        <i class="fa-solid fa-edit"></i> Edit Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Vaccine Modal -->
    <div class="modal fade" id="editVaccineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-edit"></i>
                        Edit Vaccine Record
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editVaccineForm">
                        <input type="hidden" id="editVaccineId">

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
                                    <select class="form-select" id="editAdministeredBy" required>
                                        <option value="">Select Administrator</option>
                                    </select>
                                    <label for="editAdministeredBy">
                                        <i class="fa-solid fa-user-md"></i> Administered By
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editVaccineName" required placeholder="Vaccine Name">
                                    <label for="editVaccineName">
                                        <i class="fa-solid fa-vial"></i> Vaccine Name
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="editVaccineStatus" required>
                                        <option value="">Select Status</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Ongoing">Ongoing</option>
                                        <option value="Incomplete">Incomplete</option>
                                    </select>
                                    <label for="editVaccineStatus">
                                        <i class="fa-solid fa-check-circle"></i> Status
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="datetime-local" class="form-control" id="editVaccineDate" required>
                                    <label for="editVaccineDate">
                                        <i class="fa-solid fa-calendar"></i> Date Administered
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="updateVaccine()">
                        <i class="fa-solid fa-save"></i> Update Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        class VaccineManager {
            constructor() {
                this.currentPage = 1;
                this.itemsPerPage = 10;
                this.totalRecords = 0;
                this.vaccines = [];
                this.children = [];
                this.users = [];
                this.currentVaccine = null;
                this.init();
            }

            async init() {
                this.setupEventListeners();
                await this.loadInitialData();
                await this.loadVaccines();
                this.loadTableView();
                this.updateStatistics();
                this.loadRecentVaccines();
            }

            setupEventListeners() {
                document.getElementById('searchInput').addEventListener('input', (e) => this.searchVaccines(e.target.value));

                // Set current datetime for new vaccines
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                document.getElementById('addVaccineDate').value = now.toISOString().slice(0, 16);
            }

            async loadInitialData() {
                try {
                    // Load children for dropdown
                    const childrenResponse = await fetch('./flagged_data/get_children.php');
                    const childrenData = await childrenResponse.json();
                    this.children = childrenData.children || [];

                    // Load users for administered_by dropdown
                    const usersResponse = await fetch('./vaccine_data/get_users.php');
                    const usersData = await usersResponse.json();
                    this.users = usersData.users || [];

                    this.populateChildrenDropdown();
                    this.populateUsersDropdown();
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

            populateUsersDropdown() {
                const addUserSelect = document.getElementById('addAdministeredBy');
                const editUserSelect = document.getElementById('editAdministeredBy');

                const options = '<option value="">Select Administrator</option>' +
                    this.users.map(user =>
                        `<option value="${user.user_id}">${user.full_name}</option>`
                    ).join('');

                addUserSelect.innerHTML = options;
                editUserSelect.innerHTML = options;
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

            async loadVaccines() {
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.itemsPerPage,
                        search: document.getElementById('searchInput')?.value || '',
                        status: document.getElementById('statusFilter')?.value || '',
                        vaccine_type: document.getElementById('vaccineTypeFilter')?.value || ''
                    });

                    const response = await fetch(`./vaccine_data/get_vaccines.php?${params}`);
                    const data = await response.json();

                    this.vaccines = data.vaccines || [];
                    this.totalRecords = data.total || 0;

                    document.getElementById('totalCount').textContent = `${this.totalRecords} Records`;
                } catch (error) {
                    console.error('Error loading vaccines:', error);
                }
            }

            loadTableView() {
                const tbody = document.getElementById('vaccineTableBody');
                let html = '';

                if (this.vaccines.length === 0) {
                    html = `<tr class="no-data">
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fa-solid fa-syringe"></i>
                                <h3>No vaccine records found</h3>
                                <p>No vaccine records match your current filters</p>
                            </div>
                        </td>
                    </tr>`;
                } else {
                    this.vaccines.forEach(vaccine => {
                        const vaccineDate = new Date(vaccine.vaccine_date);
                        const initials = `${vaccine.first_name[0]}${vaccine.last_name[0]}`.toUpperCase();
                        const statusClass = this.getStatusClass(vaccine.vaccine_status);
                        const vaccineClass = this.getVaccineTypeClass(vaccine.vaccine_name);

                        html += `<tr>
                            <td>
                                <div class="fw-medium">#VAC-${String(vaccine.vaccine_id).padStart(4, '0')}</div>
                                <small class="text-muted">ID: ${vaccine.vaccine_id}</small>
                            </td>
                            <td class="name-cell">
                                <div class="d-flex align-items-center">
                                    <div class="child-avatar me-3">${initials}</div>
                                    <div>
                                        <div class="fw-bold">${vaccine.first_name} ${vaccine.last_name}</div>
                                        <small class="text-muted">ID: #${vaccine.child_id}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="vaccine-badge ${vaccineClass}">${vaccine.vaccine_name}</span>
                            </td>
                            <td>
                                <span class="status-badge ${statusClass}">${vaccine.vaccine_status}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2">${this.getUserInitials(vaccine.administered_by_name)}</div>
                                    <div>
                                        <div class="fw-medium">${vaccine.administered_by_name || 'Unknown'}</div>
                                        <small class="text-muted">ID: ${vaccine.administered_by}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="date-info">
                                    <div class="fw-medium">${this.formatDate(vaccineDate)}</div>
                                    <small class="text-muted">${this.timeAgo(vaccineDate)}</small>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-action btn-primary" title="View Vaccine" data-vaccine-id="${vaccine.vaccine_id}" onclick="vaccineManager.viewVaccine(${vaccine.vaccine_id})">
                                        <i class="fa-solid fa-eye text-white"></i>
                                    </button>
                                    <button class="btn-action btn-success" title="Edit Vaccine" data-vaccine-id="${vaccine.vaccine_id}" onclick="vaccineManager.editVaccine(${vaccine.vaccine_id})">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-danger" title="Delete Vaccine" data-vaccine-id="${vaccine.vaccine_id}" onclick="vaccineManager.deleteVaccine(${vaccine.vaccine_id})">
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
                switch (status) {
                    case 'Completed':
                        return 'status-completed';
                    case 'Ongoing':
                        return 'status-ongoing';
                    case 'Incomplete':
                        return 'status-incomplete';
                    default:
                        return 'status-ongoing';
                }
            }

            getVaccineTypeClass(vaccineName) {
                if (vaccineName.includes('BCG')) return 'vaccine-bcg';
                if (vaccineName.includes('Hepatitis')) return 'vaccine-hepatitis';
                if (vaccineName.includes('DPT')) return 'vaccine-dpt';
                return 'vaccine-other';
            }

            getUserInitials(fullName) {
                if (!fullName) return 'U';
                const names = fullName.split(' ');
                return names.length > 1 ? `${names[0][0]}${names[1][0]}`.toUpperCase() : names[0][0].toUpperCase();
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
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (diffDays === 1) return '1 day ago';
                if (diffDays < 7) return `${diffDays} days ago`;
                if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
                if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
                return `${Math.floor(diffDays / 365)} years ago`;
            }

            async updateStatistics() {
                try {
                    const response = await fetch('./vaccine_data/get_vaccine_statistics.php');
                    const stats = await response.json();

                    document.getElementById('totalVaccinesCount').textContent = stats.total || 0;
                    document.getElementById('completedCount').textContent = stats.completed || 0;
                    document.getElementById('ongoingCount').textContent = stats.ongoing || 0;
                    document.getElementById('incompleteCount').textContent = stats.incomplete || 0;
                } catch (error) {
                    console.error('Error updating statistics:', error);
                }
            }

            async loadRecentVaccines() {
                try {
                    const response = await fetch('./vaccine_data/get_recent_vaccines.php');
                    const data = await response.json();

                    const container = document.getElementById('recentVaccines');
                    let html = '';

                    if (data.vaccines && data.vaccines.length > 0) {
                        data.vaccines.forEach(vaccine => {
                            const vaccineDate = new Date(vaccine.vaccine_date);
                            const statusClass = this.getStatusClass(vaccine.vaccine_status);

                            html += `
                                <div class="recent-vaccine-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${vaccine.first_name} ${vaccine.last_name}</h6>
                                            <p class="mb-1 small">${vaccine.vaccine_name}</p>
                                            <span class="status-badge ${statusClass} small">${vaccine.vaccine_status}</span>
                                        </div>
                                        <small class="text-muted">${this.timeAgo(vaccineDate)}</small>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<p class="text-muted text-center">No recent vaccinations</p>';
                    }

                    container.innerHTML = html;
                } catch (error) {
                    console.error('Error loading recent vaccines:', error);
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
                            onclick="vaccineManager.changePage(${this.currentPage - 1})">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                `;

                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                             onclick="vaccineManager.changePage(${i})">${i}</span>`;
                }

                html += `
                    <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                            onclick="vaccineManager.changePage(${this.currentPage + 1})">
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
                    await this.loadVaccines();
                    this.loadTableView();
                }
            }

            async searchVaccines(query) {
                this.currentPage = 1;
                await this.loadVaccines();
                this.loadTableView();
            }

            async viewVaccine(vaccineId) {
                try {
                    const response = await fetch(`./vaccine_data/get_vaccine.php?vaccine_id=${vaccineId}`);
                    const data = await response.json();

                    if (data.vaccine) {
                        this.currentVaccine = data.vaccine;
                        this.showViewModal(data.vaccine);
                    }
                } catch (error) {
                    console.error('Error loading vaccine:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load vaccine details.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            showViewModal(vaccine) {
                const vaccineDate = new Date(vaccine.vaccine_date);
                const child = this.children.find(c => c.child_id == vaccine.child_id);
                const user = this.users.find(u => u.user_id == vaccine.administered_by);
                const age = child ? this.calculateAge(new Date(child.birthdate)) : 'N/A';

                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="details-section">
                                <h6><i class="fa-solid fa-user"></i> Child Information</h6>
                                <div class="child-info-display">
                                    <strong>Name:</strong> ${vaccine.first_name} ${vaccine.last_name}
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
                                <h6><i class="fa-solid fa-syringe"></i> Vaccine Information</h6>
                                <div class="child-info-display">
                                    <strong>Vaccine ID:</strong> #VAC-${String(vaccine.vaccine_id).padStart(4, '0')}
                                </div>
                                <div class="child-info-display">
                                    <strong>Vaccine Name:</strong> ${vaccine.vaccine_name}
                                </div>
                                <div class="child-info-display">
                                    <strong>Status:</strong> <span class="status-badge ${this.getStatusClass(vaccine.vaccine_status)}">${vaccine.vaccine_status}</span>
                                </div>
                                <div class="child-info-display">
                                    <strong>Administered By:</strong> ${user?.full_name || 'Unknown'}
                                </div>
                                <div class="child-info-display">
                                    <strong>Date Administered:</strong> ${this.formatDate(vaccineDate)}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('viewVaccineContent').innerHTML = content;
                document.getElementById('viewModalTitle').innerHTML = `
                    <i class="fa-solid fa-eye"></i>
                    ${vaccine.vaccine_name} - ${vaccine.first_name} ${vaccine.last_name}
                `;

                const modal = new bootstrap.Modal(document.getElementById('viewVaccineModal'));
                modal.show();
            }

            async editVaccine(vaccineId) {
                try {
                    const response = await fetch(`./vaccine_data/get_vaccine.php?vaccine_id=${vaccineId}`);
                    const data = await response.json();

                    if (data.vaccine) {
                        this.currentVaccine = data.vaccine;
                        this.showEditModal(data.vaccine);
                    }
                } catch (error) {
                    console.error('Error loading vaccine for editing:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load vaccine for editing.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            showEditModal(vaccine) {
                const child = this.children.find(c => c.child_id == vaccine.child_id);
                const age = child ? this.calculateAge(new Date(child.birthdate)) : 'N/A';

                // Fix: Properly handle the vaccine date for datetime input
                let vaccineDate;
                if (typeof vaccine.vaccine_date === 'string') {
                    // Replace space with 'T' to make it ISO format, but treat as local time
                    const isoString = vaccine.vaccine_date.replace(' ', 'T');
                    vaccineDate = new Date(isoString);
                } else {
                    vaccineDate = new Date(vaccine.vaccine_date);
                }

                // Populate form
                document.getElementById('editVaccineId').value = vaccine.vaccine_id;
                document.getElementById('editChildName').textContent = `${vaccine.first_name} ${vaccine.last_name}`;
                document.getElementById('editChildAge').textContent = `${age} years`;
                document.getElementById('editChildZone').textContent = child?.zone_name || 'N/A';
                document.getElementById('editAdministeredBy').value = vaccine.administered_by;
                document.getElementById('editVaccineName').value = vaccine.vaccine_name;
                document.getElementById('editVaccineStatus').value = vaccine.vaccine_status;

                // Format datetime for input - adjust for timezone to show accurate local time
                const year = vaccineDate.getFullYear();
                const month = String(vaccineDate.getMonth() + 1).padStart(2, '0');
                const day = String(vaccineDate.getDate()).padStart(2, '0');
                const hours = String(vaccineDate.getHours()).padStart(2, '0');
                const minutes = String(vaccineDate.getMinutes()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;
                document.getElementById('editVaccineDate').value = formattedDate;

                const modal = new bootstrap.Modal(document.getElementById('editVaccineModal'));
                modal.show();
            }

            async deleteVaccine(vaccineId) {
                const result = await Swal.fire({
                    title: 'Delete Vaccine Record?',
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
                            url: '../backend/admin/vaccines/delete_vaccine.php',
                            type: 'POST',
                            data: {
                                vaccine_id: vaccineId
                            },
                            success: async (response) => {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Vaccine record has been deleted.',
                                        confirmButtonColor: '#27ae60'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Failed to delete vaccine record.',
                                        confirmButtonColor: '#dc3545'
                                    });
                                }
                            },
                            error: (xhr, status, error) => {
                                console.error('AJAX Error:', status, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while deleting the vaccine record.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        });
                    } catch (error) {
                        console.error('Error deleting vaccine:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the vaccine record.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }
            }
        }

        // Initialize vaccine manager
        let vaccineManager;
        document.addEventListener('DOMContentLoaded', function() {
            vaccineManager = new VaccineManager();
        });

        // Global functions
        function showAddChildInfo() {
            vaccineManager.showAddChildInfo();
        }

        async function addVaccine() {
            const childId = document.getElementById('addChildSelect').value;
            const administeredBy = document.getElementById('addAdministeredBy').value;
            const vaccineName = document.getElementById('addVaccineName').value;
            const vaccineStatus = document.getElementById('addVaccineStatus').value;
            const vaccineDate = document.getElementById('addVaccineDate').value;

            if (!childId || !administeredBy || !vaccineName || !vaccineStatus || !vaccineDate) {
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
                    url: '../backend/admin/vaccines/add_vaccine.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        child_id: childId,
                        administered_by: administeredBy,
                        vaccine_name: vaccineName,
                        vaccine_status: vaccineStatus,
                        vaccine_date: vaccineDate
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Added!',
                                text: 'Vaccine record added successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
                                bootstrap.Modal.getInstance(document.getElementById('addVaccineModal')).hide();
                                vaccineManager.loadVaccines().then(() => {
                                    vaccineManager.loadTableView();
                                    vaccineManager.updateStatistics();
                                    vaccineManager.loadRecentVaccines();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to add vaccine record.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error adding vaccine:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while adding the vaccine record.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            } catch (error) {
                console.error('Error adding vaccine:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding the vaccine record.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        async function updateVaccine() {
            const vaccineId = document.getElementById('editVaccineId').value;
            const administeredBy = document.getElementById('editAdministeredBy').value;
            const vaccineName = document.getElementById('editVaccineName').value;
            const vaccineStatus = document.getElementById('editVaccineStatus').value;
            const vaccineDate = document.getElementById('editVaccineDate').value;

            if (!vaccineId || !administeredBy || !vaccineName || !vaccineStatus || !vaccineDate) {
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
                    url: '../backend/admin/vaccines/update_vaccine.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        vaccine_id: vaccineId,
                        administered_by: administeredBy,
                        vaccine_name: vaccineName,
                        vaccine_status: vaccineStatus,
                        vaccine_date: vaccineDate
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'Vaccine record updated successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
                                bootstrap.Modal.getInstance(document.getElementById('editVaccineModal')).hide();
                                vaccineManager.loadVaccines().then(() => {
                                    vaccineManager.loadTableView();
                                    vaccineManager.updateStatistics();
                                    vaccineManager.loadRecentVaccines();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to update vaccine record.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating vaccine:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating the vaccine record.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            } catch (error) {
                console.error('Error updating vaccine:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the vaccine record.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        function editVaccineFromView() {
            if (vaccineManager.currentVaccine) {
                bootstrap.Modal.getInstance(document.getElementById('viewVaccineModal')).hide();
                setTimeout(() => {
                    vaccineManager.editVaccine(vaccineManager.currentVaccine.vaccine_id);
                }, 300);
            }
        }

        async function filterVaccines() {
            vaccineManager.currentPage = 1;
            await vaccineManager.loadVaccines();
            vaccineManager.loadTableView();
        }

        function exportVaccines() {
            window.open('./vaccine_data/export_vaccines.php', '_blank');
        }
    </script>
</body>

</html>