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
$count_query = "SELECT COUNT(*) as total FROM tbl_modules";
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

    <title>Educational Modules</title>
    <style>
        /* Additional styles for image preview */
        .image-preview-container {
            margin-top: 10px;
            text-align: center;
            max-width: 300px;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid #ddd;
            display: none;
        }
        
        .thumbnail-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fa-solid fa-book"></i>
                Educational Modules
            </h1>
            <p class="dashboard-subtitle">
                Manage and create educational modules to keep residents informed and engaged
            </p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="view-toggle">
                <!-- View toggle buttons can be added here if needed -->
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                <i class="fa-solid fa-plus"></i>
                Add New Module
            </button>
        </div>

        <div id="tableView" class="table-container">
            <div class="table-header">
                <div class="table-actions">
                    <div class="d-flex align-items-center gap-3">
                        <h3 class="mb-0">Modules List</h3>
                        <span class="badge bg-secondary" id="totalModulesCount"><?php echo $total_items; ?> Modules</span>
                    </div>
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search modules...">
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="announcements-table" id="modulesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Posted Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="modulesTableBody">

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

    <!-- Add Module Modal -->
    <div class="modal fade" id="addModuleModal" tabindex="-1" aria-labelledby="addModuleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addModuleModalLabel">
                        <i class="fa-solid fa-plus"></i>
                        Create New Module
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addModuleForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-12">
                                <div class="content-input mb-3">
                                    <label for="addModuleTitle" class="form-label">
                                        <i class="fa-solid fa-heading"></i>
                                        Module Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="addModuleTitle" name="module_title" placeholder="Enter module title" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="content-input mb-3">
                                    <label for="addModuleThumbnail" class="form-label">
                                        <i class="fa-solid fa-image"></i>
                                        Thumbnail Image
                                    </label>
                                    <input type="file" class="form-control" id="addModuleThumbnail" name="module_thumbnail" accept="image/*" onchange="previewImage(this, 'addModulePreview')">
                                    <div class="form-text">Upload a thumbnail image for the module (optional)</div>
                                    
                                    <!-- Image preview container -->
                                    <div class="image-preview-container mt-2">
                                        <img id="addModulePreview" class="thumbnail-preview object-fit-cover " src="../assets/modules/default-thumbnail.png" alt="Thumbnail preview">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="content-input mb-3">
                                    <label for="addModuleContent" class="form-label">
                                        <i class="fa-solid fa-file-lines"></i>
                                        Module Content <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="addModuleContent" name="module_content" rows="6" placeholder="Enter module content..." required></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addModuleBtn" onclick="addModule()">
                        <i class="fa-solid fa-save"></i>
                        Create Module
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to preview selected image
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.src = "../assets/modules/default-thumbnail.png";
            }
        }

        class ModuleManager {
            constructor() {
                this.currentPage = 1;
                this.itemsPerPage = 5;
                this.totalModules = 0;
                this.modules = [];
                this.init();
            }

            async init() {
                this.setupEventListeners();
                await this.loadModules();
                this.loadTableView();
            }

            setupEventListeners() {
                document.getElementById('searchInput').addEventListener('input', (e) => this.searchModules(e.target.value));
            }

            async loadModules() {
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.itemsPerPage,
                        search: document.getElementById('searchInput')?.value || ''
                    });

                    const response = await fetch(`./get_data/load_modules.php?${params}`);
                    const data = await response.json();
                    this.modules = data.modules || [];
                    this.totalModules = data.total || 0;

                    // Update total modules display
                    document.getElementById('totalModulesCount').textContent = `${this.totalModules} Modules`;
                } catch (error) {
                    console.error('Error loading modules:', error);
                }
            }

            loadTableView() {
                const tbody = document.getElementById('modulesTableBody');

                let html = '';
                if (this.modules.length === 0) {
                    html = `<tr class="no-data">
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fa-solid fa-book"></i>
                                <h3>No modules found</h3>
                                <p>Start by creating your first educational module</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                                    <i class="fa-solid fa-plus"></i>
                                    Create Module
                                </button>
                            </div>
                        </td>
                    </tr>`;
                } else {
                    this.modules.forEach(module => {
                        const postedDate = new Date(module.posted_date);
                        const formattedDate = postedDate.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        const thumbnailSrc = module.module_thumbnail ? 
                            `../assets/modules/${module.module_thumbnail}` : 
                            '../assets/modules/default-thumbnail.png';

                        html += `<tr data-module-id="${module.module_id}">
                            <td class="id-cell">#${module.module_id.toString().padStart(3, '0')}</td>
                            <td class="thumbnail-cell">
                                <img src="${thumbnailSrc}" alt="Thumbnail" class="module-thumbnail" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" onerror="this.src='../assets/modules/default-thumbnail.png'">
                            </td>
                            <td class="title-cell">
                                <div class="title-content">
                                    <span class="title">${module.module_title}</span>
                                </div>
                            </td>
                            <td class="content-cell">
                                <div class="content-preview" title="${module.module_content}">
                                    ${module.module_content.length > 80 ? module.module_content.substring(0, 80) + '...' : module.module_content}
                                </div>
                            </td>
                            <td class="date-cell">
                                <div class="date-info">
                                    <span class="date">${formattedDate}</span>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" title="Edit" onclick="editModule(${module.module_id})">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" title="Delete" onclick="deleteModule(${module.module_id})">
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

            updatePagination() {
                const totalPages = Math.ceil(this.totalModules / this.itemsPerPage);
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
                            onclick="moduleManager.changePage(${this.currentPage - 1})">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                `;

                // Page numbers
                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                             onclick="moduleManager.changePage(${i})">${i}</span>`;
                }

                html += `
                    <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                            onclick="moduleManager.changePage(${this.currentPage + 1})">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                `;

                container.innerHTML = html;
            }

            updateShowingInfo() {
                const startItem = (this.currentPage - 1) * this.itemsPerPage + 1;
                const endItem = Math.min(this.currentPage * this.itemsPerPage, this.totalModules);
                document.getElementById('showingInfo').textContent =
                    `Showing ${startItem} to ${endItem} of ${this.totalModules} entries`;
            }

            async changePage(page) {
                const totalPages = Math.ceil(this.totalModules / this.itemsPerPage);
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    await this.loadModules();
                    this.loadTableView();
                }
            }

            async searchModules(query) {
                this.currentPage = 1; 
                await this.loadModules(); 
                this.loadTableView();
            }
        }

        // Initialize module manager
        let moduleManager;
        document.addEventListener('DOMContentLoaded', function() {
            moduleManager = new ModuleManager();
        });

        // Global functions for module actions
        async function editModule(moduleId) {
            const module = moduleManager.modules.find(m => m.module_id == moduleId);
            if (!module) return;

            // Create and show edit modal
            const editModalHtml = `
                <div class="modal fade" id="editModuleModal${moduleId}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5">
                                    <i class="fa-solid fa-edit"></i>
                                    ${module.module_title}
                                </h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body d-flex gap-3">
                                <div class="img-body col-4">
                                    <div class="img-label">
                                        <i class="fa-solid fa-image"></i>
                                        Thumbnail
                                    </div>
                                    <div class="img-con mb-3">
                                        <img id="editModulePreview${moduleId}" 
                                             src="${module.module_thumbnail ? `../assets/modules/${module.module_thumbnail}` : '../assets/modules/default-thumbnail.png'}" 
                                             alt="Module thumbnail" 
                                             class="thumbnail-preview"
                                             onerror="this.src='../assets/modules/default-thumbnail.png'">
                                    </div>
                                    <input type="file" class="form-control" 
                                           accept="image/*" 
                                           id="editModuleThumbnail${moduleId}"
                                           onchange="previewImage(this, 'editModulePreview${moduleId}')">
                                </div>
                                <div class="content-body col-8">
                                    <div class="content-input mb-3">
                                        <label class="form-label">
                                            <i class="fa-solid fa-heading"></i>
                                            Module Title <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="editModuleTitle${moduleId}" value="${module.module_title}" required>
                                    </div>
                                    <div class="content-input mb-3">
                                        <label class="form-label">
                                            <i class="fa-solid fa-calendar"></i>
                                            Posted Date
                                        </label>
                                        <input type="date" class="form-control" 
                                               value="${new Date(module.posted_date).toISOString().split('T')[0]}" 
                                               readonly disabled>
                                    </div>
                                    <div class="content-input">
                                        <label class="form-label">
                                            <i class="fa-solid fa-file-lines"></i>
                                            Module Content <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" id="editModuleContent${moduleId}" rows="8" required>${module.module_content}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="updateModule(${moduleId})">
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
            const editModal = new bootstrap.Modal(document.getElementById(`editModuleModal${moduleId}`));
            editModal.show();

            // Remove modal from DOM when closed
            document.getElementById(`editModuleModal${moduleId}`).addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        async function updateModule(moduleId) {
            const title = document.getElementById(`editModuleTitle${moduleId}`).value.trim();
            const content = document.getElementById(`editModuleContent${moduleId}`).value.trim();
            const thumbnailFile = document.getElementById(`editModuleThumbnail${moduleId}`).files[0];

            if (!title || !content) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.'
                });
                return;
            }

            // Show loading state
            const updateButton = document.querySelector(`#editModuleModal${moduleId} .btn-primary`);
            const originalText = updateButton.innerHTML;
            updateButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
            updateButton.disabled = true;

            const formData = new FormData();
            formData.append('module_id', moduleId);
            formData.append('module_title', title);
            formData.append('module_content', content);
            if (thumbnailFile) {
                formData.append('module_thumbnail', thumbnailFile);
            }

            $.ajax({
                url: '../backend/admin/update_module.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(result) {
                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Module updated successfully.'
                        }).then(() => {
                            // Close modal
                            bootstrap.Modal.getInstance(document.getElementById(`editModuleModal${moduleId}`)).hide();

                            // Reload data
                            moduleManager.loadModules().then(() => {
                                moduleManager.loadTableView();
                            });
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to update module.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', { xhr, status, error });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating the module. Please try again.'
                    });
                },
                complete: function() {
                    // Restore button state
                    updateButton.innerHTML = originalText;
                    updateButton.disabled = false;
                }
            });
        }

        async function deleteModule(moduleId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                // Show loading state in the delete button
                const deleteButton = document.querySelector(`tr[data-module-id="${moduleId}"] .btn-delete`);
                const originalContent = deleteButton.innerHTML;
                deleteButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                deleteButton.disabled = true;

                $.ajax({
                    url: '../backend/admin/delete_module.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        module_id: moduleId
                    },
                    success: function(deleteResult) {
                        if (deleteResult.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Module has been deleted.'
                            }).then(() => {
                                // Reload data
                                moduleManager.loadModules().then(() => {
                                    moduleManager.loadTableView();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: deleteResult.message || 'Failed to delete module.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', { xhr, status, error });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the module. Please try again.'
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

        async function addModule() {
            const title = document.getElementById('addModuleTitle').value.trim();
            const content = document.getElementById('addModuleContent').value.trim();
            const thumbnailFile = document.getElementById('addModuleThumbnail').files[0];

            if (!title || !content) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.'
                });
                return;
            }

            // Show loading state
            const addButton = document.getElementById('addModuleBtn');
            const originalText = addButton.innerHTML;
            addButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
            addButton.disabled = true;

            const formData = new FormData();
            formData.append('module_title', title);
            formData.append('module_content', content);
            if (thumbnailFile) {
                formData.append('module_thumbnail', thumbnailFile);
            }

            $.ajax({
                url: '../backend/admin/add_module.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(result) {
                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Module created successfully.'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to create module.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', { xhr, status, error });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while creating the module. Please try again.'
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>

</html>