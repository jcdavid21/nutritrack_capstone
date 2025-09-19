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

    <title>Medicine Log Analytics</title>
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

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .chart-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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

        .status-resolved {
            background-color: var(--primary-blue);
            color: white;
        }

        .status-under-review {
            background-color: var(--warning-orange);
            color: white;
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

        .modal-header {
            background: var(--primary-red);
            border-radius: 12px 12px 0 0;
        }

        .modal-header .modal-title {
            color: white;
        }

        .flagged-type-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 5px;
        }

        .type-underweight {
            background-color: #fff3cd;
            color: #856404;
        }

        .type-overweight {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .type-severely-underweight {
            background-color: #f8d7da;
            color: #721c24;
        }

        .type-incomplete-vaccination {
            background-color: #e2e3e5;
            color: #41464b;
        }

        .type-growth-concern {
            background-color: #d4edda;
            color: #155724;
        }

        .type-behavioral-issues {
            background-color: #ffeaa7;
            color: #856404;
        }

        .type-medical-concern {
            background-color: #fdcb6e;
            color: #856404;
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

        .clickable-chart {
            cursor: pointer;
        }

        .chart-legend {
            margin-top: 15px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            margin-right: 20px;
            margin-bottom: 10px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 8px;
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

        .date-range-picker {
            display: flex;
            gap: 10px;
            align-items: center;
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
    </style>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="medicine-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fa-solid fa-chart-line"></i>
                        Medicine Log Analytics
                    </h1>
                    <p class="mb-0 opacity-90">Track medicine administration patterns and usage analytics for flagged children</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-primary" id="totalLogsCount">0</div>
                            <div class="stat-label">Total Medicine Logs</div>
                        </div>
                        <i class="fa-solid fa-clipboard-list text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-success" id="uniqueChildrenCount">0</div>
                            <div class="stat-label">Children Treated</div>
                        </div>
                        <i class="fa-solid fa-child text-success fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-warning" id="uniqueMedicinesCount">0</div>
                            <div class="stat-label">Different Medicines</div>
                        </div>
                        <i class="fa-solid fa-pills text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-number text-info" id="thisMonthCount">0</div>
                            <div class="stat-label">This Month</div>
                        </div>
                        <i class="fa-solid fa-calendar-day text-info fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3">
                <i class="fa-solid fa-filter"></i>
                Filter Analytics
            </h5>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <div class="date-range-picker">
                        <input type="date" class="form-control form-control-sm" id="startDate">
                        <span>to</span>
                        <input type="date" class="form-control form-control-sm" id="endDate">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Flagged Type</label>
                    <select class="form-select form-select-sm" id="flaggedTypeFilter">
                        <option value="">All Types</option>
                        <option value="1">Underweight</option>
                        <option value="2">Overweight</option>
                        <option value="3">Severely Underweight</option>
                        <option value="4">Incomplete Vaccination</option>
                        <option value="5">Growth Concern</option>
                        <option value="6">Behavioral Issues</option>
                        <option value="7">Medical Concern</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Medicine</label>
                    <select class="form-select form-select-sm" id="medicineFilter">
                        <option value="">All Medicines</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
                            <i class="fa-solid fa-refresh"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-section">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-chart-pie"></i>
                        Most Used Medicines
                    </h5>
                    <div class="chart-container">
                        <canvas id="medicineUsageChart" class="clickable-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-section">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-chart-bar"></i>
                        Medicine Usage by Flagged Type
                    </h5>
                    <div class="chart-container">
                        <canvas id="flaggedTypeChart" class="clickable-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>


        <!-- Recent Medicine Logs Table -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <h3 class="mb-0">Recent Medicine Logs</h3>
                <span class="badge bg-secondary" id="totalLogsBadge">0 Logs</span>
            </div>
        </div>

        <div class="table-container">
            <div class="table-wrapper">
                <table class="medicine-table" id="medicineLogTable">
                    <thead>
                        <tr>
                            <th>Child</th>
                            <th>Medicine</th>
                            <th>Quantity</th>
                            <th>Flagged Issue</th>
                            <th>Administered By</th>
                            <th>Date Administered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="medicineLogTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Medicine Usage Details Modal -->
    <div class="modal fade" id="medicineUsageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="medicineUsageModalTitle">
                        <i class="fa-solid fa-pills"></i>
                        Medicine Usage Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="medicineUsageContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        class MedicineLogAnalytics {
            constructor() {
                this.medicineUsageChart = null;
                this.flaggedTypeChart = null;
                this.currentFilters = {
                    startDate: '',
                    endDate: '',
                    flaggedType: '',
                    medicine: ''
                };
                this.init();
            }

            async init() {
                this.setupEventListeners();
                await this.loadInitialData();
                this.initializeCharts();
                await this.loadStatistics();
                await this.loadRecentLogs();
            }

            // Replace the setupEventListeners method in your MedicineLogAnalytics class
            setupEventListeners() {
                // Set default date range (last 30 days)
                const today = new Date();
                const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);

                document.getElementById('endDate').value = today.toISOString().split('T')[0];
                document.getElementById('startDate').value = thirtyDaysAgo.toISOString().split('T')[0];

                // Add event listeners for auto-filtering
                document.getElementById('startDate').addEventListener('change', () => this.applyFilters());
                document.getElementById('endDate').addEventListener('change', () => this.applyFilters());
                document.getElementById('flaggedTypeFilter').addEventListener('change', () => this.applyFilters());
                document.getElementById('medicineFilter').addEventListener('change', () => this.applyFilters());
            }

            async loadInitialData() {
                try {
                    // Load medicines for filter dropdown
                    const medicineResponse = await fetch('./medicine_logs/get_medicines_simple.php');
                    const medicineData = await medicineResponse.json();

                    const medicineSelect = document.getElementById('medicineFilter');

                    medicineData.medicines?.forEach(medicine => {
                        const option1 = new Option(medicine.medicine_name, medicine.medicine_id);
                        medicineSelect.add(option1);
                    });


                } catch (error) {
                    console.error('Error loading initial data:', error);
                }
            }

            async initializeCharts() {
                await this.initMedicineUsageChart();
                await this.initFlaggedTypeChart();
            }

            async initMedicineUsageChart() {
                const ctx = document.getElementById('medicineUsageChart');

                try {
                    const response = await fetch('./medicine_logs/get_medicine_usage_chart.php?' + this.buildQueryString());
                    const data = await response.json();

                    const colors = [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                    ];

                    this.medicineUsageChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels || [],
                            datasets: [{
                                data: data.values || [],
                                backgroundColor: colors.slice(0, data.labels?.length || 0),
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
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return `${label}: ${value} uses (${percentage}%)`;
                                        }
                                    }
                                }
                            },
                            onClick: (event, elements) => {
                                if (elements.length > 0) {
                                    const index = elements[0].index;
                                    const medicineName = this.medicineUsageChart.data.labels[index];
                                    this.showMedicineUsageDetails(medicineName);
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error initializing medicine usage chart:', error);
                }
            }

            async initFlaggedTypeChart() {
                const ctx = document.getElementById('flaggedTypeChart');

                try {
                    const response = await fetch('./medicine_logs/get_flagged_type_chart.php?' + this.buildQueryString());
                    const data = await response.json();

                    const colors = [
                        '#ff7675', '#74b9ff', '#fd79a8', '#fdcb6e', '#55a3ff',
                        '#e17055', '#00b894', '#a29bfe', '#6c5ce7', '#ffeaa7'
                    ];

                    this.flaggedTypeChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels || [],
                            datasets: [{
                                label: 'Medicine Administrations',
                                data: data.values || [],
                                backgroundColor: colors.slice(0, data.labels?.length || 0),
                                borderColor: colors.slice(0, data.labels?.length || 0),
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.label}: ${context.parsed.y} administrations`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            },
                            onClick: (event, elements) => {
                                if (elements.length > 0) {
                                    const index = elements[0].index;
                                    const flaggedType = this.flaggedTypeChart.data.labels[index];
                                    this.showFlaggedTypeDetails(flaggedType);
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error initializing flagged type chart:', error);
                }
            }


            buildQueryString() {
                const params = new URLSearchParams();
                if (this.currentFilters.startDate) params.append('start_date', this.currentFilters.startDate);
                if (this.currentFilters.endDate) params.append('end_date', this.currentFilters.endDate);
                if (this.currentFilters.flaggedType) params.append('flagged_type', this.currentFilters.flaggedType);
                if (this.currentFilters.medicine) params.append('medicine', this.currentFilters.medicine);
                return params.toString();
            }

            async loadStatistics() {
                try {
                    const response = await fetch('./medicine_logs/get_medicine_log_statistics.php?' + this.buildQueryString());
                    const stats = await response.json();

                    document.getElementById('totalLogsCount').textContent = stats.total_logs || 0;
                    document.getElementById('uniqueChildrenCount').textContent = stats.unique_children || 0;
                    document.getElementById('uniqueMedicinesCount').textContent = stats.unique_medicines || 0;
                    document.getElementById('thisMonthCount').textContent = stats.this_month || 0;
                } catch (error) {
                    console.error('Error loading statistics:', error);
                }
            }

            async loadRecentLogs() {
                try {
                    const response = await fetch('./medicine_logs/get_recent_medicine_logs.php?' + this.buildQueryString());
                    const data = await response.json();

                    this.displayRecentLogs(data.logs || []);
                    document.getElementById('totalLogsBadge').textContent = `${data.total || 0} Logs`;
                } catch (error) {
                    console.error('Error loading recent logs:', error);
                }
            }

            displayRecentLogs(logs) {
                const tbody = document.getElementById('medicineLogTableBody');
                let html = '';

                if (logs.length === 0) {
                    html = `<tr class="no-data">
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fa-solid fa-clipboard-list"></i>
                                <h3>No medicine logs found</h3>
                                <p>No medicine logs match your current filters</p>
                            </div>
                        </td>
                    </tr>`;
                } else {
                    logs.forEach(log => {
                        const flaggedTypeBadge = this.getFlaggedTypeBadge(log.flagged_type);
                        const initials = log.child_name ? log.child_name.split(' ').map(n => n[0]).join('').toUpperCase() : 'N/A';

                        html += `<tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="medicine-icon me-3">${initials}</div>
                                    <div>
                                        <div class="fw-bold">${log.child_name || 'Unknown Child'}</div>
                                        <small class="text-muted">ID: ${log.child_id}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold">${log.medicine_name}</div>
                                <small class="text-muted">${log.brand || log.generic_name || 'No brand'}</small>
                            </td>
                            <td>
                                <span class="fw-bold">${log.quantity_given}</span>
                                <small class="text-muted d-block">${log.unit || ''}</small>
                            </td>
                            <td>
                                ${log.flagged_type ? flaggedTypeBadge : '<span class="text-muted">No flagged record</span>'}
                            </td>
                            <td>
                                <div>${log.administered_by_name || 'System'}</div>
                                <small class="text-muted">${log.administered_by_role || ''}</small>
                            </td>
                            <td>
                                <div class="fw-medium">${this.formatDateTime(log.date_administered)}</div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="medicineLogAnalytics.viewLogDetails(${log.log_id})" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                }

                tbody.innerHTML = html;
            }

            getFlaggedTypeBadge(flaggedType) {
                const types = {
                    'Underweight': {
                        class: 'type-underweight',
                        text: 'Underweight'
                    },
                    'Overweight': {
                        class: 'type-overweight',
                        text: 'Overweight'
                    },
                    'Severely Underweight': {
                        class: 'type-severely-underweight',
                        text: 'Severely Underweight'
                    },
                    'Incomplete Vaccination': {
                        class: 'type-incomplete-vaccination',
                        text: 'Incomplete Vaccination'
                    },
                    'Growth Concern': {
                        class: 'type-growth-concern',
                        text: 'Growth Concern'
                    },
                    'Behavioral Issues': {
                        class: 'type-behavioral-issues',
                        text: 'Behavioral Issues'
                    },
                    'Medical Concern': {
                        class: 'type-medical-concern',
                        text: 'Medical Concern'
                    }
                };

                const typeInfo = types[flaggedType] || {
                    class: 'type-medical-concern',
                    text: flaggedType
                };
                return `<span class="flagged-type-badge ${typeInfo.class}">${typeInfo.text}</span>`;
            }

            formatDateTime(dateString) {
                if (!dateString) return 'Not specified';
                const date = new Date(dateString);
                return date.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            async showMedicineUsageDetails(medicineName) {
                try {
                    const response = await fetch(`./medicine_logs/get_medicine_usage_details.php?medicine=${encodeURIComponent(medicineName)}&${this.buildQueryString()}`);
                    const data = await response.json();

                    this.displayUsageModal(data.details || [], medicineName, 'Medicine Usage');
                } catch (error) {
                    console.error('Error loading medicine usage details:', error);
                    this.showErrorModal('Failed to load medicine usage details.');
                }
            }

            async showFlaggedTypeDetails(flaggedType) {
                try {
                    const response = await fetch(`./medicine_logs/get_flagged_type_details.php?flagged_type=${encodeURIComponent(flaggedType)}&${this.buildQueryString()}`);
                    const data = await response.json();

                    this.displayUsageModal(data.details || [], flaggedType, 'Flagged Type Usage');
                } catch (error) {
                    console.error('Error loading flagged type details:', error);
                    this.showErrorModal('Failed to load flagged type details.');
                }
            }

            displayUsageModal(details, title, type) {
                const modalTitle = document.getElementById('medicineUsageModalTitle');
                const modalContent = document.getElementById('medicineUsageContent');

                modalTitle.innerHTML = `<i class="fa-solid fa-chart-pie"></i> ${title} - ${type}`;

                let html = '';
                if (details.length === 0) {
                    html = `
                        <div class="empty-state">
                            <i class="fa-solid fa-info-circle"></i>
                            <h4>No details available</h4>
                            <p>No usage details found for the selected item.</p>
                        </div>
                    `;
                } else {
                    html = `
                        <div class="mb-3">
                            <h6><i class="fa-solid fa-users"></i> Children Using This ${type === 'Medicine Usage' ? 'Medicine' : 'With This Issue'} (${details.length})</h6>
                        </div>
                        <div class="row">
                    `;

                    details.forEach(detail => {
                        const flaggedTypeBadge = this.getFlaggedTypeBadge(detail.flagged_type);

                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="medicine-icon me-3">
                                                ${detail.child_name ? detail.child_name.split(' ').map(n => n[0]).join('').toUpperCase() : 'N/A'}
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-2">${detail.child_name || 'Unknown Child'}</h6>
                                                
                                                <div class="info-display">
                                                    <strong>Medicine:</strong> ${detail.medicine_name}
                                                </div>
                                                
                                                <div class="info-display">
                                                    <strong>Quantity Given:</strong> ${detail.quantity_given} ${detail.unit || ''}
                                                </div>
                                                
                                                <div class="info-display">
                                                    <strong>Issue Type:</strong> ${detail.flagged_type ? flaggedTypeBadge : 'No flagged record'}
                                                </div>
                                                
                                                <div class="info-display">
                                                    <strong>Date:</strong> ${this.formatDateTime(detail.date_administered)}
                                                </div>
                                                
                                                <div class="info-display">
                                                    <strong>Administered By:</strong> ${detail.administered_by_name || 'System'}
                                                </div>
                                                
                                                ${detail.notes ? `
                                                    <div class="info-display">
                                                        <strong>Notes:</strong> ${detail.notes}
                                                    </div>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                }

                modalContent.innerHTML = html;

                const modal = new bootstrap.Modal(document.getElementById('medicineUsageModal'));
                modal.show();
            }

            showErrorModal(message) {
                const modalContent = document.getElementById('medicineUsageContent');
                modalContent.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                `;

                const modal = new bootstrap.Modal(document.getElementById('medicineUsageModal'));
                modal.show();
            }

            async applyFilters() {
                this.currentFilters = {
                    startDate: document.getElementById('startDate').value,
                    endDate: document.getElementById('endDate').value,
                    flaggedType: document.getElementById('flaggedTypeFilter').value,
                    medicine: document.getElementById('medicineFilter').value
                };

                await this.refreshData();
            }

            async resetFilters() {
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('flaggedTypeFilter').value = '';
                document.getElementById('medicineFilter').value = '';

                this.currentFilters = {
                    startDate: '',
                    endDate: '',
                    flaggedType: '',
                    medicine: ''
                };

                await this.refreshData();
            }

            async refreshData() {
                await this.loadStatistics();
                await this.loadRecentLogs();

                if (this.medicineUsageChart) {
                    this.medicineUsageChart.destroy();
                    await this.initMedicineUsageChart();
                }

                if (this.flaggedTypeChart) {
                    this.flaggedTypeChart.destroy();
                    await this.initFlaggedTypeChart();
                }

            }

            async viewLogDetails(logId) {
                try {
                    const response = await fetch(`./medicine_logs/get_medicine_log_detail.php?log_id=${logId}`);
                    const data = await response.json();

                    if (data.log) {
                        this.displayLogDetailModal(data.log);
                    } else {
                        throw new Error('Log not found');
                    }
                } catch (error) {
                    console.error('Error loading log details:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load log details.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }

            displayLogDetailModal(log) {
                const flaggedTypeBadge = this.getFlaggedTypeBadge(log.flagged_type);

                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fa-solid fa-child"></i> Child Information</h6>
                            <div class="info-display">
                                <strong>Name:</strong> ${log.child_name || 'Unknown Child'}
                            </div>
                            <div class="info-display">
                                <strong>Age:</strong> ${log.child_age ? log.child_age + ' years old' : 'Not specified'}
                            </div>
                            <div class="info-display">
                                <strong>Gender:</strong> ${log.child_gender || 'Not specified'}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fa-solid fa-pills"></i> Medicine Information</h6>
                            <div class="info-display">
                                <strong>Medicine:</strong> ${log.medicine_name}
                            </div>
                            <div class="info-display">
                                <strong>Brand:</strong> ${log.brand || log.generic_name || 'Not specified'}
                            </div>
                            <div class="info-display">
                                <strong>Form & Strength:</strong> ${log.dosage_form || 'N/A'} ${log.strength ? '- ' + log.strength : ''}
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6><i class="fa-solid fa-prescription-bottle-medical"></i> Administration Details</h6>
                            <div class="info-display">
                                <strong>Quantity Given:</strong> ${log.quantity_given} ${log.unit || ''}
                            </div>
                            <div class="info-display">
                                <strong>Frequency:</strong> ${log.frequency || 'Not specified'}
                            </div>
                            <div class="info-display">
                                <strong>Duration:</strong> ${log.duration || 'Not specified'}
                            </div>
                            <div class="info-display">
                                <strong>Date Administered:</strong> ${this.formatDateTime(log.date_administered)}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fa-solid fa-flag"></i> Flagged Record Information</h6>
                            <div class="info-display">
                                <strong>Issue Type:</strong> ${log.flagged_type ? flaggedTypeBadge : 'No flagged record'}
                            </div>
                            <div class="info-display">
                                <strong>Administered By:</strong> ${log.administered_by_name || 'System'}
                            </div>
                            <div class="info-display">
                                <strong>Role:</strong> ${log.administered_by_role || 'Not specified'}
                            </div>
                        </div>
                    </div>

                    ${log.dosage_instructions || log.notes ? `
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><i class="fa-solid fa-comment"></i> Additional Information</h6>
                                ${log.dosage_instructions ? `
                                    <div class="info-display">
                                        <strong>Dosage Instructions:</strong> ${log.dosage_instructions}
                                    </div>
                                ` : ''}
                                ${log.notes ? `
                                    <div class="info-display">
                                        <strong>Notes:</strong> ${log.notes}
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    ` : ''}
                `;

                document.getElementById('medicineUsageModalTitle').innerHTML = `
                    <i class="fa-solid fa-clipboard-list"></i>
                    Medicine Log Details - ${log.medicine_name}
                `;
                document.getElementById('medicineUsageContent').innerHTML = content;

                const modal = new bootstrap.Modal(document.getElementById('medicineUsageModal'));
                modal.show();
            }
        }

        // Global functions
        async function applyFilters() {
            await medicineLogAnalytics.applyFilters();
        }

        async function resetFilters() {
            await medicineLogAnalytics.resetFilters();
        }

        async function exportMedicineLog() {
            try {
                const queryString = medicineLogAnalytics.buildQueryString();
                window.open(`./medicine_logs/export_medicine_log.php?${queryString}`, '_blank');
            } catch (error) {
                console.error('Error exporting medicine log:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to export medicine log report.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        // Initialize the medicine log analytics when the page loads
        let medicineLogAnalytics;

        document.addEventListener('DOMContentLoaded', function() {
            medicineLogAnalytics = new MedicineLogAnalytics();

        });
    </script>
</body>

</html>