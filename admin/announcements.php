<!DOCTYPE html>
<html lang="en">
<?php
include "../backend/config.php";
session_start();
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
                    <button class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Add New Announcement
                    </button>
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" placeholder="Search announcements...">
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
                    <tbody>
                        <?php
                        $query = "SELECT ta.*, tz.zone_name, ts.username FROM tbl_announcements ta 
                            INNER JOIN tbl_barangay tz ON tz.zone_id = ta.zone_id
                            INNER JOIN tbl_user ts ON ts.user_id = ta.user_id
                            ORDER BY ta.post_date DESC";
                        $result = mysqli_query($conn, $query);
                        
                        if ($result && $result->num_rows > 0) {
                            while ($data = $result->fetch_assoc()) {
                                $content_preview = strlen($data["content"]) > 50 
                                    ? substr($data["content"], 0, 50) . "..." 
                                    : $data["content"];
                                
                                $formatted_date = date('M d, Y', strtotime($data["post_date"]));
                        ?>
                        <tr>
                            <td class="id-cell">#<?php echo str_pad($data["announcement_id"], 3, '0', STR_PAD_LEFT) ?></td>
                            <td class="thumbnail-cell">
                                <?php if (!empty($data["img_content"]) && $data["img_content"] != '*NULL*'): ?>
                                    <div class="thumbnail">
                                        <img src="../uploads/announcements/<?php echo $data["img_content"] ?>" alt="Thumbnail">
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
                                    <button class="btn-action btn-view" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
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
                    Showing 1 to <?php echo $result ? $result->num_rows : 0 ?> of <?php echo $result ? $result->num_rows : 0 ?> entries
                </div>
                <div class="pagination">
                    <button class="btn-pagination" disabled>
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <span class="page-number active">1</span>
                    <button class="btn-pagination" disabled>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('.announcements-table tbody tr:not(.no-data)');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', function() {
                console.log('View announcement');
            });
        });

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                console.log('Edit announcement');
            });
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
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
                        console.log('Delete announcement');
                    }
                });
            });
        });
    </script>
</body>

</html>