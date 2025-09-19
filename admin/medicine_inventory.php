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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <title>Medicine Inventory Management</title>
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

        .medicine-card {
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
            background-color: var(--success-green);
            color: white;
        }

        .status-inactive {
            background-color: var(--medium-grey);
            color: white;
        }

        .status-expired {
            background-color: var(--danger-red);
            color: white;
        }

        .stock-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .stock-normal {
            background-color: #d4edda;
            color: var(--success-green);
        }

        .stock-low {
            background-color: #fff3cd;
            color: #856404;
        }

        .stock-critical {
            background-color: #f8d7da;
            color: var(--danger-red);
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

        .medicine-icon {
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

        .expiry-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .expiry-danger {
            background-color: #f8d7da;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .details-section {
            background: var(--light-grey);
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }

        .info-display {
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

        .info-display strong {
            color: var(--medium-grey);
            margin-right: 8px;
            font-weight: 500;
        }

        .table-wrapper {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .medicine-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .medicine-table th {
            background: var(--primary-red);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .medicine-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-grey);
            vertical-align: middle;
        }

        .medicine-table tbody tr:hover {
            background-color: var(--light-grey);
        }

        .name-cell {
            min-width: 200px;
        }

        .actions-cell {
            min-width: 120px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--medium-grey);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--border-grey);
        }

        .search-box {
            position: relative;
            display: inline-block;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-grey);
        }

        .search-box input {
            padding-left: 35px;
            border: 1px solid var(--border-grey);
            border-radius: 8px;
            padding: 8px 12px 8px 35px;
            width: 250px;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-radius: 0 0 12px 12px;
            border-top: 1px solid var(--border-grey);
        }

        .showing-info {
            color: var(--medium-grey);
            font-size: 0.9rem;
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
            color: var(--dark-grey);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-pagination:hover:not(:disabled) {
            background: var(--primary-red);
            color: white;
        }

        .btn-pagination:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-number {
            padding: 8px 12px;
            border: 1px solid var(--border-grey);
            background: white;
            color: var(--dark-grey);
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
    </style>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="medicine-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fa-solid fa-pills"></i>
                        Medicine Inventory Management
                    </h1>
                    <p class="mb-0 opacity-90">Manage medicine stock, track expiry dates, and monitor dispensing</p>
                </div>
                <div class="text-end">
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-md" onclick="exportMedicineInventory()">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
                            <i class="fa-solid fa-plus"></i> Add Medicine
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
                            <div class="stat-number text-primary" id="totalMedicinesCount">0</div>
                            <div class="stat-label">Total Medicines</div>
                        </div>
                        <i class="fa-solid fa-pills text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-warning" id="lowStockCount">0</div>
                            <div class="stat-label">Low Stock</div>
                        </div>
                        <i class="fa-solid fa-exclamation-triangle text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-danger" id="expiredCount">0</div>
                            <div class="stat-label">Expired</div>
                        </div>
                        <i class="fa-solid fa-calendar-times text-danger fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-info" id="totalValueCount">₱0</div>
                            <div class="stat-label">Total Value</div>
                        </div>
                        <i class="fa-solid fa-peso-sign text-info fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="filter-section">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-chart-pie"></i>
                        Stock Status Distribution
                    </h5>
                    <div class="chart-container">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="filter-section">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-calendar-check"></i>
                        Expiry Alerts
                    </h5>
                    <div id="expiryAlerts" style="max-height: 320px; overflow-y: auto;">
                        <!-- Expiry alerts will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <h3 class="mb-0">Medicine Inventory</h3>
                <span class="badge bg-secondary" id="totalMedicinesBadge">0 Items</span>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="statusFilter" onchange="filterMedicines()">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Expired">Expired</option>
                </select>
                <select class="form-select form-select-sm" id="stockFilter" onchange="filterMedicines()">
                    <option value="">All Stock Levels</option>
                    <option value="normal">Normal Stock</option>
                    <option value="low">Low Stock</option>
                    <option value="critical">Critical Stock</option>
                </select>
                <div class="search-box">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search medicines...">
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-wrapper">
                <table class="medicine-table" id="medicineTable">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Form & Strength</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Expiry Date</th>
                            <th>Unit Cost</th>
                            <th>Total Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="medicineTableBody">
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

    <!-- Add Medicine Modal -->
    <div class="modal fade" id="addMedicineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-plus"></i>
                        Add New Medicine
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMedicineForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="addMedicineName" required oninput="removeSpecialCharacters(this)">
                                    <label for="addMedicineName">
                                        <i class="fa-solid fa-pills"></i> Medicine Name *
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="addBrand" oninput="removeSpecialCharacters(this)">
                                    <label for="addBrand">
                                        <i class="fa-solid fa-tag"></i> Brand
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="addGenericName" oninput="removeSpecialCharacters(this)">
                                    <label for="addGenericName">
                                        <i class="fa-solid fa-certificate"></i> Generic Name
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="addDosageForm">
                                        <option value="">Select Form</option>
                                        <option value="Tablet">Tablet</option>
                                        <option value="Capsule">Capsule</option>
                                        <option value="Syrup">Syrup</option>
                                        <option value="Suspension">Suspension</option>
                                        <option value="Drops">Drops</option>
                                        <option value="Powder">Powder</option>
                                        <option value="Injection">Injection</option>
                                        <option value="Cream">Cream</option>
                                        <option value="Ointment">Ointment</option>
                                    </select>
                                    <label for="addDosageForm">
                                        <i class="fa-solid fa-flask"></i> Dosage Form
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="addStrength" placeholder="e.g., 500mg, 10ml">
                                    <label for="addStrength">
                                        <i class="fa-solid fa-weight"></i> Strength
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="addUnit">
                                        <option value="">Select Unit</option>
                                        <option value="pieces">Pieces</option>
                                        <option value="bottles">Bottles</option>
                                        <option value="boxes">Boxes</option>
                                        <option value="sachets">Sachets</option>
                                        <option value="vials">Vials</option>
                                        <option value="tubes">Tubes</option>
                                    </select>
                                    <label for="addUnit">
                                        <i class="fa-solid fa-box"></i> Unit
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="addStockQuantity" step="0.01" min="0" required>
                                    <label for="addStockQuantity">
                                        <i class="fa-solid fa-warehouse"></i> Stock Quantity *
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="addMinimumStock" step="0.01" min="0" value="10">
                                    <label for="addMinimumStock">
                                        <i class="fa-solid fa-chart-line"></i> Minimum Stock
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="addUnitCost" step="0.01" min="0">
                                    <label for="addUnitCost">
                                        <i class="fa-solid fa-peso-sign"></i> Unit Cost
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="addExpiryDate">
                                    <label for="addExpiryDate">
                                        <i class="fa-solid fa-calendar"></i> Expiry Date
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="addBatchNumber">
                                    <label for="addBatchNumber">
                                        <i class="fa-solid fa-barcode"></i> Batch Number
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="addSupplier">
                                    <label for="addSupplier">
                                        <i class="fa-solid fa-truck"></i> Supplier
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="addDescription" style="height: 100px" placeholder="Additional notes or description..."></textarea>
                            <label for="addDescription">
                                <i class="fa-solid fa-comment"></i> Description
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="addMedicine()">
                        <i class="fa-solid fa-save"></i> Add Medicine
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Medicine Modal -->
    <div class="modal fade" id="editMedicineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-edit"></i>
                        Edit Medicine
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editMedicineForm">
                        <input type="hidden" id="editMedicineId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editMedicineName" required oninput="removeSpecialCharacters(this)">
                                    <label for="editMedicineName">
                                        <i class="fa-solid fa-pills"></i> Medicine Name *
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editBrand" oninput="removeSpecialCharacters(this)">
                                    <label for="editBrand">
                                        <i class="fa-solid fa-tag"></i> Brand
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editGenericName" oninput="removeSpecialCharacters(this)">
                                    <label for="editGenericName">
                                        <i class="fa-solid fa-certificate"></i> Generic Name
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="editDosageForm">
                                        <option value="">Select Form</option>
                                        <option value="Tablet">Tablet</option>
                                        <option value="Capsule">Capsule</option>
                                        <option value="Syrup">Syrup</option>
                                        <option value="Suspension">Suspension</option>
                                        <option value="Drops">Drops</option>
                                        <option value="Powder">Powder</option>
                                        <option value="Injection">Injection</option>
                                        <option value="Cream">Cream</option>
                                        <option value="Ointment">Ointment</option>
                                    </select>
                                    <label for="editDosageForm">
                                        <i class="fa-solid fa-flask"></i> Dosage Form
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editStrength">
                                    <label for="editStrength">
                                        <i class="fa-solid fa-weight"></i> Strength
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="editUnit">
                                        <option value="">Select Unit</option>
                                        <option value="pieces">Pieces</option>
                                        <option value="bottles">Bottles</option>
                                        <option value="boxes">Boxes</option>
                                        <option value="sachets">Sachets</option>
                                        <option value="vials">Vials</option>
                                        <option value="tubes">Tubes</option>
                                    </select>
                                    <label for="editUnit">
                                        <i class="fa-solid fa-box"></i> Unit
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="editStockQuantity" step="0.01" min="0" required>
                                    <label for="editStockQuantity">
                                        <i class="fa-solid fa-warehouse"></i> Stock Quantity *
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="editMinimumStock" step="0.01" min="0">
                                    <label for="editMinimumStock">
                                        <i class="fa-solid fa-chart-line"></i> Minimum Stock
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="editUnitCost" step="0.01" min="0">
                                    <label for="editUnitCost">
                                        <i class="fa-solid fa-peso-sign"></i> Unit Cost
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="editExpiryDate">
                                    <label for="editExpiryDate">
                                        <i class="fa-solid fa-calendar"></i> Expiry Date
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editBatchNumber">
                                    <label for="editBatchNumber">
                                        <i class="fa-solid fa-barcode"></i> Batch Number
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="editSupplier">
                                    <label for="editSupplier">
                                        <i class="fa-solid fa-truck"></i> Supplier
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="editDescription" style="height: 80px" placeholder="Additional notes or description..."></textarea>
                                    <label for="editDescription">
                                        <i class="fa-solid fa-comment"></i> Description
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="editStatus">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Expired">Expired</option>
                                    </select>
                                    <label for="editStatus">
                                        <i class="fa-solid fa-flag"></i> Status
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="updateMedicine()">
                        <i class="fa-solid fa-save"></i> Update Medicine
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Medicine Modal -->
    <div class="modal fade" id="viewMedicineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMedicineTitle">
                        <i class="fa-solid fa-eye"></i>
                        Medicine Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewMedicineContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editMedicineFromView()">
                        <i class="fa-solid fa-edit"></i> Edit Medicine
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        class MedicineInventoryManager {
            constructor() {
                this.currentPage = 1;
                this.itemsPerPage = 10;
                this.totalRecords = 0;
                this.medicines = [];
                this.currentMedicine = null;
                this.chart = null;
                this.init();
            }

            async init() {
                this.setupEventListeners();
                await this.loadMedicines();
                this.loadTableView();
                this.updateStatistics();
                this.loadExpiryAlerts();
                this.initializeChart();
            }

            setupEventListeners() {
                document.getElementById('searchInput').addEventListener('input', (e) => this.searchMedicines(e.target.value));
            }

            async loadMedicines() {
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.itemsPerPage,
                        search: document.getElementById('searchInput')?.value || '',
                        status: document.getElementById('statusFilter')?.value || '',
                        stock: document.getElementById('stockFilter')?.value || ''
                    });

                    const response = await fetch(`./medicine_data/get_medicines.php?${params}`);
                    const data = await response.json();

                    this.medicines = data.medicines || [];
                    this.totalRecords = data.total || 0;

                    document.getElementById('totalMedicinesBadge').textContent = `${this.totalRecords} Items`;
                } catch (error) {
                    console.error('Error loading medicines:', error);
                }
            }

            loadTableView() {
                const tbody = document.getElementById('medicineTableBody');
                let html = '';

                if (this.medicines.length === 0) {
                    html = `<tr class="no-data">
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fa-solid fa-pills"></i>
                                <h3>No medicines found</h3>
                                <p>No medicines match your current filters</p>
                            </div>
                        </td>
                    </tr>`;
                } else {
                    this.medicines.forEach(medicine => {
                        const stockStatus = this.getStockStatus(medicine.stock_quantity, medicine.minimum_stock);
                        const statusClass = this.getStatusClass(medicine.status);
                        const expiryStatus = this.getExpiryStatus(medicine.expiry_date);
                        const totalValue = (parseFloat(medicine.stock_quantity) * parseFloat(medicine.unit_cost || 0)).toFixed(2);
                        const initials = medicine.medicine_name.substring(0, 2).toUpperCase();

                        html += `<tr>
                            <td class="name-cell">
                                <div class="d-flex align-items-center">
                                    <div class="medicine-icon me-3">${initials}</div>
                                    <div>
                                        <div class="fw-bold">${medicine.medicine_name}</div>
                                        <small class="text-muted">${medicine.brand || medicine.generic_name || 'No brand'}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>${medicine.dosage_form || '-'}</div>
                                <small class="text-muted">${medicine.strength || 'No strength specified'}</small>
                            </td>
                            <td>
                                <div class="fw-bold">${medicine.stock_quantity} ${medicine.unit || ''}</div>
                                <span class="stock-badge ${stockStatus.class}">${stockStatus.text}</span>
                            </td>
                            <td>
                                <span class="status-badge ${statusClass}">${medicine.status}</span>
                                ${expiryStatus.warning ? `<div class="mt-1"><small class="text-${expiryStatus.class}">${expiryStatus.text}</small></div>` : ''}
                            </td>
                            <td>
                                <div class="fw-medium">${medicine.expiry_date ? this.formatDate(new Date(medicine.expiry_date)) : 'No expiry'}</div>
                                ${medicine.expiry_date ? `<small class="text-muted">${this.timeUntilExpiry(new Date(medicine.expiry_date))}</small>` : ''}
                            </td>
                            <td>₱${parseFloat(medicine.unit_cost || 0).toFixed(2)}</td>
                            <td class="fw-bold">₱${totalValue}</td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-action btn-primary" title="View Details" onclick="medicineManager.viewMedicine(${medicine.medicine_id})">
                                        <i class="fa-solid fa-eye text-white"></i>
                                    </button>
                                    <button class="btn-action btn-success" title="Edit Medicine" onclick="medicineManager.editMedicine(${medicine.medicine_id})">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-danger" title="Delete Medicine" onclick="medicineManager.deleteMedicine(${medicine.medicine_id})">
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

            getStockStatus(currentStock, minimumStock) {
                const stock = parseFloat(currentStock);
                const minimum = parseFloat(minimumStock);

                if (stock <= 0) {
                    return { text: 'Out of Stock', class: 'stock-critical' };
                } else if (stock <= minimum) {
                    return { text: 'Low Stock', class: 'stock-low' };
                } else if (stock <= minimum * 1.5) {
                    return { text: 'Normal', class: 'stock-normal' };
                } else {
                    return { text: 'Good Stock', class: 'stock-normal' };
                }
            }

            getStatusClass(status) {
                if (!status) return '';
                const statusLower = status.toLowerCase();
                if (statusLower === 'active') return 'status-active';
                if (statusLower === 'inactive') return 'status-inactive';
                if (statusLower === 'expired') return 'status-expired';
                return '';
            }

            getExpiryStatus(expiryDate) {
                if (!expiryDate) return { warning: false };

                const today = new Date();
                const expiry = new Date(expiryDate);
                const daysUntilExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));

                if (daysUntilExpiry < 0) {
                    return { warning: true, text: 'Expired', class: 'danger' };
                } else if (daysUntilExpiry <= 30) {
                    return { warning: true, text: 'Expiring Soon', class: 'warning' };
                } else if (daysUntilExpiry <= 90) {
                    return { warning: true, text: 'Check Expiry', class: 'info' };
                }

                return { warning: false };
            }

            formatDate(date) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                const d = new Date(date);
                return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
            }

            timeUntilExpiry(expiryDate) {
                const today = new Date();
                const expiry = new Date(expiryDate);
                const daysUntil = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));

                if (daysUntil < 0) {
                    return `Expired ${Math.abs(daysUntil)} days ago`;
                } else if (daysUntil === 0) {
                    return 'Expires today';
                } else if (daysUntil === 1) {
                    return 'Expires tomorrow';
                } else if (daysUntil <= 30) {
                    return `${daysUntil} days left`;
                } else if (daysUntil <= 365) {
                    const months = Math.floor(daysUntil / 30);
                    return `${months} month${months > 1 ? 's' : ''} left`;
                } else {
                    const years = Math.floor(daysUntil / 365);
                    return `${years} year${years > 1 ? 's' : ''} left`;
                }
            }

            async updateStatistics() {
                try {
                    const response = await fetch('./medicine_data/get_medicine_statistics.php');
                    const stats = await response.json();

                    document.getElementById('totalMedicinesCount').textContent = stats.total || 0;
                    document.getElementById('lowStockCount').textContent = stats.low_stock || 0;
                    document.getElementById('expiredCount').textContent = stats.expired || 0;
                    document.getElementById('totalValueCount').textContent = `₱${(stats.total_value || 0).toLocaleString()}`;
                } catch (error) {
                    console.error('Error updating statistics:', error);
                }
            }

            async loadExpiryAlerts() {
                try {
                    const response = await fetch('./medicine_data/get_expiry_alerts.php');
                    const data = await response.json();

                    const container = document.getElementById('expiryAlerts');
                    let html = '';

                    if (data.alerts && data.alerts.length > 0) {
                        data.alerts.forEach(alert => {
                            const alertClass = alert.days_until_expiry < 0 ? 'expiry-danger' : 'expiry-warning';
                            const alertIcon = alert.days_until_expiry < 0 ? 'fa-times-circle' : 'fa-exclamation-triangle';
                            
                            html += `
                                <div class="${alertClass}">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid ${alertIcon} me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${alert.medicine_name}</h6>
                                            <p class="mb-1 small">${alert.brand || alert.generic_name}</p>
                                            <small class="fw-bold">
                                                ${alert.days_until_expiry < 0 ? 
                                                    `Expired ${Math.abs(alert.days_until_expiry)} days ago` : 
                                                    `Expires in ${alert.days_until_expiry} days`}
                                            </small>
                                        </div>
                                        <small class="text-muted">${alert.stock_quantity} ${alert.unit}</small>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<p class="text-muted text-center">No expiry alerts</p>';
                    }

                    container.innerHTML = html;
                } catch (error) {
                    console.error('Error loading expiry alerts:', error);
                }
            }

            initializeChart() {
                const ctx = document.getElementById('stockChart');
                if (!ctx) return;

                this.chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Normal Stock', 'Low Stock', 'Out of Stock'],
                        datasets: [{
                            data: [0, 0, 0],
                            backgroundColor: [
                                '#27ae60',
                                '#ffc107',
                                '#dc3545'
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

                this.updateChart();
            }

            async updateChart() {
                try {
                    const response = await fetch('./medicine_data/get_stock_chart_data.php');
                    const data = await response.json();

                    this.chart.data.datasets[0].data = [
                        data.normal || 0,
                        data.low || 0,
                        data.out_of_stock || 0
                    ];

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
                            onclick="medicineManager.changePage(${this.currentPage - 1})">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                `;

                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                             onclick="medicineManager.changePage(${i})">${i}</span>`;
                }

                html += `
                    <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                            onclick="medicineManager.changePage(${this.currentPage + 1})">
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
                    await this.loadMedicines();
                    this.loadTableView();
                }
            }

            async searchMedicines(query) {
                this.currentPage = 1;
                await this.loadMedicines();
                this.loadTableView();
            }

            async viewMedicine(medicineId) {
                try {
                    const response = await fetch(`./medicine_data/get_medicine.php?medicine_id=${medicineId}`);
                    const data = await response.json();

                    if (data.medicine) {
                        this.currentMedicine = data.medicine;
                        this.showViewModal(data.medicine);
                    }
                } catch (error) {
                    console.error('Error loading medicine:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load medicine details.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            showViewModal(medicine) {
                const stockStatus = this.getStockStatus(medicine.stock_quantity, medicine.minimum_stock);
                const statusClass = this.getStatusClass(medicine.status);
                const expiryStatus = this.getExpiryStatus(medicine.expiry_date);
                const totalValue = (parseFloat(medicine.stock_quantity) * parseFloat(medicine.unit_cost || 0)).toFixed(2);

                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="details-section">
                                <h6><i class="fa-solid fa-pills"></i> Medicine Information</h6>
                                <div class="info-display">
                                    <strong>Medicine Name:</strong> ${medicine.medicine_name}
                                </div>
                                <div class="info-display">
                                    <strong>Brand:</strong> ${medicine.brand || 'No brand specified'}
                                </div>
                                <div class="info-display">
                                    <strong>Generic Name:</strong> ${medicine.generic_name || 'Not specified'}
                                </div>
                                <div class="info-display">
                                    <strong>Form & Strength:</strong> ${medicine.dosage_form || 'N/A'} ${medicine.strength ? '- ' + medicine.strength : ''}
                                </div>
                                <div class="info-display">
                                    <strong>Status:</strong> <span class="status-badge ${statusClass}">${medicine.status}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="details-section">
                                <h6><i class="fa-solid fa-warehouse"></i> Stock Information</h6>
                                <div class="info-display">
                                    <strong>Current Stock:</strong> ${medicine.stock_quantity} ${medicine.unit || ''}
                                </div>
                                <div class="info-display">
                                    <strong>Stock Status:</strong> <span class="stock-badge ${stockStatus.class}">${stockStatus.text}</span>
                                </div>
                                <div class="info-display">
                                    <strong>Minimum Stock:</strong> ${medicine.minimum_stock} ${medicine.unit || ''}
                                </div>
                                <div class="info-display">
                                    <strong>Unit Cost:</strong> ₱${parseFloat(medicine.unit_cost || 0).toFixed(2)}
                                </div>
                                <div class="info-display">
                                    <strong>Total Value:</strong> ₱${totalValue}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="details-section">
                                <h6><i class="fa-solid fa-calendar"></i> Expiry Information</h6>
                                <div class="info-display">
                                    <strong>Expiry Date:</strong> ${medicine.expiry_date ? this.formatDate(new Date(medicine.expiry_date)) : 'No expiry date'}
                                </div>
                                ${medicine.expiry_date ? `
                                    <div class="info-display">
                                        <strong>Time Until Expiry:</strong> ${this.timeUntilExpiry(new Date(medicine.expiry_date))}
                                    </div>
                                ` : ''}
                                <div class="info-display">
                                    <strong>Batch Number:</strong> ${medicine.batch_number || 'Not specified'}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="details-section">
                                <h6><i class="fa-solid fa-truck"></i> Supply Information</h6>
                                <div class="info-display">
                                    <strong>Supplier:</strong> ${medicine.supplier || 'Not specified'}
                                </div>
                                <div class="info-display">
                                    <strong>Created:</strong> ${this.formatDate(new Date(medicine.created_at))}
                                </div>
                                <div class="info-display">
                                    <strong>Last Updated:</strong> ${this.formatDate(new Date(medicine.updated_at))}
                                </div>
                            </div>
                        </div>
                    </div>

                    ${medicine.description ? `
                        <div class="details-section">
                            <h6><i class="fa-solid fa-comment"></i> Description</h6>
                            <p>${medicine.description}</p>
                        </div>
                    ` : ''}
                `;

                document.getElementById('viewMedicineContent').innerHTML = content;
                document.getElementById('viewMedicineTitle').innerHTML = `
                    <i class="fa-solid fa-eye"></i>
                    ${medicine.medicine_name}
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('viewMedicineModal'));
                modal.show();
            }

            async editMedicine(medicineId) {
                try {
                    const response = await fetch(`./medicine_data/get_medicine.php?medicine_id=${medicineId}`);
                    const data = await response.json();

                    if (data.medicine) {
                        this.currentMedicine = data.medicine;
                        this.populateEditForm(data.medicine);
                        const modal = new bootstrap.Modal(document.getElementById('editMedicineModal'));
                        modal.show();
                    }
                } catch (error) {
                    console.error('Error loading medicine for edit:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load medicine details for editing.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            populateEditForm(medicine) {
                document.getElementById('editMedicineId').value = medicine.medicine_id;
                document.getElementById('editMedicineName').value = medicine.medicine_name;
                document.getElementById('editBrand').value = medicine.brand || '';
                document.getElementById('editGenericName').value = medicine.generic_name || '';
                document.getElementById('editDosageForm').value = medicine.dosage_form || '';
                document.getElementById('editStrength').value = medicine.strength || '';
                document.getElementById('editUnit').value = medicine.unit || '';
                document.getElementById('editStockQuantity').value = medicine.stock_quantity;
                document.getElementById('editMinimumStock').value = medicine.minimum_stock;
                document.getElementById('editUnitCost').value = medicine.unit_cost || '';
                document.getElementById('editExpiryDate').value = medicine.expiry_date || '';
                document.getElementById('editBatchNumber').value = medicine.batch_number || '';
                document.getElementById('editSupplier').value = medicine.supplier || '';
                document.getElementById('editDescription').value = medicine.description || '';
                document.getElementById('editStatus').value = medicine.status;
            }

            editMedicineFromView() {
                if (this.currentMedicine) {
                    // Close view modal
                    const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewMedicineModal'));
                    viewModal.hide();
                    
                    // Open edit modal
                    setTimeout(() => {
                        this.populateEditForm(this.currentMedicine);
                        const editModal = new bootstrap.Modal(document.getElementById('editMedicineModal'));
                        editModal.show();
                    }, 300);
                }
            }

            async deleteMedicine(medicineId) {
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (result.isConfirmed) {
                    try {
                        const formData = new FormData();
                        formData.append('medicine_id', medicineId);

                        const response = await fetch('../backend/admin/medicine_data/delete_medicine.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Medicine has been deleted.',
                                confirmButtonColor: '#28a745'
                            });
                            
                            await this.loadMedicines();
                            this.loadTableView();
                            this.updateStatistics();
                            this.updateChart();
                        } else {
                            throw new Error(data.message || 'Failed to delete medicine');
                        }
                    } catch (error) {
                        console.error('Error deleting medicine:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to delete medicine.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }
            }
        }

        async function editMedicineFromView() {
            medicineManager.editMedicineFromView();
            
        }

        // Global functions
        async function filterMedicines() {
            medicineManager.currentPage = 1;
            await medicineManager.loadMedicines();
            medicineManager.loadTableView();
        }

        function removeSpecialCharacters(input) {
            input.value = input.value.replace(/[^a-zA-Z0-9\s]/g, '');
        }

        async function addMedicine() {
            const form = document.getElementById('addMedicineForm');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData();
            formData.append('medicine_name', document.getElementById('addMedicineName').value);
            formData.append('brand', document.getElementById('addBrand').value);
            formData.append('generic_name', document.getElementById('addGenericName').value);
            formData.append('dosage_form', document.getElementById('addDosageForm').value);
            formData.append('strength', document.getElementById('addStrength').value);
            formData.append('unit', document.getElementById('addUnit').value);
            formData.append('stock_quantity', document.getElementById('addStockQuantity').value);
            formData.append('minimum_stock', document.getElementById('addMinimumStock').value);
            formData.append('unit_cost', document.getElementById('addUnitCost').value);
            formData.append('expiry_date', document.getElementById('addExpiryDate').value);
            formData.append('batch_number', document.getElementById('addBatchNumber').value);
            formData.append('supplier', document.getElementById('addSupplier').value);
            formData.append('description', document.getElementById('addDescription').value);

            try {
                const response = await fetch('../backend/admin/medicine_data/add_medicine.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Medicine added successfully.',
                        confirmButtonColor: '#28a745'
                    });
                    
                    form.reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addMedicineModal'));
                    modal.hide();
                    
                    await medicineManager.loadMedicines();
                    medicineManager.loadTableView();
                    medicineManager.updateStatistics();
                    medicineManager.updateChart();
                } else {
                    throw new Error(data.message || 'Failed to add medicine');
                }
            } catch (error) {
                console.error('Error adding medicine:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to add medicine.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        async function updateMedicine() {
            const form = document.getElementById('editMedicineForm');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData();
            formData.append('medicine_id', document.getElementById('editMedicineId').value);
            formData.append('medicine_name', document.getElementById('editMedicineName').value);
            formData.append('brand', document.getElementById('editBrand').value);
            formData.append('generic_name', document.getElementById('editGenericName').value);
            formData.append('dosage_form', document.getElementById('editDosageForm').value);
            formData.append('strength', document.getElementById('editStrength').value);
            formData.append('unit', document.getElementById('editUnit').value);
            formData.append('stock_quantity', document.getElementById('editStockQuantity').value);
            formData.append('minimum_stock', document.getElementById('editMinimumStock').value);
            formData.append('unit_cost', document.getElementById('editUnitCost').value);
            formData.append('expiry_date', document.getElementById('editExpiryDate').value);
            formData.append('batch_number', document.getElementById('editBatchNumber').value);
            formData.append('supplier', document.getElementById('editSupplier').value);
            formData.append('description', document.getElementById('editDescription').value);
            formData.append('status', document.getElementById('editStatus').value);

            try {
                const response = await fetch('../backend/admin/medicine_data/update_medicine.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Medicine updated successfully.',
                        confirmButtonColor: '#28a745'
                    });
                    
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editMedicineModal'));
                    modal.hide();
                    
                    await medicineManager.loadMedicines();
                    medicineManager.loadTableView();
                    medicineManager.updateStatistics();
                    medicineManager.updateChart();
                } else {
                    throw new Error(data.message || 'Failed to update medicine');
                }
            } catch (error) {
                console.error('Error updating medicine:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update medicine.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        async function exportMedicineInventory() {
            try {
                window.open('./medicine_data/export_medicine_inventory.php', '_blank');
            } catch (error) {
                console.error('Error exporting medicine inventory:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to export medicine inventory.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        // Initialize the medicine manager when the page loads
        let medicineManager;
        
        document.addEventListener('DOMContentLoaded', function() {
            medicineManager = new MedicineInventoryManager();
        });
    </script>
</body>
</html>