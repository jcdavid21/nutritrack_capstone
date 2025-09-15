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
$items_per_page = 2;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM tbl_user";
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles/event_scheduler.css">

    <title>User Management</title>
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
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
            color: #1565c0;
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

        .btn-activate{
            background-color: #28a745;
            color: white;
        }
    </style>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fa-solid fa-users"></i>
                User Management
            </h1>
            <p class="dashboard-subtitle">
                Manage and monitor registered users in the system
            </p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="view-toggle">
                <!-- View toggle buttons can be added here if needed -->
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fa-solid fa-plus"></i>
                Add New User
            </button>
        </div>

        <div id="tableView" class="table-container">
            <div class="table-header">
                <div class="table-actions">
                    <div class="d-flex align-items-center gap-3">
                        <h3 class="mb-0">Users List</h3>
                        <span class="badge bg-secondary" id="totalUsersCount"><?php echo $total_items; ?> Users</span>
                    </div>
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search users...">
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="announcements-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Avatar</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">

                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="showing-info" id="showingInfo">
                    Showing 0 to 0 of 0 entries
                </div>
                <div class="pagination" id="paginationContainer">

                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addUserModalLabel">
                        <i class="fa-solid fa-plus"></i>
                        Create New User
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="content-input mb-3">
                                    <label for="addUserFullName" class="form-label">
                                        <i class="fa-solid fa-user"></i>
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="addUserFullName" name="full_name" placeholder="Enter full name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="content-input mb-3">
                                    <label for="addUserContact" class="form-label">
                                        <i class="fa-solid fa-phone"></i>
                                        Contact Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="addUserContact" name="contact" placeholder="Enter contact number" required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="content-input mb-3">
                                    <label for="addUserRole" class="form-label">
                                        <i class="fa-solid fa-user-tag"></i>
                                        Role <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="addUserRole" name="role_id" required>
                                        <option value="">Select Role</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="content-input mb-3">
                                    <label for="addUserStatus" class="form-label">
                                        <i class="fa-solid fa-toggle-on"></i>
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="addUserStatus" name="status" required>
                                        <option value="" disabled selected>Select Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="content-input mb-3">
                                    <label for="addUserUsername" class="form-label">
                                        <i class="fa-solid fa-at"></i>
                                        Username <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="addUserUsername" name="username" placeholder="Enter username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="content-input mb-3">
                                    <label for="addPassword" class="form-label">
                                        <i class="fa-solid fa-lock"></i>
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="addUserPassword" name="password" placeholder="Enter password" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="content-input mb-3">
                                    <label for="addUserAddress" class="form-label">
                                        <i class="fa-solid fa-map-marker-alt"></i>
                                        Address <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="addUserAddress" name="address" rows="3" placeholder="Enter address..." required></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addUserBtn" onclick="addUser()">
                        <i class="fa-solid fa-save"></i>
                        Create User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        class UserManager {
            constructor() {
                this.currentPage = 1;
                this.itemsPerPage = 5;
                this.totalUsers = 0;
                this.users = [];
                this.roles = [];
                this.init();
            }

            async init() {
                this.setupEventListeners();
                await this.loadUsers();
                this.loadTableView();
            }

            setupEventListeners() {
                document.getElementById('searchInput').addEventListener('input', (e) => this.searchUsers(e.target.value));
            }

            async loadUsers() {
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.itemsPerPage,
                        search: document.getElementById('searchInput')?.value || ''
                    });

                    const response = await fetch(`./get_data/get_users.php?${params}`);
                    const data = await response.json();
                    this.users = data.user_data || [];
                    this.roles = data.roles || [];
                    this.totalUsers = data.total || 0;

                    // Update total users display
                    document.getElementById('totalUsersCount').textContent = `${this.totalUsers} Users`;

                    // Update role dropdowns
                    this.updateRoleDropdowns();
                } catch (error) {
                    console.error('Error loading users:', error);
                }
            }

            updateRoleDropdowns() {
                const addRoleSelect = document.getElementById('addUserRole');
                if (addRoleSelect) {
                    // Clear existing options except the first one
                    addRoleSelect.innerHTML = '<option value="" disabled selected>Select Role</option>';

                    // Add role options
                    this.roles.forEach(role => {
                        const option = document.createElement('option');
                        option.value = role.role_id;
                        option.textContent = role.role_name;
                        addRoleSelect.appendChild(option);
                    });
                }
            }

            loadTableView() {
                const tbody = document.getElementById('usersTableBody');

                let html = '';
                if (this.users.length === 0) {
                    html = `<tr class="no-data">
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="fa-solid fa-users"></i>
                                <h3>No users found</h3>
                                <p>Start by creating your first user</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fa-solid fa-plus"></i>
                                    Create User
                                </button>
                            </div>
                        </td>
                    </tr>`;
                } else {
                    this.users.forEach(user => {
                        const dateAdded = new Date(user.date_added);
                        const formattedDate = dateAdded.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        // Create avatar with initials
                        const initials = user.full_name ? user.full_name.split(' ').map(name => name[0]).join('').substring(0, 2).toUpperCase() : 'U';

                        const statusClass = user.status.toLowerCase() === 'active' ? 'status-active' : 'status-inactive';

                        html += `<tr data-user-id="${user.user_id}">
                            <td class="id-cell">#${user.user_id.toString().padStart(3, '0')}</td>
                            <td class="avatar-cell">
                                <div class="user-avatar">${initials}</div>
                            </td>
                            <td class="name-cell">
                                <div class="name-content">
                                    <span class="name">${user.full_name || 'N/A'}</span>
                                </div>
                            </td>
                            <td class="username-cell">
                                <span class="username">${user.username}</span>
                            </td>
                            <td class="contact-cell">
                                <span class="contact">${user.contact || 'N/A'}</span>
                            </td>
                            <td class="address-cell">
                                <div class="address-preview" title="${user.address || 'N/A'}">
                                    ${user.address ? (user.address.length > 30 ? user.address.substring(0, 30) + '...' : user.address) : 'N/A'}
                                </div>
                            </td>
                            <td class="role-cell">
                                <span class="role-badge">
                                    ${user.role_name || 'No Role'}
                                </span>
                            </td>
                            <td class="status-cell">
                                <span class="status-badge ${statusClass}">
                                    ${user.status ? user.status.charAt(0).toUpperCase() + user.status.slice(1) : 'Unknown'}
                                </span>
                            </td>
                            <td class="date-cell">
                                <div class="date-info">
                                    <span class="date">${formattedDate}</span>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" title="Edit" onclick="editUser(${user.user_id})">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" title="Delete" onclick="deleteUser(${user.user_id})">
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </button>
                                    ${user.status.toLowerCase() === 'inactive' ? `
                                        <button class="btn-action btn-activate" title="Activate" onclick="activateUser(${user.user_id})">
                                            <i class="fa-solid fa-arrow-up"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>`;
                    });
                }

                tbody.innerHTML = html;
                this.updatePagination();
                this.updateShowingInfo();
            }

            updatePagination() {
                const totalPages = Math.ceil(this.totalUsers / this.itemsPerPage);
                const container = document.getElementById('paginationContainer');

                if (totalPages <= 1) {
                    container.innerHTML = `
                        <button class="btn-pagination" disabled>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span class="page-number active">1</span>
                        <button class="btn-pagination" disabled>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    `;
                    return;
                }

                let html = `
                    <button class="btn-pagination" ${this.currentPage <= 1 ? 'disabled' : ''} 
                            onclick="userManager.changePage(${this.currentPage - 1})">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                `;

                // Page numbers
                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                             onclick="userManager.changePage(${i})">${i}</span>`;
                }

                html += `
                    <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                            onclick="userManager.changePage(${this.currentPage + 1})">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                `;

                container.innerHTML = html;
            }

            updateShowingInfo() {
                const startItem = (this.currentPage - 1) * this.itemsPerPage + 1;
                const endItem = Math.min(this.currentPage * this.itemsPerPage, this.totalUsers);
                document.getElementById('showingInfo').textContent =
                    `Showing ${startItem} to ${endItem} of ${this.totalUsers} entries`;
            }

            async changePage(page) {
                const totalPages = Math.ceil(this.totalUsers / this.itemsPerPage);
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    await this.loadUsers();
                    this.loadTableView();
                }
            }

            async searchUsers(query) {
                this.currentPage = 1;
                await this.loadUsers();
                this.loadTableView();
            }
        }

        // Initialize user manager
        let userManager;
        document.addEventListener('DOMContentLoaded', function() {
            userManager = new UserManager();
        });

        // Global functions for user actions
        async function editUser(userId) {
            const user = userManager.users.find(u => u.user_id == userId);
            if (!user) return;

            // Create role options for edit modal
            let roleOptions = '';
            userManager.roles.forEach(role => {
                const selected = role.role_id == user.role_id ? 'selected' : '';
                roleOptions += `<option value="${role.role_id}" ${selected}>${role.role_name}</option>`;
            });

            // Create and show edit modal
            const editModalHtml = `
                <div class="modal fade" id="editUserModal${userId}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5">
                                    <i class="fa-solid fa-edit"></i>
                                    Edit ${user.full_name || user.username}
                                </h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editUserForm${userId}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="content-input mb-3">
                                                <label class="form-label">
                                                    <i class="fa-solid fa-user"></i>
                                                    Full Name <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="editUserFullName${userId}" value="${user.full_name || ''}" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="content-input mb-3">
                                                <label class="form-label">
                                                    <i class="fa-solid fa-phone"></i>
                                                    Contact Number <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="editUserContact${userId}" value="${user.contact || ''}" required
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="content-input mb-3">
                                                <label class="form-label">
                                                    <i class="fa-solid fa-user-tag"></i>
                                                    Role <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control" id="editUserRole${userId}" required>
                                                    <option value="">Select Role</option>
                                                    ${roleOptions}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="content-input mb-3">
                                                <label class="form-label">
                                                    <i class="fa-solid fa-toggle-on"></i>
                                                    Status <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control" id="editUserStatus${userId}" required>
                                                    <option value="active" ${user.status === 'active' ? 'selected' : ''}>Active</option>
                                                    <option value="inactive" ${user.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="content-input mb-3">
                                                <label class="form-label">
                                                    <i class="fa-solid fa-at"></i>
                                                    Username <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="editUserUsername${userId}" value="${user.username}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="content-input mb-3">
                                                <label for="addPassword" class="form-label">
                                                    <i class="fa-solid fa-lock"></i>
                                                    New Password (optional)
                                                </label>
                                                <input type="password" class="form-control" id="editUserPassword${userId}" name="password" placeholder="Enter new password">
                                            </div>
                                        </div>
                                        

                                        <div class="col-md-6">
                                            <div class="content-input mb-3">
                                                <label class="form-label">
                                                    <i class="fa-solid fa-calendar"></i>
                                                    Date Added
                                                </label>
                                                <input type="date" class="form-control" 
                                                       value="${new Date(user.date_added).toISOString().split('T')[0]}" 
                                                       readonly disabled>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="content-input">
                                                <label class="form-label">
                                                    <i class="fa-solid fa-map-marker-alt"></i>
                                                    Address <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control" id="editUserAddress${userId}" rows="4" required>${user.address || ''}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="updateUser(${userId})">
                                    <i class="fa-solid fa-save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to DOM
            document.body.insertAdjacentHTML('beforeend', editModalHtml);

            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById(`editUserModal${userId}`));
            editModal.show();

            // Remove modal from DOM when closed
            document.getElementById(`editUserModal${userId}`).addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

       async function activateUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will activate the user account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, activate it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const activateButton = document.querySelector(`tr[data-user-id="${userId}"] .btn-activate`);
                    const originalContent = activateButton.innerHTML;
                    activateButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                    activateButton.disabled = true;

                    $.ajax({
                        url: '../backend/admin/activate_user.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            user_id: userId
                        },
                        success: function(activateResult) {
                            if (activateResult.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Activated!',
                                    text: 'User has been activated.'
                                }).then(() => {
                                    // Reload data
                                    userManager.loadUsers().then(() => {
                                        userManager.loadTableView();
                                    });
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: activateResult.message || 'Failed to activate user.'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', {
                                xhr,
                                status,
                                error
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while activating the user. Please try again.'
                            });
                        },
                        complete: function() {
                            // Restore button state
                            if (activateButton) {
                                activateButton.innerHTML = originalContent;
                                activateButton.disabled = false;
                            }
                        }
                    });
                }
            });
       }

        async function updateUser(userId) {
            const fullName = document.getElementById(`editUserFullName${userId}`).value.trim();
            const username = document.getElementById(`editUserUsername${userId}`).value.trim();
            const contact = document.getElementById(`editUserContact${userId}`).value.trim();
            const address = document.getElementById(`editUserAddress${userId}`).value.trim();
            const status = document.getElementById(`editUserStatus${userId}`).value;
            const roleId = document.getElementById(`editUserRole${userId}`).value;
            const password = document.getElementById(`editUserPassword${userId}`).value;

            if (!fullName || !username || !contact || !address || !status || !roleId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.'
                });
                return;
            }

            if(contact.length != 11 || !/^\d{11}$/.test(contact)){
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Contact Number',
                    text: 'Contact number must be exactly 11 digits.'
                });
                return;
            }

            if(password && password.length < 8){
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must be at least 8 characters long.'
                });
                return;
            }

            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if(password && !passwordPattern.test(password)){
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
                });
                return;
            }

            // Show loading state
            const updateButton = document.querySelector(`#editUserModal${userId} .btn-primary`);
            const originalText = updateButton.innerHTML;
            updateButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
            updateButton.disabled = true;

            $.ajax({
                url: '../backend/admin/update_user.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    user_id: userId,
                    full_name: fullName,
                    username: username,
                    contact: contact,
                    address: address,
                    status: status,
                    role_id: roleId,
                    password: password 
                },
                success: function(result) {
                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'User updated successfully.'
                        }).then(() => {
                            // Close modal
                            bootstrap.Modal.getInstance(document.getElementById(`editUserModal${userId}`)).hide();

                            // Reload data
                            userManager.loadUsers().then(() => {
                                userManager.loadTableView();
                            });
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to update user.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        xhr,
                        status,
                        error
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating the user. Please try again.'
                    });
                },
                complete: function() {
                    // Restore button state
                    updateButton.innerHTML = originalText;
                    updateButton.disabled = false;
                }
            });
        }

        async function deleteUser(userId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, deactivate it!',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                // Show loading state in the delete button
                const deleteButton = document.querySelector(`tr[data-user-id="${userId}"] .btn-delete`);
                const originalContent = deleteButton.innerHTML;
                deleteButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                deleteButton.disabled = true;

                $.ajax({
                    url: '../backend/admin/deactivate_user.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: userId
                    },
                    success: function(deleteResult) {
                        if (deleteResult.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deactivated!',
                                text: 'User has been deactivated.'
                            }).then(() => {
                                // Reload data
                                userManager.loadUsers().then(() => {
                                    userManager.loadTableView();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: deleteResult.message || 'Failed to deactivate user.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {
                            xhr,
                            status,
                            error
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deactivating the user. Please try again.'
                        });
                    },
                    complete: function() {
                        // Restore button state
                        if (deleteButton) {
                            deleteButton.innerHTML = originalContent;
                            deleteButton.disabled = false;
                        }
                    }
                });
            }
        }

        async function addUser() {
            const fullName = document.getElementById('addUserFullName').value.trim();
            const username = document.getElementById('addUserUsername').value.trim();
            const contact = document.getElementById('addUserContact').value.trim();
            const address = document.getElementById('addUserAddress').value.trim();
            const status = document.getElementById('addUserStatus').value;
            const roleId = document.getElementById('addUserRole').value;
            const password = document.getElementById('addUserPassword').value;

            if (!fullName || !username || !contact || !address || !status || !roleId || !password) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.'
                });
                return;
            }

            if(contact.length != 11 || !/^\d{11}$/.test(contact)){
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Contact Number',
                    text: 'Contact number must be exactly 11 digits.'
                });
                return;
            }

            if(password.length < 8){
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must be at least 8 characters long.'
                });
                return;
            }

            //password should have uppercase, lowercase, number, special character
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;
            if(!passwordPattern.test(password)){
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
                });
                return;
            }

            // Show loading state
            const addButton = document.getElementById('addUserBtn');
            const originalText = addButton.innerHTML;
            addButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
            addButton.disabled = true;

            $.ajax({
                url: '../backend/admin/add_user.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    full_name: fullName,
                    username: username,
                    contact: contact,
                    address: address,
                    status: status,
                    role_id: roleId,
                    password: password
                },
                success: function(result) {
                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'User created successfully.'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to create user.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        xhr,
                        status,
                        error
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while creating the user. Please try again.'
                    });
                },
                complete: function() {
                    // Restore button state
                    addButton.innerHTML = originalText;
                    addButton.disabled = false;
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-Ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
</body>

</html>