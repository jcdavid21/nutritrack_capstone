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

    <title>Child Nutrition Records</title>
    <style>
        :root {
            --primary-green: #2d5a3d;
            --light-green: #4a7c59;
            --success-green: #27ae60;
            --light-grey: #f8f9fa;
            --medium-grey: #6c757d;
            --dark-grey: #343a40;
            --border-grey: #dee2e6;
        }

        .nutrition-card {
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
            color: var(--dark-grey);
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

        .status-normal {
            background-color: #d4edda;
            color: var(--success-green);
            border: 1px solid var(--success-green);
        }

        .status-underweight {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .status-overweight {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }

        .status-severely-underweight {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }

        .child-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--light-green) 100%);
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

        .modal-xl .chart-container {
            height: 450px;
        }

        .measurement-form {
            background: var(--light-grey);
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
        }

        .form-floating>label {
            color: var(--medium-grey);
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--light-green) 100%);
            border: none;
            color: white;
            transition: all 0.3s;
        }

        .btn-gradient:hover {
            transform: translateY(-1px);
            background: #27ae60;
            color: white;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--light-green) 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .age-badge {
            background-color: var(--light-grey);
            color: var(--dark-grey);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .gender-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .gender-male {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .gender-female {
            background-color: #fce4ec;
            color: #ad1457;
        }

        .record-timeline {
            position: relative;
            padding-left: 25px;
        }

        .record-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-grey);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -21px;
            top: 15px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--success-green);
        }

        .bmi-display {
            font-size: 14px;
            font-weight: 500;
            padding: 10px;
            border-radius: 8px;
            background: var(--light-grey);
            border: 1px solid var(--border-grey);
            text-align: center;
        }

        .chart-toggle-btn {
            background: var(--light-grey);
            border: 1px solid var(--border-grey);
            color: var(--dark-grey);
            transition: all 0.3s;
        }

        .chart-toggle-btn.active {
            background: var(--success-green);
            border-color: var(--success-green);
            color: white;
        }

        .chart-toggle-btn:hover {
            background: var(--primary-green);
            border-color: var(--primary-green);
            color: white;
        }

        .modal-title {
            color: white !important;
        }

        .child-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .auto-calculated,
        #measurementDate,
        #editMeasurementDate {
            background-color: #e8f5e8;
            border-color: #27ae60;
        }

        .edit-record-btn {
            background: #27ae60;
            border: none;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .edit-record-btn:hover {
            background: #2d5a3d;
            transform: translateY(-1px);
        }

        .delete-record-btn {
            background: #dc3545;
            border: none;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-left: 5px;
            transition: all 0.2s;
        }

        .delete-record-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .info-field {
            border: 1px solid var(--border-grey);
            border-radius: 8px;
            padding: 12px 15px;
            background-color: #f8f9fa;
            margin-bottom: 10px;
            min-height: 45px;
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--dark-grey);
        }

        .info-field strong {
            color: var(--medium-grey);
            margin-right: 8px;
            font-weight: 500;
        }

        .measurement-search-box {
            position: relative;
            margin-bottom: 15px;
        }

        .measurement-search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid var(--border-grey);
            border-radius: 8px;
            font-size: 14px;
            background-color: white;
            transition: all 0.2s ease;
        }

        .measurement-search-box input:focus {
            outline: none;
            border-color: var(--success-green);
            box-shadow: 0 0 0 2px rgba(39, 174, 96, 0.1);
        }

        .measurement-search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-grey);
            z-index: 2;
        }

        .no-records-found {
            text-align: center;
            padding: 40px 20px;
            color: var(--medium-grey);
        }

        .no-records-found i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--border-grey);
        }
    </style>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="nutrition-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fa-solid fa-chart-line"></i>
                        Child Nutrition Records
                    </h1>
                    <p class="mb-0 opacity-90">Monitor and track children's growth and nutritional status</p>
                </div>
                <div class="text-end">
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm" onclick="exportRecords()">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addChildModal">
                            <i class="fa-solid fa-user-plus"></i> Add Child
                        </button>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addMeasurementModal">
                            <i class="fa-solid fa-plus"></i> Add Measurement
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
                            <div class="stat-number" id="totalChildrenCount">0</div>
                            <div class="stat-label">Total Children</div>
                        </div>
                        <i class="fa-solid fa-child text-success fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number" id="normalStatusCount">0</div>
                            <div class="stat-label">Normal Weight</div>
                        </div>
                        <i class="fa-solid fa-heart text-success fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number" id="underweightCount">0</div>
                            <div class="stat-label">Underweight</div>
                        </div>
                        <i class="fa-solid fa-exclamation-triangle text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number" id="recentRecordsCount">0</div>
                            <div class="stat-label">This Month</div>
                        </div>
                        <i class="fa-solid fa-calendar text-info fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <h3 class="mb-0">Nutrition Records</h3>
                <span class="badge bg-secondary" id="totalRecordsCount">0 Records</span>
            </div>
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search children...">
            </div>
        </div>

        <div id="tableView" class="table-container">
            <div class="table-wrapper">
                <table class="announcements-table" id="nutritionTable">
                    <thead>
                        <tr>
                            <th>Child</th>
                            <th>Age/Gender</th>
                            <th>Zone</th>
                            <th>Latest Weight</th>
                            <th>Latest Height</th>
                            <th>BMI</th>
                            <th>Status</th>
                            <th>Last Record</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="nutritionTableBody">
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

    <!-- Add Measurement Modal -->
    <div class="modal fade" id="addMeasurementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-plus"></i>
                        Add New Measurement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMeasurementForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="childSelect" required>
                                        <option value="">Select Child</option>
                                    </select>
                                    <label for="childSelect">
                                        <i class="fa-solid fa-child"></i> Child
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="measurementDate" readonly required>
                                    <label for="measurementDate">
                                        <i class="fa-solid fa-calendar"></i> Date
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Child Info Card (Hidden by default) -->
                        <div id="childInfoCard" class="child-info-card" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="fa-solid fa-user"></i> Child Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <span id="childInfoName">-</span></p>
                                    <p class="mb-1"><strong>Gender:</strong> <span id="childInfoGender">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Date of Birth:</strong> <span id="childInfoBirthdate">-</span></p>
                                    <p class="mb-1"><strong>Current Age:</strong> <span id="childInfoAge">-</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="weightInput" step="0.1" min="0" max="100" required>
                                    <label for="weightInput">
                                        <i class="fa-solid fa-weight"></i> Weight (kg)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="heightInput" step="0.1" min="0" max="200" required>
                                    <label for="heightInput">
                                        <i class="fa-solid fa-ruler-vertical"></i> Height (cm)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-calculated" id="bmiDisplay" readonly>
                                    <label for="bmiDisplay">
                                        <i class="fa-solid fa-calculator"></i> BMI (Auto-calculated)
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <select class="form-select" id="statusSelect" required>
                                        <option value="" disabled>Select Nutritional Status</option>
                                    </select>
                                    <label for="statusSelect">
                                        <i class="fa-solid fa-heartbeat"></i> Nutritional Status
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="addMeasurement()">
                        <i class="fa-solid fa-save"></i> Save Measurement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Measurement Modal -->
    <div class="modal fade" id="editMeasurementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-edit"></i>
                        Edit Measurement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editMeasurementForm">
                        <input type="hidden" id="editRecordId">

                        <!-- Child Info Card -->
                        <div id="editChildInfoCard" class="child-info-card">
                            <div class="info-section">
                                <h6><i class="fa-solid fa-user"></i> Child Information</h6>
                                <div class="info-row">
                                    <span class="info-label">Full Name:</span>
                                    <span class="info-value" id="editChildInfoName">-</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Gender:</span>
                                    <span class="info-value" id="editChildInfoGender">-</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Date of Birth:</span>
                                    <span class="info-value" id="editChildInfoBirthdate">-</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Current Age:</span>
                                    <span class="info-value" id="editChildInfoAge">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="editMeasurementDate" readonly required>
                                    <label for="editMeasurementDate">
                                        <i class="fa-solid fa-calendar"></i> Date
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="editWeightInput" step="0.1" min="0" max="100" required>
                                    <label for="editWeightInput">
                                        <i class="fa-solid fa-weight"></i> Weight (kg)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="editHeightInput" step="0.1" min="0" max="200" required>
                                    <label for="editHeightInput">
                                        <i class="fa-solid fa-ruler-vertical"></i> Height (cm)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-calculated" id="editBmiDisplay" readonly>
                                    <label for="editBmiDisplay">
                                        <i class="fa-solid fa-calculator"></i> BMI (Auto-calculated)
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <select class="form-select" id="editStatusSelect" required>
                                        <option value="" disabled>Select Nutritional Status</option>
                                    </select>
                                    <label for="editStatusSelect">
                                        <i class="fa-solid fa-heartbeat"></i> Nutritional Status
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="updateMeasurement()">
                        <i class="fa-solid fa-save"></i> Update Measurement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Child Details Modal -->
    <div class="modal fade" id="childDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="childDetailsModalTitle">
                        <i class="fa-solid fa-user-circle"></i>
                        Child Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="childDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-gradient" onclick="printChildReport()">
                        <i class="fa-solid fa-print"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Child Details Modal -->
    <div class="modal fade" id="editChildDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editChildDetailsModalTitle">
                        <i class="fa-solid fa-user-circle"></i>
                        Edit Child Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editChildDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-gradient" onclick="saveChildDetails()">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Child Modal -->
    <div class="modal fade" id="addChildModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-user-plus"></i>
                        Add New Child
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addChildForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="newFirstName" required>
                                    <label for="newFirstName">
                                        <i class="fa-solid fa-user"></i> First Name
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="newLastName" required>
                                    <label for="newLastName">
                                        <i class="fa-solid fa-user"></i> Last Name
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="newGender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <label for="newGender">
                                        <i class="fa-solid fa-venus-mars"></i> Gender
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="newBirthdate" required>
                                    <label for="newBirthdate">
                                        <i class="fa-solid fa-calendar"></i> Date of Birth
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="newZone" required>
                                        <option value="">Select Zone</option>
                                    </select>
                                    <label for="newZone">
                                        <i class="fa-solid fa-map-marker-alt"></i> Zone
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-calculated" id="newCurrentAge" readonly>
                                    <label for="newCurrentAge">
                                        <i class="fa-solid fa-clock"></i> Current Age (Auto-calculated)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="addChild()">
                        <i class="fa-solid fa-save"></i> Add Child
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
        class NutritionRecordsManager {
            constructor() {
                this.currentPage = 1;
                this.itemsPerPage = 10;
                this.totalRecords = 0;
                this.records = [];
                this.children = [];
                this.nutritionStatuses = [];
                this.currentChildId = null;
                this.currentChartType = 'weight';
                this.currentChildData = null;
                this.currentRecordsData = null;
                this.isEditMode = false;
                this.currentEditRecordId = null;
                this.originalMeasurementRecords = [];
                this.init();
            }

            async init() {
                this.setupEventListeners();
                this.setupModalManagement();
                await this.loadInitialData();
                await this.loadRecords();
                this.loadTableView();
                this.updateStatistics();
            }

            setupEventListeners() {
                document.getElementById('searchInput').addEventListener('input', (e) => this.searchRecords(e.target.value));
                document.getElementById('measurementDate').valueAsDate = new Date();

                // Child selection for add modal
                document.getElementById('childSelect').addEventListener('change', (e) => {
                    this.clearMeasurementInputs();
                    this.showChildInfo(e.target.value);
                });

                // BMI and status auto-calculation for add modal
                document.getElementById('weightInput').addEventListener('input', () => this.calculateBMIAndStatus());
                document.getElementById('heightInput').addEventListener('input', () => this.calculateBMIAndStatus());

                // BMI and status auto-calculation for edit modal
                document.getElementById('editWeightInput').addEventListener('input', () => this.calculateBMIAndStatus(true));
                document.getElementById('editHeightInput').addEventListener('input', () => this.calculateBMIAndStatus(true));

                // Clear inputs when modals are hidden
                document.getElementById('addMeasurementModal').addEventListener('hidden.bs.modal', () => {
                    this.clearAllInputs();
                });

                document.getElementById('editMeasurementModal').addEventListener('hidden.bs.modal', () => {
                    this.clearAllInputs();
                });

                // Age calculation for new child
                document.getElementById('newBirthdate').addEventListener('change', (e) => {
                    const birthDate = new Date(e.target.value);
                    const age = this.calculateAge(birthDate);
                    document.getElementById('newCurrentAge').value = `${age} years`;
                });

                // Clear inputs when add child modal is hidden
                document.getElementById('addChildModal').addEventListener('hidden.bs.modal', () => {
                    document.getElementById('addChildForm').reset();
                    document.getElementById('newCurrentAge').value = '';
                });
            }


            setupModalManagement() {
                // Get all modal elements
                const modals = [
                    'addMeasurementModal',
                    'editMeasurementModal',
                    'childDetailsModal',
                    'editChildDetailsModal',
                    'addChildModal'
                ];

                // Add event listeners for each modal
                modals.forEach(modalId => {
                    const modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        modalElement.addEventListener('show.bs.modal', () => {
                            // Close all other modals when this one is about to show
                            modals.forEach(otherId => {
                                if (otherId !== modalId) {
                                    const otherModal = document.getElementById(otherId);
                                    if (otherModal) {
                                        const bsModal = bootstrap.Modal.getInstance(otherModal);
                                        if (bsModal) {
                                            bsModal.hide();
                                        }
                                    }
                                }
                            });
                        });
                    }
                });
            }

            clearMeasurementInputs() {
                document.getElementById('weightInput').value = '';
                document.getElementById('heightInput').value = '';
                document.getElementById('bmiDisplay').value = '';
                document.getElementById('statusSelect').value = '';
            }

            clearAllInputs() {
                // Clear add modal
                document.getElementById('addMeasurementForm').reset();
                document.getElementById('childInfoCard').style.display = 'none';
                document.getElementById('measurementDate').valueAsDate = new Date();
                this.clearMeasurementInputs();

                // Clear edit modal
                document.getElementById('editMeasurementForm').reset();
                document.getElementById('editRecordId').value = '';
                document.getElementById('editBmiDisplay').value = '';
                document.getElementById('editStatusSelect').value = '';

                this.isEditMode = false;
                this.currentEditRecordId = null;
            }

            async showChildInfo(childId) {
                if (!childId) {
                    document.getElementById('childInfoCard').style.display = 'none';
                    return;
                }

                const child = this.children.find(c => c.child_id == childId);
                if (!child) return;

                const birthDate = new Date(child.birthdate);
                const age = this.calculateAge(birthDate);

                document.getElementById('childInfoName').textContent = `${child.first_name} ${child.last_name}`;
                document.getElementById('childInfoGender').textContent = child.gender;
                document.getElementById('childInfoBirthdate').textContent = this.formatDate(birthDate);
                document.getElementById('childInfoAge').textContent = `${age} years`;

                document.getElementById('childInfoCard').style.display = 'block';
            }

            calculateBMIAndStatus(isEditMode = false) {
                const weightId = isEditMode ? 'editWeightInput' : 'weightInput';
                const heightId = isEditMode ? 'editHeightInput' : 'heightInput';
                const bmiId = isEditMode ? 'editBmiDisplay' : 'bmiDisplay';

                const weight = parseFloat(document.getElementById(weightId).value);
                const height = parseFloat(document.getElementById(heightId).value);

                let childId;
                let child;

                if (isEditMode) {
                    const childName = document.getElementById('editChildInfoName').textContent;
                    child = this.children.find(c => `${c.first_name} ${c.last_name}` === childName);
                } else {
                    childId = document.getElementById('childSelect').value;
                    child = this.children.find(c => c.child_id == childId);
                }

                if (weight && height && child) {
                    // Input validation
                    if (weight < 0.5 || weight > 200) {
                        document.getElementById(bmiId).value = 'Invalid weight';
                        return;
                    }

                    if (height < 30 || height > 250) {
                        document.getElementById(bmiId).value = 'Invalid height';
                        return;
                    }

                    // Calculate BMI
                    const heightInMeters = height / 100;
                    const bmi = weight / (heightInMeters * heightInMeters);
                    document.getElementById(bmiId).value = bmi.toFixed(1);
                } else {
                    document.getElementById(bmiId).value = '';
                }
            }

            calculateAgeInMonths(birthDate, measurementDate) {
                const years = measurementDate.getFullYear() - birthDate.getFullYear();
                const months = measurementDate.getMonth() - birthDate.getMonth();
                const days = measurementDate.getDate() - birthDate.getDate();

                let totalMonths = years * 12 + months;
                if (days < 0) totalMonths--;

                return Math.max(0, totalMonths);
            }

            formatDate(date) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ];

                const d = new Date(date);
                return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
            }

            determineNutritionalStatus(bmi, ageInMonths, gender) {
                const ageInYears = ageInMonths / 12;

                // Find statuses from loaded data with better matching
                const normalStatus = this.nutritionStatuses?.find(s =>
                    s.status_name && (
                        s.status_name.toLowerCase().includes('normal') ||
                        s.status_name.toLowerCase().includes('healthy')
                    )
                );

                const underweightStatus = this.nutritionStatuses?.find(s =>
                    s.status_name &&
                    s.status_name.toLowerCase().includes('underweight') &&
                    !s.status_name.toLowerCase().includes('severely')
                );

                const severelyUnderweightStatus = this.nutritionStatuses?.find(s =>
                    s.status_name && (
                        s.status_name.toLowerCase().includes('severely underweight') ||
                        s.status_name.toLowerCase().includes('severe underweight')
                    )
                );

                const overweightStatus = this.nutritionStatuses?.find(s =>
                    s.status_name && s.status_name.toLowerCase().includes('overweight')
                );

                // Default fallbacks if database lookup fails
                const defaultStatuses = {
                    normal: normalStatus || {
                        name: 'Normal Weight',
                        id: 1
                    },
                    underweight: underweightStatus || {
                        name: 'Underweight',
                        id: 2
                    },
                    severelyUnderweight: severelyUnderweightStatus || {
                        name: 'Severely Underweight',
                        id: 3
                    },
                    overweight: overweightStatus || {
                        name: 'Overweight',
                        id: 4
                    }
                };

                let selectedStatus = defaultStatuses.normal; // Default to NORMAL, not severely underweight

                // Age-specific BMI thresholds (simplified but more accurate)
                if (ageInYears < 2) {
                    // Infants and toddlers (0-2 years)
                    if (bmi < 13) {
                        selectedStatus = defaultStatuses.severelyUnderweight;
                    } else if (bmi < 15) {
                        selectedStatus = defaultStatuses.underweight;
                    } else if (bmi > 19) {
                        selectedStatus = defaultStatuses.overweight;
                    } else {
                        selectedStatus = defaultStatuses.normal;
                    }
                } else if (ageInYears < 5) {
                    // Preschoolers (2-5 years)
                    if (bmi < 13.5) {
                        selectedStatus = defaultStatuses.severelyUnderweight;
                    } else if (bmi < 15.5) {
                        selectedStatus = defaultStatuses.underweight;
                    } else if (bmi > 18) {
                        selectedStatus = defaultStatuses.overweight;
                    } else {
                        selectedStatus = defaultStatuses.normal;
                    }
                } else if (ageInYears < 12) {
                    // School age (5-12 years)
                    if (bmi < 14) {
                        selectedStatus = defaultStatuses.severelyUnderweight;
                    } else if (bmi < 16) {
                        selectedStatus = defaultStatuses.underweight;
                    } else if (bmi > 20) {
                        selectedStatus = defaultStatuses.overweight;
                    } else {
                        selectedStatus = defaultStatuses.normal;
                    }
                } else {
                    // Adolescents (12+ years) - closer to adult BMI ranges
                    if (bmi < 16) {
                        selectedStatus = defaultStatuses.severelyUnderweight;
                    } else if (bmi < 18.5) {
                        selectedStatus = defaultStatuses.underweight;
                    } else if (bmi > 25) {
                        selectedStatus = defaultStatuses.overweight;
                    } else {
                        selectedStatus = defaultStatuses.normal;
                    }
                }

                // Return the correct format - check if it's from database or default
                if (selectedStatus.status_name) {
                    return {
                        name: selectedStatus.status_name,
                        id: selectedStatus.status_id
                    };
                } else {
                    return selectedStatus; // Already in correct format
                }
            }

            async loadInitialData() {
                try {
                    // Load children for dropdown
                    const childrenResponse = await fetch('./child_data/get_children.php');
                    const childrenData = await childrenResponse.json();
                    this.children = childrenData.children || [];
                    console.log('Loaded children:', this.children.length);

                    // Load nutrition statuses
                    const statusResponse = await fetch('./child_data/get_nutrition_statuses.php');
                    const statusData = await statusResponse.json();
                    this.nutritionStatuses = statusData.statuses || [];
                    console.log('Loaded statuses:', this.nutritionStatuses);

                    // Load zones for add child modal
                    const zonesResponse = await fetch('./flagged_data/get_barangay_zones.php');
                    const zonesData = await zonesResponse.json();
                    this.zones = zonesData.zones || [];

                    this.populateDropdowns();
                } catch (error) {
                    console.error('Error loading initial data:', error);
                }
            }

            populateDropdowns() {
                const childSelect = document.getElementById('childSelect');
                childSelect.innerHTML = '<option value="">Select Child</option>';
                this.children.forEach(child => {
                    const option = document.createElement('option');
                    option.value = child.child_id;
                    option.textContent = `${child.first_name} ${child.last_name}`;
                    childSelect.appendChild(option);
                });

                const statusSelects = ['statusSelect', 'editStatusSelect'];
                statusSelects.forEach(selectId => {
                    const statusSelect = document.getElementById(selectId);
                    if (statusSelect) {
                        statusSelect.innerHTML = '<option value="" disabled>Select Nutritional Status</option>';
                        this.nutritionStatuses.forEach(status => {
                            const option = document.createElement('option');
                            option.value = status.status_id;
                            option.textContent = status.status_name;
                            statusSelect.appendChild(option);
                        });
                    }
                });

                // Populate zones dropdown for add child modal
                const newZoneSelect = document.getElementById('newZone');
                if (newZoneSelect && this.zones) {
                    newZoneSelect.innerHTML = '<option value="">Select Zone</option>';
                    this.zones.forEach(zone => {
                        const option = document.createElement('option');
                        option.value = zone.zone_id;
                        option.textContent = zone.zone_name;
                        newZoneSelect.appendChild(option);
                    });
                }
            }

            async loadRecords() {
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.itemsPerPage,
                        search: document.getElementById('searchInput')?.value || ''
                    });

                    const response = await fetch(`./child_data/get_child_nutrition_records.php?${params}`);
                    const data = await response.json();

                    this.records = data.records || [];
                    this.totalRecords = data.total || 0;

                    document.getElementById('totalRecordsCount').textContent = `${this.totalRecords} Records`;
                } catch (error) {
                    console.error('Error loading records:', error);
                }
            }

            loadTableView() {
                const tbody = document.getElementById('nutritionTableBody');
                let html = '';

                if (this.records.length === 0) {
                    html = `<tr class="no-data">
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fa-solid fa-chart-line"></i>
                                <h3>No nutrition records found</h3>
                                <p>Start tracking children's growth by adding measurements</p>
                                <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addMeasurementModal">
                                    <i class="fa-solid fa-plus"></i>
                                    Add First Measurement
                                </button>
                            </div>
                        </td>
                    </tr>`;
                } else {
                    this.records.forEach(record => {
                        const birthDate = new Date(record.birthdate);
                        const age = this.calculateAge(birthDate);
                        const initials = `${record.first_name[0]}${record.last_name[0]}`.toUpperCase();
                        const genderClass = record.gender.toLowerCase() === 'male' ? 'gender-male' : 'gender-female';
                        const statusClass = this.getStatusClass(record.status_name);
                        let lastRecordDate = null;
                        if (record.date_recorded != null) {
                            const parsed = new Date(record.date_recorded);
                            if (!isNaN(parsed.getTime())) {
                                lastRecordDate = parsed;
                            }
                        }


                        html += `<tr data-child-id="${record.child_id}">
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
                                <span class="age-badge">${age} yrs</span>
                                <span class="gender-badge ${genderClass}">${record.gender}</span>
                            </td>
                            <td>
                                <span class="fw-medium">${record.zone_name || 'N/A'}</span>
                            </td>
                            <td class="fw-medium">${record.weight || 'N/A'} kg</td>
                            <td class="fw-medium">${record.height || 'N/A'} cm</td>
                            <td>
                                <span class="bmi-display">${record.bmi || 'N/A'}</span>
                            </td>
                            <td>
                                <span class="status-badge ${statusClass}">${record.status_name || 'N/A'}</span>
                            </td>
                            <td>
                                <div class="date-info">
                                    <div class="fw-medium">${lastRecordDate ? this.formatDate(lastRecordDate) : 'N/A'}</div>
                                    <small class="text-muted">${lastRecordDate ? this.timeAgo(lastRecordDate) : 'N/A'}</small>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-action btn-primary" title="View Details" onclick="nutritionManager.viewChildDetails(${record.child_id})">
                                        <i class="fa-solid fa-eye text-white"></i>
                                    </button>
                                    <button class="btn-action btn-success" title="Add Measurement" onclick="nutritionManager.quickAddMeasurement(${record.child_id})">
                                        <i class="fa-solid fa-plus"></i>
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

            calculateAge(birthDate) {
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                return age;
            }

            getStatusClass(statusName) {
                if (!statusName) return '';

                const status = statusName.toLowerCase();
                if (status.includes('normal')) return 'status-normal';
                if (status.includes('underweight')) return 'status-underweight';
                if (status.includes('overweight')) return 'status-overweight';
                if (status.includes('severely')) return 'status-severely-underweight';
                return '';
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
                    const response = await fetch('./child_data/get_nutrition_statistics.php');
                    const stats = await response.json();

                    document.getElementById('totalChildrenCount').textContent = stats.total_children || 0;
                    document.getElementById('normalStatusCount').textContent = stats.normal_status || 0;
                    document.getElementById('underweightCount').textContent = stats.underweight || 0;
                    document.getElementById('recentRecordsCount').textContent = stats.recent_records || 0;
                } catch (error) {
                    console.error('Error updating statistics:', error);
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
                            onclick="nutritionManager.changePage(${this.currentPage - 1})">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                `;

                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                             onclick="nutritionManager.changePage(${i})">${i}</span>`;
                }

                html += `
                    <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                            onclick="nutritionManager.changePage(${this.currentPage + 1})">
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

            quickAddMeasurement(childId) {
                const child = this.children.find(c => c.child_id == childId);
                if (child) {
                    this.clearAllInputs();
                    document.getElementById('childSelect').value = childId;
                    this.showChildInfo(childId);
                    const modal = new bootstrap.Modal(document.getElementById('addMeasurementModal'));
                    modal.show();
                }
            }

            async editMeasurement(recordId) {
                try {
                    const response = await fetch(`./child_data/get_measurement_record.php?record_id=${recordId}`);
                    const data = await response.json();

                    if (data.record) {
                        this.isEditMode = true;
                        this.currentEditRecordId = recordId;

                        const record = data.record;
                        const child = this.children.find(c => c.child_id == record.child_id);

                        if (child) {
                            // Populate child info
                            const birthDate = new Date(child.birthdate);
                            const age = this.calculateAge(birthDate);

                            document.getElementById('editChildInfoName').textContent = `${child.first_name} ${child.last_name}`;
                            document.getElementById('editChildInfoGender').textContent = child.gender;
                            document.getElementById('editChildInfoBirthdate').textContent = this.formatDate(birthDate);
                            document.getElementById('editChildInfoAge').textContent = `${age} years`;

                            // Populate form
                            document.getElementById('editRecordId').value = recordId;
                            document.getElementById('editMeasurementDate').value = record.date_recorded;
                            document.getElementById('editWeightInput').value = record.weight;
                            document.getElementById('editHeightInput').value = record.height;

                            // Trigger calculation and set status
                            this.calculateBMIAndStatus(true);
                            document.getElementById('editStatusSelect').value = record.status_id;

                            const modal = new bootstrap.Modal(document.getElementById('editMeasurementModal'));
                            modal.show();
                        }
                    }
                } catch (error) {
                    console.error('Error loading measurement record:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load measurement record.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            async deleteMeasurement(recordId) {
                const result = await Swal.fire({
                    title: 'Delete Measurement?',
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
                            url: '../backend/admin/delete_measurement_record.php',
                            type: 'POST',
                            data: {
                                record_id: recordId
                            },
                            success: async (response) => {
                                const res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Measurement record has been deleted.',
                                        confirmButtonColor: '#3085d6'
                                    }).then((result) => {
                                        if (result) {
                                            window.location.reload();
                                        }
                                    })

                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: res.message || 'Failed to delete measurement record.',
                                        confirmButtonColor: '#dc3545'
                                    });
                                }
                            },
                            error: (xhr, status, error) => {
                                console.error('AJAX Error:', status, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while deleting the measurement.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                    } catch (error) {
                        console.error('Error deleting measurement:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the measurement.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }
            }

            async viewChildDetails(childId) {
                try {
                    const response = await fetch(`./child_data/get_child_details.php?child_id=${childId}`);
                    const data = await response.json();

                    if (data.child) {
                        this.currentChildData = data.child;
                        this.currentRecordsData = data.records || [];
                        this.showChildDetailsModal(data);
                    }
                } catch (error) {
                    console.error('Error loading child details:', error);
                }
            }

            showChildDetailsModal(data) {
                const child = data.child;
                const records = data.records || [];
                const birthDate = new Date(child.birthdate);
                const age = this.calculateAge(birthDate);
                const initials = `${child.first_name[0]}${child.last_name[0]}`.toUpperCase();

                document.getElementById('childDetailsModalTitle').innerHTML = `
                    <i class="fa-solid fa-user-circle"></i>
                    ${child.first_name} ${child.last_name}
                `;

                const content = `
                    <!-- Top Section: Basic Info and Measurement History -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="text-center mb-4">
                                <div class="child-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 28px;">
                                    ${initials}
                                </div>
                                <h4>${child.first_name} ${child.last_name}</h4>
                                <p class="text-muted">ID: #${child.child_id}</p>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fa-solid fa-info-circle"></i> Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-field">
                                        <strong>Full Name:</strong> ${child.first_name} ${child.last_name}
                                    </div>
                                    <div class="info-field">
                                        <strong>Age:</strong> ${age} years
                                    </div>
                                    <div class="info-field">
                                        <strong>Gender:</strong> ${child.gender}
                                    </div>
                                    <div class="info-field">
                                        <strong>Date of Birth:</strong> ${this.formatDate(birthDate)}
                                    </div>
                                    <div class="info-field">
                                        <strong>Zone:</strong> ${child.zone_name || 'N/A'}
                                    </div>
                                    <div class="info-field">
                                        <strong>Date Registered:</strong> ${this.formatDate(new Date(child.created_at))}
                                    </div>
                                    <div class="edit-child-info" style="font-size: 14px;">
                                        Edit Child Info:
                                        <button class="btn btn-sm btn-outline-primary ms-2"
                                            onclick="nutritionManager.editChildDetails(${child.child_id})">
                                            <i class="fa-solid fa-edit"></i> Edit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card" style="height: 100%;">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fa-solid fa-history"></i> Measurement History</h6>
                                    <span class="badge bg-secondary">${records.length} Records</span>
                                </div>
                                <div class="card-body">
                                    <div class="measurement-search-box">
                                        <i class="fa-solid fa-search"></i>
                                        <input type="text" id="measurementSearchInput" placeholder="Search measurements by date or status..." 
                                            onkeyup="nutritionManager.filterMeasurementHistory()">
                                    </div>
                                    <div class="record-timeline" id="measurementTimeline" style="max-height: 450px; overflow-y: auto;">
                                        ${this.renderMeasurementHistory(records)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                        <!-- Bottom Section: Full Width Growth Chart -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fa-solid fa-chart-line"></i> Growth Trends Over Time</h6>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm chart-toggle-btn ${this.currentChartType === 'weight' ? 'active' : ''}" onclick="nutritionManager.toggleChartType('weight')">Weight</button>
                                            <button type="button" class="btn btn-sm chart-toggle-btn ${this.currentChartType === 'height' ? 'active' : ''}" onclick="nutritionManager.toggleChartType('height')">Height</button>
                                            <button type="button" class="btn btn-sm chart-toggle-btn ${this.currentChartType === 'bmi' ? 'active' : ''}" onclick="nutritionManager.toggleChartType('bmi')">BMI</button>
                                        </div>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="chart-container">
                                            <canvas id="growthChart" style="width: 100% !important; height: 100% !important;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                document.getElementById('childDetailsContent').innerHTML = content;

                // Store original records for filtering
                this.originalMeasurementRecords = records;

                const modal = new bootstrap.Modal(document.getElementById('childDetailsModal'));
                modal.show();

                // Initialize chart after modal is shown
                setTimeout(() => {
                    this.initializeGrowthChart(records);
                }, 300);
            }


            initializeGrowthChart(records) {
                const ctx = document.getElementById('growthChart');
                if (!ctx) return;

                const sortedRecords = records.sort((a, b) => new Date(a.date_recorded) - new Date(b.date_recorded));

                const labels = sortedRecords.map(record => this.formatDate(new Date(record.date_recorded)));
                const weightData = sortedRecords.map(record => parseFloat(record.weight));
                const heightData = sortedRecords.map(record => parseFloat(record.height));
                const bmiData = sortedRecords.map(record => parseFloat(record.bmi));

                if (this.growthChart) {
                    this.growthChart.destroy();
                }

                this.growthChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Weight (kg)',
                                data: weightData,
                                borderColor: '#27ae60',
                                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                                tension: 0.4,
                                fill: true,
                                hidden: this.currentChartType !== 'weight'
                            },
                            {
                                label: 'Height (cm)',
                                data: heightData,
                                borderColor: '#2d5a3d',
                                backgroundColor: 'rgba(45, 90, 61, 0.1)',
                                tension: 0.4,
                                fill: false,
                                hidden: this.currentChartType !== 'height'
                            },
                            {
                                label: 'BMI',
                                data: bmiData,
                                borderColor: '#6c757d',
                                backgroundColor: 'rgba(108, 117, 125, 0.1)',
                                tension: 0.4,
                                fill: false,
                                hidden: this.currentChartType !== 'bmi'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Growth Trends Over Time',
                                color: '#343a40',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: '#343a40',
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                grid: {
                                    color: 'rgba(108, 117, 125, 0.2)'
                                },
                                ticks: {
                                    color: '#6c757d'
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(108, 117, 125, 0.2)'
                                },
                                ticks: {
                                    color: '#6c757d',
                                    maxRotation: 45
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        elements: {
                            point: {
                                radius: 4,
                                hoverRadius: 6,
                                backgroundColor: '#ffffff',
                                borderWidth: 2
                            }
                        }
                    }
                });
            }

            toggleChartType(type) {
                this.currentChartType = type;

                // Update button states
                document.querySelectorAll('.chart-toggle-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector(`[onclick="nutritionManager.toggleChartType('${type}')"]`).classList.add('active');

                if (!this.growthChart) return;

                this.growthChart.data.datasets.forEach((dataset, index) => {
                    if (type === 'weight' && index === 0) {
                        dataset.hidden = false;
                    } else if (type === 'height' && index === 1) {
                        dataset.hidden = false;
                    } else if (type === 'bmi' && index === 2) {
                        dataset.hidden = false;
                    } else {
                        dataset.hidden = true;
                    }
                });

                this.growthChart.update();
            }

            showGrowthChart(childId) {
                this.viewChildDetails(childId);
            }


            renderMeasurementHistory(records) {
                if (records.length === 0) {
                    return `
                        <div class="no-records-found">
                            <i class="fa-solid fa-chart-line"></i>
                            <h6>No measurement records found</h6>
                            <p class="mb-0">No measurements have been recorded for this child yet.</p>
                        </div>
                    `;
                }

                return records.map(record => {
                    const recordDate = new Date(record.date_recorded);
                    const statusClass = this.getStatusClass(record.status_name);
                    return `
                    <div class="timeline-item measurement-record-item" data-date="${this.formatDate(recordDate)}" data-status="${record.status_name.toLowerCase()}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${this.formatDate(recordDate)}</h6>
                                <p class="mb-1">
                                    Weight: <strong>${record.weight} kg</strong> | 
                                    Height: <strong>${record.height} cm</strong> | 
                                    BMI: <strong>${record.bmi}</strong>
                                </p>
                                <span class="status-badge ${statusClass}">${record.status_name}</span>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                                <small class="text-muted mb-2">${this.timeAgo(recordDate)}</small>
                                <div>
                                    <button class="edit-record-btn" onclick="nutritionManager.editMeasurement(${record.nutrition_id})" title="Edit Record">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="delete-record-btn" onclick="nutritionManager.deleteMeasurement(${record.nutrition_id})" title="Delete Record">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                }).join('');
            }


            filterMeasurementHistory() {
                const searchInput = document.getElementById('measurementSearchInput');
                if (!searchInput || !this.originalMeasurementRecords) return;

                const searchTerm = searchInput.value.toLowerCase().trim();
                let filteredRecords = this.originalMeasurementRecords;

                if (searchTerm) {
                    filteredRecords = this.originalMeasurementRecords.filter(record => {
                        const recordDate = new Date(record.date_recorded).toLocaleDateString().toLowerCase();
                        const statusName = record.status_name.toLowerCase();
                        const weight = record.weight.toString();
                        const height = record.height.toString();
                        const bmi = record.bmi.toString();

                        return recordDate.includes(searchTerm) ||
                            statusName.includes(searchTerm) ||
                            weight.includes(searchTerm) ||
                            height.includes(searchTerm) ||
                            bmi.includes(searchTerm);
                    });
                }

                // Update the timeline with filtered results
                const timeline = document.getElementById('measurementTimeline');
                if (timeline) {
                    timeline.innerHTML = this.renderMeasurementHistory(filteredRecords);
                }
            }

            async editChildDetails(childId) {
                try {
                    const response = await fetch(`./child_data/get_child_for_edit.php?child_id=${childId}`);
                    const data = await response.json();

                    if (data.child && data.zones) {
                        this.showEditChildDetailsModal(data.child, data.zones);
                    }
                } catch (error) {
                    console.error('Error loading child for edit:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load child information.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            showEditChildDetailsModal(child, zones) {
                const birthDate = new Date(child.birthdate);
                const age = this.calculateAge(birthDate);

                const content = `
                        <form id="editChildForm">
                            <input type="hidden" id="editChildId" value="${child.child_id}">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="editFirstName" value="${child.first_name}" required>
                                        <label for="editFirstName">
                                            <i class="fa-solid fa-user"></i> First Name
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="editLastName" value="${child.last_name}" required>
                                        <label for="editLastName">
                                            <i class="fa-solid fa-user"></i> Last Name
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="editGender" required>
                                            <option value="Male" ${child.gender === 'Male' ? 'selected' : ''}>Male</option>
                                            <option value="Female" ${child.gender === 'Female' ? 'selected' : ''}>Female</option>
                                        </select>
                                        <label for="editGender">
                                            <i class="fa-solid fa-venus-mars"></i> Gender
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="date" class="form-control" id="editBirthdate" value="${child.birthdate}" required>
                                        <label for="editBirthdate">
                                            <i class="fa-solid fa-calendar"></i> Date of Birth
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="editZone" required>
                                            <option value="">Select Zone</option>
                                            ${zones.map(zone => `
                                                <option value="${zone.zone_id}" ${zone.zone_id == child.zone_id ? 'selected' : ''}>
                                                    ${zone.zone_name}
                                                </option>
                                            `).join('')}
                                        </select>
                                        <label for="editZone">
                                            <i class="fa-solid fa-map-marker-alt"></i> Zone
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="editCurrentAge" value="${age} years" readonly>
                                        <label for="editCurrentAge">
                                            <i class="fa-solid fa-clock"></i> Current Age (Auto-calculated)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    `;

                document.getElementById('editChildDetailsContent').innerHTML = content;

                // Update age when birthdate changes
                document.getElementById('editBirthdate').addEventListener('change', (e) => {
                    const newBirthDate = new Date(e.target.value);
                    const newAge = this.calculateAge(newBirthDate);
                    document.getElementById('editCurrentAge').value = `${newAge} years`;
                });

                const modal = new bootstrap.Modal(document.getElementById('editChildDetailsModal'));
                modal.show();
            }
        }

        // Initialize nutrition manager
        let nutritionManager;
        document.addEventListener('DOMContentLoaded', function() {
            nutritionManager = new NutritionRecordsManager();
        });

        // Global functions
        async function addMeasurement() {
            const childId = document.getElementById('childSelect').value;
            const date = document.getElementById('measurementDate').value;
            const weight = document.getElementById('weightInput').value;
            const height = document.getElementById('heightInput').value;
            const statusId = document.getElementById('statusSelect').value;

            if (!childId || !date || !weight || !height || !statusId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields and ensure BMI/status is calculated.',
                    confirmButtonColor: '#2d5a3d'
                });
                return;
            }

            if (weight <= 0 || height <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Weight and Height must be positive numbers.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (height < 30 || height > 200) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unrealistic Height',
                    text: 'Please enter a realistic height between 30 cm and 200 cm.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            const bmi = parseFloat(document.getElementById('bmiDisplay').value);

            if (bmi <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid BMI',
                    text: 'Please enter a valid BMI.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            console.log('Adding measurement:', {
                childId,
                date,
                weight,
                height,
                bmi,
                statusId
            });
            try {
                $.ajax({
                    url: '../backend/admin/add_nutrition_record.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        child_id: childId,
                        weight: weight,
                        height: height,
                        bmi: bmi,
                        status_id: statusId,
                        date_recorded: date
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Added!',
                                text: 'Measurement added successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
                                bootstrap.Modal.getInstance(document.getElementById('addMeasurementModal')).hide();
                                nutritionManager.loadRecords().then(() => {
                                    nutritionManager.loadTableView();
                                    nutritionManager.updateStatistics();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to add measurement.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error adding measurement:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while adding the measurement.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            } catch (error) {
                console.error('Error adding measurement:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding the measurement.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        async function updateMeasurement() {
            const recordId = document.getElementById('editRecordId').value;
            const date = document.getElementById('editMeasurementDate').value;
            const weight = document.getElementById('editWeightInput').value;
            const height = document.getElementById('editHeightInput').value;
            const statusId = document.getElementById('editStatusSelect').value;

            if (!recordId || !date || !weight || !height || !statusId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields and ensure BMI/status is calculated.',
                    confirmButtonColor: '#2d5a3d'
                });
                return;
            }

            if (weight <= 0 || height <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Weight and Height must be positive numbers.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (height < 30 || height > 200) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unrealistic Height',
                    text: 'Please enter a realistic height between 30 cm and 200 cm.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            const bmi = parseFloat(document.getElementById('editBmiDisplay').value);

            if (bmi <= 0 || isNaN(bmi)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid BMI',
                    text: 'Calculated BMI is invalid. Please check weight and height inputs.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }


            try {
                $.ajax({
                    url: '../backend/admin/update_nutrition_record.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        record_id: recordId,
                        weight: weight,
                        height: height,
                        bmi: bmi,
                        status_id: statusId,
                        date_recorded: date
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'Measurement updated successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
                                bootstrap.Modal.getInstance(document.getElementById('editMeasurementModal')).hide();
                                nutritionManager.loadRecords().then(() => {
                                    nutritionManager.loadTableView();
                                    nutritionManager.updateStatistics();
                                });

                                // Refresh child details modal if open
                                if (nutritionManager.currentChildData) {
                                    nutritionManager.viewChildDetails(nutritionManager.currentChildData.child_id);
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to update measurement.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating measurement:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating the measurement.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
            } catch (error) {
                console.error('Error updating measurement:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the measurement.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        function exportRecords() {
            window.open('./child_data/export_nutrition_records.php', '_blank');
        }

        function printChildReport() {
            if (!nutritionManager.currentChildData || !nutritionManager.currentRecordsData) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Data Available',
                    text: 'Please select a child to view their report.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            const printWindow = window.open('./child_data/child_nutrition_print.php', '_blank', 'width=800,height=600');

            printWindow.onload = function() {
                if (printWindow.populateReport) {
                    printWindow.populateReport(nutritionManager.currentChildData, nutritionManager.currentRecordsData);
                    // Auto-print after a short delay to ensure data is loaded
                    setTimeout(() => {
                        printWindow.print();
                    }, 500);
                }

                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            };
        }

        function saveChildDetails() {
            const childId = document.getElementById('editChildId').value;
            const firstName = document.getElementById('editFirstName').value.trim();
            const lastName = document.getElementById('editLastName').value.trim();
            const gender = document.getElementById('editGender').value;
            const birthdate = document.getElementById('editBirthdate').value;
            const zoneId = document.getElementById('editZone').value;

            // Validation
            if (!firstName || !lastName || !gender || !birthdate || !zoneId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Check if birthdate is not in the future
            const today = new Date();
            const selectedDate = new Date(birthdate);
            if (selectedDate > today) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Date of birth cannot be in the future.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            try {
                $.ajax({
                    url: '../backend/admin/update_child_details.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        child_id: childId,
                        first_name: firstName,
                        last_name: lastName,
                        gender: gender,
                        birthdate: birthdate,
                        zone_id: zoneId
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'Child details updated successfully.',
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
                                text: result.message || 'Failed to update child details.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating child details:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating child details.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            } catch (error) {
                console.error('Error updating child details:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating child details.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        function addChild() {
            const firstName = document.getElementById('newFirstName').value.trim();
            const lastName = document.getElementById('newLastName').value.trim();
            const gender = document.getElementById('newGender').value;
            const birthdate = document.getElementById('newBirthdate').value;
            const zoneId = document.getElementById('newZone').value;

            // Validation
            if (!firstName || !lastName || !gender || !birthdate || !zoneId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Check if birthdate is not in the future
            const today = new Date();
            const selectedDate = new Date(birthdate);
            if (selectedDate > today) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Date of birth cannot be in the future.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            try {
                $.ajax({
                    url: '../backend/admin/add_child.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        first_name: firstName,
                        last_name: lastName,
                        gender: gender,
                        birthdate: birthdate,
                        zone_id: zoneId
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Added!',
                                text: 'Child added successfully.',
                                confirmButtonColor: '#27ae60'
                            }).then(() => {
                                bootstrap.Modal.getInstance(document.getElementById('addChildModal')).hide();
                                // Reload data to show the new child
                                nutritionManager.loadInitialData().then(() => {
                                    nutritionManager.loadRecords().then(() => {
                                        nutritionManager.loadTableView();
                                        nutritionManager.updateStatistics();
                                    });
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to add child.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error adding child:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while adding the child.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            } catch (error) {
                console.error('Error adding child:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding the child.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }
    </script>
</body>

</html>