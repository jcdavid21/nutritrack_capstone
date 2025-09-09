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
$items_per_page = 5;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM tbl_announcements ta 
    INNER JOIN tbl_barangay tz ON tz.zone_id = ta.zone_id
    INNER JOIN tbl_user ts ON ts.user_id = ta.user_id";
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
    <style>
        /* Additional styles for the add modal */
        .placeholder-upload {
            width: 100%;
            height: 120px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .placeholder-upload:hover {
            border-color: #007bff;
            background-color: #e7f3ff;
        }

        .placeholder-upload.drag-over {
            border-color: #28a745;
            background-color: #d4edda;
        }

        .placeholder-upload i {
            font-size: 24px;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .placeholder-upload span {
            font-size: 12px;
            color: #6c757d;
            text-align: center;
        }

        #addAnnouncementModal .img-con {
            position: relative;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
        }

        #addAnnouncementModal .img-label {
            background-color: #1B5E20;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .img-label.updated {
            background-color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }
    </style>

    <title>Announcements</title>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fa-solid fa-bullhorn"></i>
                Announcements
            </h1>
            <p class="dashboard-subtitle">Manage and monitor community announcements across different zones.</p>
        </div>

        <div class="table-container">
            <div class="table-header">
                <div class="table-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                        <i class="fa-solid fa-plus"></i>
                        Add New Announcement
                    </button>
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search announcements...">
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="announcements-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Created By</th>
                            <th>Zone</th>
                            <th>Content Preview</th>
                            <th>Date Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="announcementsTableBody">
                        <?php
                        $query = "SELECT ta.*, tz.zone_name, ts.username FROM tbl_announcements ta 
                            INNER JOIN tbl_barangay tz ON tz.zone_id = ta.zone_id
                            INNER JOIN tbl_user ts ON ts.user_id = ta.user_id
                            ORDER BY ta.post_date DESC
                            LIMIT $items_per_page OFFSET $offset";
                        $result = mysqli_query($conn, $query);

                        if ($result && $result->num_rows > 0) {
                            while ($data = $result->fetch_assoc()) {
                                $content_preview = strlen($data["content"]) > 50
                                    ? substr($data["content"], 0, 50) . "..."
                                    : $data["content"];
                                date_default_timezone_set('Asia/Manila');
                                $formatted_date = date('M d, Y', strtotime($data["post_date"]));
                        ?>
                                <tr data-announcement-id="<?php echo $data['announcement_id'] ?>">
                                    <td class="id-cell">#<?php echo str_pad($data["announcement_id"], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td class="thumbnail-cell">
                                        <?php if (!empty($data["img_content"]) && $data["img_content"] != '*NULL*'): ?>
                                            <div class="thumbnail">
                                                <img src="../assets/announcements/<?php echo $data["img_content"] ?>" alt="Thumbnail">
                                            </div>
                                        <?php else: ?>
                                            <div class="thumbnail no-image">
                                                <i class="fa-solid fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="title-cell">
                                        <div class="title-content">
                                            <span class="title"><?php echo htmlspecialchars($content_preview) ?></span>
                                        </div>
                                    </td>
                                    <td class="user-cell">
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($data["username"], 0, 1)) ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($data["username"]) ?></span>
                                        </div>
                                    </td>
                                    <td class="zone-cell">
                                        <span class="zone-badge"><?php echo htmlspecialchars($data["zone_name"]) ?></span>
                                    </td>
                                    <td class="content-cell">
                                        <div class="content-preview" title="<?php echo htmlspecialchars($data["content"]) ?>">
                                            <?php echo htmlspecialchars($content_preview) ?>
                                        </div>
                                    </td>
                                    <td class="date-cell">
                                        <div class="date-info">
                                            <span class="date"><?php echo $formatted_date ?></span>
                                            <span class="time"><?php echo date('H:i', strtotime($data["post_date"])) ?></span>
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#exampleModal<?php echo $data["announcement_id"] ?>">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete"
                                            data-anc-id="<?php echo $data['announcement_id']; ?>"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal<?php echo $data["announcement_id"] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="exampleModalLabel">
                                                    <?php echo htmlspecialchars($data["title"]); ?>
                                                </h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body d-flex gap-3">
                                                <div class="img-body col-4">
                                                    <div class="img-label">
                                                        <i class="fa-solid fa-image"></i>
                                                        Thumbnail
                                                    </div>
                                                    <div class="img-con">
                                                        <img src="../assets/announcements/<?php echo htmlspecialchars($data["img_content"]); ?>" alt="annoncement">
                                                    </div>
                                                    <input type="file" class="form-control"
                                                        accept="image/*"
                                                        id="formFile<?php echo $data["announcement_id"] ?>">
                                                </div>

                                                <div class="content-body col-8">
                                                    <div class="d-flex gap-2">
                                                        <div class="content-input col-6">
                                                            <label for="created_by" class="form-label">
                                                                <i class="fa-solid fa-user"></i>
                                                                Created By</label>
                                                            <input type="text" class="form-control w-100" id="created_by" value="<?php echo htmlspecialchars($data["username"]); ?>"
                                                                readonly disabled>
                                                        </div>
                                                        <div class="content-input col-6">
                                                            <label for="barangay_zone" class="form-label">
                                                                <i class="fa-solid fa-map-marker-alt"></i>
                                                                Barangay Zone</label>
                                                            <?php
                                                            $query_zones = "SELECT * FROM tbl_barangay";
                                                            $result_zones = mysqli_query($conn, $query_zones);
                                                            if ($result_zones && $result_zones->num_rows > 0) {
                                                            ?>
                                                                <select class="form-select" id="barangay_zone" aria-label="Default select example">
                                                                    <option selected value="<?php echo $data["zone_id"]; ?>"><?php echo htmlspecialchars($data["zone_name"]); ?></option>
                                                                    <?php
                                                                    while ($zone = $result_zones->fetch_assoc()) {
                                                                        if ($zone["zone_id"] != $data["zone_id"]) {
                                                                    ?>
                                                                            <option value="<?php echo $zone["zone_id"]; ?>"><?php echo htmlspecialchars($zone["zone_name"]); ?></option>
                                                                <?php
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                                </select>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2 mt-3">
                                                        <div class="content-input col-6">
                                                            <label for="posted_date" class="form-label">
                                                                <i class="fa-solid fa-circle-info"></i>
                                                                Title</label>
                                                            <input type="text" class="form-control w-100" id="title" value="<?php echo htmlspecialchars($data["title"]); ?>">
                                                        </div>
                                                        <div class="content-input col-6">
                                                            <label for="posted_date" class="form-label">
                                                                <i class="fa-solid fa-calendar"></i>
                                                                Posted Date</label>
                                                            <input type="date" class="form-control w-100" id="posted_date" value="<?php echo date('Y-m-d', strtotime($data["post_date"])); ?>"
                                                            readonly disabled
                                                            >
                                                        </div>
                                                    </div>
                                                    <div class="content-input mt-3">
                                                        <label for="content" class="form-label">
                                                            <i class="fa-solid fa-file-lines"></i>
                                                            Content</label>
                                                        <textarea class="form-control col-12" id="content" rows="5"><?php echo htmlspecialchars($data["content"]); ?></textarea>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-submit btn-success"
                                                    data-anc-id="<?php echo $data['announcement_id']; ?>">Save changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                        } else {
                            ?>
                            <tr class="no-data">
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-bullhorn"></i>
                                        <h3>No announcements found</h3>
                                        <p>Start by creating your first announcement</p>
                                        <button class="btn btn-primary">
                                            <i class="fa-solid fa-plus"></i>
                                            Create Announcement
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="showing-info">
                    <?php
                    if ($total_items > 0) {
                        $start_item = $offset + 1;
                        $end_item = min($offset + $items_per_page, $total_items);
                        echo "Showing $start_item to $end_item of $total_items entries";
                    } else {
                        echo "Showing 0 to 0 of 0 entries";
                    }
                    ?>
                </div>
                <div class="pagination" id="paginationContainer">
                    <?php if ($total_pages > 1): ?>
                        <!-- Previous button -->
                        <button class="btn-pagination" id="prevBtn"
                            onclick="changePage(<?php echo $current_page - 1 ?>)"
                            <?php echo $current_page <= 1 ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <!-- Page numbers -->
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        // Show first page if not in range
                        if ($start_page > 1): ?>
                            <span class="page-number" onclick="changePage(1)">1</span>
                            <?php if ($start_page > 2): ?>
                                <span class="page-ellipsis">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Page number range -->
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <span class="page-number <?php echo $i == $current_page ? 'active' : '' ?>"
                                onclick="changePage(<?php echo $i ?>)">
                                <?php echo $i ?>
                            </span>
                        <?php endfor; ?>

                        <!-- Show last page if not in range -->
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span class="page-ellipsis">...</span>
                            <?php endif; ?>
                            <span class="page-number" onclick="changePage(<?php echo $total_pages ?>)">
                                <?php echo $total_pages ?>
                            </span>
                        <?php endif; ?>

                        <!-- Next button -->
                        <button class="btn-pagination" id="nextBtn"
                            onclick="changePage(<?php echo $current_page + 1 ?>)"
                            <?php echo $current_page >= $total_pages ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    <?php else: ?>
                        <button class="btn-pagination" disabled>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span class="page-number active">1</span>
                        <button class="btn-pagination" disabled>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addAnnouncementModalLabel">
                        <i class="fa-solid fa-plus"></i>
                        Add New Announcement
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex gap-3">
                    <div class="img-body col-4">
                        <div class="img-label">
                            <i class="fa-solid fa-image"></i>
                            Thumbnail
                        </div>
                        <div class="img-con" id="addImageContainer">
                            <div class="placeholder-upload">
                                <i class="fa-solid fa-cloud-upload-alt"></i>
                                <span>Click or drag to upload</span>
                            </div>
                            <img id="addPreviewImage" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                        </div>
                        <input type="file" class="form-control mt-2" accept="image/*" id="addFormFile">
                        <small class="text-muted">Max size: 5MB. Formats: JPG, PNG, GIF, WebP</small>
                    </div>

                    <div class="content-body col-8">
                        <div class="d-flex gap-2">
                            <div class="content-input col-12">
                                <label for="addCreatedBy" class="form-label">
                                    <i class="fa-solid fa-user"></i>
                                    Created By
                                </label>
                                <input type="text" class="form-control w-100" id="addCreatedBy"
                                    value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Current User'; ?>"
                                    readonly disabled>
                            </div>
                            <div class="content-input col-6">
                                <label for="addBarangayZone" class="form-label">
                                    <i class="fa-solid fa-map-marker-alt"></i>
                                    Barangay Zone <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="addBarangayZone" aria-label="Select barangay zone">
                                    <option value="">Select Zone</option>
                                    <?php
                                    $query_zones = "SELECT * FROM tbl_barangay ORDER BY zone_name";
                                    $result_zones = mysqli_query($conn, $query_zones);
                                    if ($result_zones && $result_zones->num_rows > 0) {
                                        while ($zone = $result_zones->fetch_assoc()) {
                                    ?>
                                            <option value="<?php echo $zone['zone_id']; ?>">
                                                <?php echo htmlspecialchars($zone['zone_name']); ?>
                                            </option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <div class="content-input col-6">
                                <label for="addTitle" class="form-label">
                                    <i class="fa-solid fa-circle-info"></i>
                                    Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control w-100" id="addTitle" placeholder="Enter announcement title">
                            </div>
                            <div class="content-input col-6">
                                <label for="addPostedDate" class="form-label">
                                    <i class="fa-solid fa-calendar"></i>
                                    Posted Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control w-100" id="addPostedDate" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="content-input mt-3">
                            <label for="addContent" class="form-label">
                                <i class="fa-solid fa-file-lines"></i>
                                Content <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control col-12" id="addContent" rows="5" placeholder="Enter announcement content..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addAnnouncementBtn">
                        <i class="fa-solid fa-plus"></i>
                        Create Announcement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>

    <script>
        // Pagination function
        function changePage(page) {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('page', page);
            window.location.href = currentUrl.toString();
        }

        // Search functionality
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value.toLowerCase().trim();

            searchTimeout = setTimeout(() => {
                if (searchTerm === '') {
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.delete('search');
                    currentUrl.searchParams.set('page', 1);
                    window.location.href = currentUrl.toString();
                } else {
                    const tableRows = document.querySelectorAll('#announcementsTableBody tr:not(.no-data)');
                    let visibleCount = 0;

                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const isVisible = text.includes(searchTerm);
                        row.style.display = isVisible ? '' : 'none';
                        if (isVisible) visibleCount++;
                    });

                    const showingInfo = document.querySelector('.showing-info');
                    if (visibleCount === 0) {
                        showingInfo.textContent = 'No matching announcements found';
                        document.getElementById('paginationContainer').style.display = 'none';
                    } else {
                        showingInfo.textContent = `Showing ${visibleCount} matching results`;
                        document.getElementById('paginationContainer').style.display = 'none';
                    }
                }
            }, 300);
        });

        $(document).ready(function() {
            // Store original image sources when page loads
            $('.modal').each(function() {
                const imgElement = $(this).find('.img-con img');
                const originalSrc = imgElement.attr('src');
                imgElement.data('original-src', originalSrc);
            });

            // Image upload and preview functionality
            $('input[type="file"]').on('change', function() {
                const fileInput = this;
                const file = fileInput.files[0];
                const modal = $(this).closest('.modal');
                const imgElement = modal.find('.img-con img');
                const imgLabel = modal.find('.img-label');

                if (file) {
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        Swal.fire('Error', 'Please select a valid image file (JPEG, PNG, GIF, or WebP).', 'error');
                        fileInput.value = '';
                        return;
                    }

                    // Validate file size (max 5MB)
                    const maxSize = 5 * 1024 * 1024;
                    if (file.size > maxSize) {
                        Swal.fire('Error', 'File size must be less than 5MB.', 'error');
                        fileInput.value = '';
                        return;
                    }

                    // Create FileReader to preview the image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Update the image source with the new file
                        imgElement.attr('src', e.target.result);
                        imgElement.attr('alt', 'New thumbnail preview');

                        // Add visual indicator that image has been changed
                        imgLabel.html('<i class="fa-solid fa-image"></i> Thumbnail (Updated)');
                        imgLabel.css('background-color', '#28a745').addClass('updated');

                        // Show success message
                        Swal.fire({
                            title: 'Image Updated!',
                            text: 'Your new thumbnail has been loaded. Don\'t forget to save changes.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Save changes functionality
            $('.btn-submit').click(function() {
                const ancId = $(this).data('anc-id');
                const modal = $(this).closest('.modal');
                const title = modal.find('#title').val().trim();
                const content = modal.find('#content').val().trim();
                const zoneId = modal.find('#barangay_zone').val();
                const postDate = modal.find('#posted_date').val();
                const fileInput = modal.find('#formFile' + ancId)[0];
                const file = fileInput.files[0];

                if (!title || !content || !zoneId || !postDate) {
                    Swal.fire('Error', 'Please fill in all required fields.', 'error');
                    return;
                }

                const originalText = $(this).html();
                $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

                const formData = new FormData();
                formData.append('announcement_id', ancId);
                formData.append('title', title);
                formData.append('content', content);
                formData.append('zone_id', zoneId);
                formData.append('post_date', postDate);

                // Only append file if a new one was selected
                if (file) {
                    formData.append('thumbnail', file);
                }

                $.ajax({
                    url: '../backend/admin/update_announcement.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire('Success', res.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                            Swal.fire('Error', 'An unexpected error occurred.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        Swal.fire('Error', 'Failed to update announcement. Please try again.', 'error');
                    },
                    complete: function() {
                        // Restore button state
                        $('.btn-submit').prop('disabled', false).html(originalText);
                    }
                });
            });

            // Reset modal state when closed
            $('.modal').on('hidden.bs.modal', function() {
                const modal = $(this);
                const ancId = modal.attr('id').replace('exampleModal', '');
                const fileInput = modal.find('#formFile' + ancId)[0];
                const imgLabel = modal.find('.img-label');
                const imgElement = modal.find('.img-con img');

                // Clear file input
                fileInput.value = '';

                // Reset image label
                imgLabel.html('<i class="fa-solid fa-image"></i> Thumbnail');
                imgLabel.css('background-color', '#1B5E20').removeClass('updated');

                // Reset image to original source
                const originalSrc = imgElement.data('original-src');
                if (originalSrc) {
                    imgElement.attr('src', originalSrc);
                    imgElement.attr('alt', 'announcement');
                }
            });

            // Initialize drag and drop functionality
            $('.img-con').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('drag-over');
            });

            $('.img-con').on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
            });

            $('.img-con').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    const modal = $(this).closest('.modal');
                    const ancId = modal.attr('id').replace('exampleModal', '');
                    const fileInput = modal.find('#formFile' + ancId)[0];

                    // Set the file to the input
                    fileInput.files = files;

                    // Trigger change event
                    $(fileInput).trigger('change');
                }
            });

            // Make image container clickable to trigger file input
            $('.img-con').on('click', function() {
                const modal = $(this).closest('.modal');
                const ancId = modal.attr('id').replace('exampleModal', '');
                const fileInput = modal.find('#formFile' + ancId)[0];
                fileInput.click();
            });
        });

        // Delete functionality
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const announcementId = this.getAttribute('data-anc-id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../backend/admin/delete_announcement.php',
                            type: 'POST',
                            data: { announcement_id: announcementId },
                            success: function(response) {
                                try {
                                    const res = JSON.parse(response);
                                    if (res.status === 'success') {
                                        Swal.fire('Deleted!', res.message, 'success').then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire('Error', res.message, 'error');
                                    }
                                } catch (e) {
                                    console.error('Parse error:', e);
                                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX error:', error);
                                Swal.fire('Error', 'Failed to delete announcement. Please try again.', 'error');
                            }
                        });
                    }
                });
            });
        });

        // Show pagination when not searching
        document.getElementById('searchInput').addEventListener('focus', function() {
            if (this.value.trim() === '') {
                document.getElementById('paginationContainer').style.display = 'flex';
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // Image upload and preview functionality for add modal
            $('#addFormFile').on('change', function() {
                const fileInput = this;
                const file = fileInput.files[0];
                const previewImage = $('#addPreviewImage');
                const placeholder = $('.placeholder-upload');
                const imgLabel = $('#addAnnouncementModal .img-label'); // More specific selector

                if (file) {
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        Swal.fire('Error', 'Please select a valid image file (JPEG, PNG, GIF, or WebP).', 'error');
                        fileInput.value = '';
                        return;
                    }

                    // Validate file size (max 5MB)
                    const maxSize = 5 * 1024 * 1024;
                    if (file.size > maxSize) {
                        Swal.fire('Error', 'File size must be less than 5MB.', 'error');
                        fileInput.value = '';
                        return;
                    }

                    // Create FileReader to preview the image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Show the image and hide placeholder
                        placeholder.hide();
                        previewImage.attr('src', e.target.result).show();

                        // Update label to show image is selected
                        imgLabel.html('<i class="fa-solid fa-image"></i> Thumbnail (Selected)');
                        imgLabel.addClass('updated');

                        // Show success message
                        Swal.fire({
                            title: 'Image Selected!',
                            text: 'Your thumbnail has been loaded.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Create new announcement functionality
            $('#addAnnouncementBtn').click(function() {
                const title = $('#addTitle').val();
                const content = $('#addContent').val();
                const zoneId = $('#addBarangayZone').val();
                const postDate = $('#addPostedDate').val();
                const fileInput = $('#addFormFile')[0];
                const file = fileInput ? fileInput.files[0] : null;

                // Validation
                if (!title) {
                    Swal.fire('Error', 'Please enter an announcement title.', 'error');
                    $('#addTitle').focus();
                    return;
                }

                if (!content) {
                    Swal.fire('Error', 'Please enter announcement content.', 'error');
                    $('#addContent').focus();
                    return;
                }

                if (!zoneId) {
                    Swal.fire('Error', 'Please select a barangay zone.', 'error');
                    $('#addBarangayZone').focus();
                    return;
                }

                if (!postDate) {
                    Swal.fire('Error', 'Please select a posted date.', 'error');
                    $('#addPostedDate').focus();
                    return;
                }

                // Show loading state
                const originalText = $(this).html();
                $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Creating...');

                // Prepare form data
                const formData = new FormData();
                formData.append('title', title);
                formData.append('content', content);
                formData.append('zone_id', zoneId);
                formData.append('post_date', postDate);

                // Only append file if one was selected
                if (file) {
                    formData.append('thumbnail', file);
                }

                // Submit the form
                $.ajax({
                    url: '../backend/admin/add_announcement.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: res.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    // Close modal and reload page
                                    $('#addAnnouncementModal').modal('hide');
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                            console.log('Raw response:', response);
                            Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        Swal.fire('Error', 'Failed to create announcement. Please try again.', 'error');
                    },
                    complete: function() {
                        // Restore button state
                        $('#addAnnouncementBtn').prop('disabled', false).html(originalText);
                    }
                });
            });

            // Reset modal state when closed
            $('#addAnnouncementModal').on('hidden.bs.modal', function() {
                // Clear all form fields
                $('#addTitle').val('');
                $('#addContent').val('');
                $('#addBarangayZone').val('');
                $('#addPostedDate').val('<?php echo date('Y-m-d'); ?>');

                // Clear file input and reset image preview
                const fileInput = $('#addFormFile')[0];
                if (fileInput) {
                    fileInput.value = '';
                }

                // Reset image preview
                const previewImage = $('#addPreviewImage');
                const placeholder = $('#addAnnouncementModal .placeholder-upload');
                const imgLabel = $('#addAnnouncementModal .img-label');

                previewImage.hide();
                placeholder.show();

                // Reset label
                imgLabel.html('<i class="fa-solid fa-image"></i> Thumbnail');
                imgLabel.removeClass('updated');
            });

            // Initialize drag and drop functionality for add modal
            $('#addImageContainer').on('dragover', function(e) {
                e.preventDefault();
                $('#addAnnouncementModal .placeholder-upload').addClass('drag-over');
            });

            $('#addImageContainer').on('dragleave', function(e) {
                e.preventDefault();
                $('#addAnnouncementModal .placeholder-upload').removeClass('drag-over');
            });

            $('#addImageContainer').on('drop', function(e) {
                e.preventDefault();
                $('#addAnnouncementModal .placeholder-upload').removeClass('drag-over');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    const fileInput = $('#addFormFile')[0];
                    if (fileInput) {
                        fileInput.files = files;
                        $(fileInput).trigger('change');
                    }
                }
            });

            // Make image container clickable to trigger file input
            $('#addImageContainer').on('click', function() {
                const fileInput = $('#addFormFile')[0];
                if (fileInput) {
                    fileInput.click();
                }
            });

            // Alternative click handler for placeholder specifically
            $('#addAnnouncementModal .placeholder-upload').on('click', function(e) {
                e.stopPropagation(); // Prevent event bubbling
                const fileInput = $('#addFormFile')[0];
                if (fileInput) {
                    fileInput.click();
                }
            });
        });
    </script>

</body>

</html>