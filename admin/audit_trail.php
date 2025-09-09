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

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM tbl_audit_logs";
$count_result = mysqli_query($conn, $count_query);
$total_items = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_items / $items_per_page);

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/adminAnc.css">
    <link rel="stylesheet" href="../styles/general.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles/event_scheduler.css">

    <title>Audit Logs</title>
    <style>
        /* Additional styles for status badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .role-badge {
            background-color: #e3f2fd;
            color: #1565c0;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
            padding: 4px 12px;
        }

        .activity-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
            text-transform: uppercase;

            max-width: 350px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-grow: 1;
        }

        .activity-create {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .activity-update {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .activity-delete {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .activity-login {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #155724;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .log-details {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .filter-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .date-range-input {
            max-width: 200px;
        }

        .log-info-display {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            background-color: #f8f9fa;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #343a40;
        }

        .log-info-display strong {
            color: #6c757d;
            margin-right: 8px;
            font-weight: 500;
        }

        #viewLogContent {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fa-solid fa-clipboard-list"></i>
                Audit Logs
            </h1>
            <p class="dashboard-subtitle">
                Monitor user activities and system changes in real-time.
            </p>
        </div>

        <!-- Filter Section -->
        <div class="filter-container">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fa-solid fa-filter"></i>
                        Activity Type
                    </label>
                    <select class="form-control" id="activityFilter">
                        <option value="">All Activities</option>
                        <option value="login">Login</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="logout">Logout</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fa-solid fa-user-tag"></i>
                        Role
                    </label>
                    <select class="form-control" id="roleFilter">
                        <option value="">All Roles</option>
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">
                        <i class="fa-solid fa-calendar"></i>
                        From Date
                    </label>
                    <input type="date" class="form-control date-range-input" id="dateFromFilter">
                </div>
                <div class="col-md-2">
                    <label class="form-label">
                        <i class="fa-solid fa-calendar"></i>
                        To Date
                    </label>
                    <input type="date" class="form-control date-range-input" id="dateToFilter">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" onclick="clearFilters()">
                        <i class="fa-solid fa-refresh"></i>
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <div id="tableView" class="table-container">
            <div class="table-header">
                <div class="table-actions">
                    <div class="d-flex align-items-center gap-3">
                        <h3 class="mb-0">Activity Logs</h3>
                        <span class="badge bg-primary" id="totalLogsCount"><?php echo $total_items; ?> Logs</span>
                    </div>
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search logs...">
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="announcements-table" id="logsTable">
                    <thead>
                        <tr>
                            <th>LOG ID</th>
                            <th>Avatar</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Activity</th>
                            <th>Date & Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        <!-- Content will be loaded dynamically -->
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="showing-info" id="showingInfo">
                    Showing 0 to 0 of 0 entries
                </div>
                <div class="pagination" id="paginationContainer">
                    <!-- Pagination will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- View Log Details Modal -->
    <div class="modal fade" id="viewLogModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-eye"></i>
                        Log Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewLogContent">
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
        class AuditLogManager {
            constructor() {
                this.currentPage = 1;
                this.itemsPerPage = 10;
                this.totalLogs = 0;
                this.logs = [];
                this.roles = [];
                this.init();
            }

            async init() {
                this.setupEventListeners();
                await this.loadLogs();
                this.loadTableView();
            }

            setupEventListeners() {
                document.getElementById('searchInput').addEventListener('input',
                    this.debounce((e) => this.searchLogs(e.target.value), 300)
                );

                document.getElementById('activityFilter').addEventListener('change', () => this.filterLogs());
                document.getElementById('roleFilter').addEventListener('change', () => this.filterLogs());
                document.getElementById('dateFromFilter').addEventListener('change', () => this.filterLogs());
                document.getElementById('dateToFilter').addEventListener('change', () => this.filterLogs());
            }

            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
            updateActivityFilter() {
                const activityFilter = document.getElementById('activityFilter');
                const currentValue = activityFilter.value;

                // Clear existing options except "All Activities"
                activityFilter.innerHTML = '<option value="">All Activities</option>';

                // Add activity options from backend
                if (this.activityTypes && this.activityTypes.length > 0) {
                    this.activityTypes.forEach(activity => {
                        const option = document.createElement('option');
                        option.value = activity.value;
                        option.textContent = `${activity.label} (${activity.count})`;
                        if (activity.value === currentValue) {
                            option.selected = true;
                        }
                        activityFilter.appendChild(option);
                    });
                }
            }

            async loadLogs() {
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.itemsPerPage,
                        search: document.getElementById('searchInput')?.value || '',
                        activity_type: document.getElementById('activityFilter')?.value || '',
                        role_id: document.getElementById('roleFilter')?.value || '',
                        date_from: document.getElementById('dateFromFilter')?.value || '',
                        date_to: document.getElementById('dateToFilter')?.value || ''
                    });

                    const response = await fetch(`./audit_log/get_logs.php?${params}`);
                    const data = await response.json();

                    this.logs = data.log_data || [];
                    this.roles = data.roles || [];
                    this.activityTypes = data.activity_types || []; // Add this line
                    this.totalLogs = data.total || 0;

                    // Update total logs display
                    document.getElementById('totalLogsCount').textContent = `${this.totalLogs} Logs`;

                    this.updateRoleFilter();
                    this.updateActivityFilter(); // Add this line
                } catch (error) {
                    console.error('Error loading logs:', error);
                    this.showError('Failed to load audit logs');
                }
            }

            updateRoleFilter() {
                const roleFilter = document.getElementById('roleFilter');
                const currentValue = roleFilter.value;

                // Clear existing options except "All Roles"
                roleFilter.innerHTML = '<option value="">All Roles</option>';

                // Add role options
                this.roles.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.role_id;
                    option.textContent = role.role_name;
                    if (role.role_id == currentValue) {
                        option.selected = true;
                    }
                    roleFilter.appendChild(option);
                });
            }

            loadTableView() {
                const tbody = document.getElementById('logsTableBody');

                if (this.logs.length === 0) {
                    tbody.innerHTML = `
                        <tr class="no-data">
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                    <h3>No audit logs found</h3>
                                    <p>No activities match your current filters</p>
                                </div>
                            </td>
                        </tr>`;
                    this.updatePagination();
                    this.updateShowingInfo();
                    return;
                }

                let html = '';
                this.logs.forEach(log => {
                    const logDate = new Date(log.log_date);
                    const formattedDate = logDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });

                    // Create avatar with initials
                    const initials = log.full_name ?
                        log.full_name.split(' ').map(name => name[0]).join('').substring(0, 2).toUpperCase() :
                        log.username ? log.username.substring(0, 2).toUpperCase() : 'NA';

                    // Get activity badge class
                    const activityClass = this.getActivityBadgeClass(log.activity_type);

                    html += `
                        <tr data-log-id="${log.log_id}">
                            <td class="id-cell">#${log.log_id.toString().padStart(4, '0')}</td>
                            <td class="avatar-cell">
                                <div class="user-avatar">${initials}</div>
                            </td>
                            <td class="name-cell">
                                <div class="name-content">
                                    <span class="name">${log.full_name || 'Unknown User'}</span>
                                    <small class="text-muted d-block">@${log.username || 'N/A'}</small>
                                </div>
                            </td>
                            <td class="role-cell">
                                <span class="role-badge">
                                    ${log.role_name || 'No Role'}
                                </span>
                            </td>
                            <td class="activity-cell">
                                <span class="activity-badge ${activityClass}">
                                    ${log.activity_type || 'N/A'}
                                </span>
                            </td>
                            <td class="date-cell">
                                <div class="date-info">
                                    <span class="date">${formattedDate}</span>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" title="View Details" onclick="viewLogDetails(${log.log_id})">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });

                tbody.innerHTML = html;
                this.updatePagination();
                this.updateShowingInfo();
            }

            getActivityBadgeClass(activityType) {
                const type = activityType ? activityType.toLowerCase() : '';
                switch (type) {
                    case 'create':
                    case 'add':
                        return 'activity-create';
                    case 'update':
                    case 'edit':
                        return 'activity-update';
                    case 'delete':
                    case 'remove':
                        return 'activity-delete';
                    case 'login':
                        return 'activity-login';
                    default:
                        return 'activity-create';
                }
            }

            updatePagination() {
                const totalPages = Math.ceil(this.totalLogs / this.itemsPerPage);
                const container = document.getElementById('paginationContainer');

                if (totalPages <= 1) {
                    container.innerHTML = `
                        <button class="btn-pagination" disabled>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span class="page-number active">1</span>
                        <button class="btn-pagination" disabled>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>`;
                    return;
                }

                let html = `
                    <button class="btn-pagination" ${this.currentPage <= 1 ? 'disabled' : ''} 
                            onclick="auditLogManager.changePage(${this.currentPage - 1})">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>`;

                // Page numbers
                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);

                if (startPage > 1) {
                    html += `<span class="page-number" onclick="auditLogManager.changePage(1)">1</span>`;
                    if (startPage > 2) {
                        html += `<span class="page-ellipsis">...</span>`;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                             onclick="auditLogManager.changePage(${i})">${i}</span>`;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        html += `<span class="page-ellipsis">...</span>`;
                    }
                    html += `<span class="page-number" onclick="auditLogManager.changePage(${totalPages})">${totalPages}</span>`;
                }

                html += `
                    <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                            onclick="auditLogManager.changePage(${this.currentPage + 1})">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>`;

                container.innerHTML = html;
            }

            updateShowingInfo() {
                const startItem = this.totalLogs > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
                const endItem = Math.min(this.currentPage * this.itemsPerPage, this.totalLogs);
                document.getElementById('showingInfo').textContent =
                    `Showing ${startItem} to ${endItem} of ${this.totalLogs} entries`;
            }

            async changePage(page) {
                const totalPages = Math.ceil(this.totalLogs / this.itemsPerPage);
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    await this.loadLogs();
                    this.loadTableView();
                }
            }

            async searchLogs(query) {
                this.currentPage = 1;
                await this.loadLogs();
                this.loadTableView();
            }

            async filterLogs() {
                this.currentPage = 1;
                await this.loadLogs();
                this.loadTableView();
            }

            showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            }
        }

        // Initialize audit log manager
        let auditLogManager;
        document.addEventListener('DOMContentLoaded', function() {
            auditLogManager = new AuditLogManager();
        });

        // Global functions
        async function viewLogDetails(logId) {
            const log = auditLogManager.logs.find(l => l.log_id == logId);
            if (!log) return;

            const logDate = new Date(log.log_date);
            const formattedDate = logDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZoneName: 'short'
            });

            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-hashtag"></i> Log ID</h6>
                        <p class="mb-3 log-info-display">#${log.log_id.toString().padStart(4, '0')}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-user"></i> User</h6>
                        <p class="mb-3 log-info-display">${log.full_name || 'Unknown User'} (@${log.username || 'N/A'})</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-user-tag"></i> Role</h6>
                        <p class="mb-3 log-info-display">${log.role_name || 'No Role'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-bolt"></i> Activity Type</h6>
                        <p class="mb-3 log-info-display">
                            <span class="activity-badge ${auditLogManager.getActivityBadgeClass(log.activity_type)}">
                                ${log.activity_type || 'N/A'}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-calendar-clock"></i> Date & Time</h6>
                        <p class="mb-3 log-info-display">${formattedDate}</p>
                    </div>
                </div>`;

            document.getElementById('viewLogContent').innerHTML = content;
            const modal = new bootstrap.Modal(document.getElementById('viewLogModal'));
            modal.show();
        }

        function clearFilters() {
            document.getElementById('activityFilter').value = '';
            document.getElementById('roleFilter').value = '';
            document.getElementById('dateFromFilter').value = '';
            document.getElementById('dateToFilter').value = '';
            document.getElementById('searchInput').value = '';

            auditLogManager.filterLogs();
        }

        // Export functionality (optional)
        function exportLogs() {
            const params = new URLSearchParams({
                export: 'csv',
                search: document.getElementById('searchInput')?.value || '',
                activity_type: document.getElementById('activityFilter')?.value || '',
                role_id: document.getElementById('roleFilter')?.value || '',
                date_from: document.getElementById('dateFromFilter')?.value || '',
                date_to: document.getElementById('dateToFilter')?.value || ''
            });

            window.open(`./audit_log/export_logs.php?${params}`, '_blank');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-Ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
</body>

</html>