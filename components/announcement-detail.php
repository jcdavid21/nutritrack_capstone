<?php
session_start();
include_once '../backend/config.php';

// Get announcement ID from URL
$announcement_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($announcement_id <= 0) {
    header('Location: ./announcements.php');
    exit();
}

// Fetch announcement details
$query = "SELECT ta.username, tz.zone_name, tba.announcement_id, tba.content, tba.img_content, tba.title, tba.post_date, td.full_name
          FROM tbl_announcements tba 
          JOIN tbl_user ta ON tba.user_id = ta.user_id 
          JOIN tbl_user_details td ON ta.user_id = td.user_id
          JOIN tbl_barangay tz ON tba.zone_id = tz.zone_id 
          WHERE tba.announcement_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $announcement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ./announcements.php');
    exit();
}

$announcement = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/announcement-detail.css">
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?php echo htmlspecialchars($announcement['title']); ?> - NutritionTrack</title>
</head>

<body>
    <?php include_once './navbar.php'; ?>
    
    <main>
        <div class="announcement-detail-container">

            <!-- Main Content -->
            <article class="announcement-content">
                <!-- Header -->
                <header class="announcement-header">
                    <div class="header-meta">
                        <div class="category-badge">
                            <i class="fa-solid fa-bullhorn"></i>
                            Announcement
                        </div>
                        <div class="date-info">
                            <i class="fa-solid fa-calendar"></i>
                            Published on <?php echo date('F d, Y', strtotime($announcement['post_date'])); ?>
                        </div>
                    </div>
                    
                    <h1 class="announcement-title">
                        <?php echo htmlspecialchars($announcement['title']); ?>
                    </h1>
                    
                    <div class="author-info">
                        <div class="author-details">
                            <div class="author-avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="author-text">
                                <span class="author-name">By <?php echo htmlspecialchars($announcement['full_name']); ?></span>
                                <span class="author-location">
                                    <i class="fa-solid fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($announcement['zone_name']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="share-buttons">
                            <button class="share-btn" onclick="shareAnnouncement()" title="Share this announcement">
                                <i class="fa-solid fa-share-alt"></i>
                                Share
                            </button>
                        </div>
                    </div>
                </header>

                <!-- Featured Image -->
                <div class="announcement-image">
                    <img src="../assets/announcements/<?php echo htmlspecialchars($announcement['img_content']); ?>" 
                         alt="<?php echo htmlspecialchars($announcement['title']); ?>"
                         onerror="this.style.display='none'">
                </div>

                <!-- Content -->
                <div class="announcement-body">
                    <div class="content-wrapper">
                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                    </div>
                </div>

                <!-- Footer Actions -->
                <footer class="announcement-footer">
                    <div class="footer-actions">
                        <a href="./announcements.php" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back to All Announcements
                        </a>
                        
                        <button class="btn btn-primary" onclick="shareAnnouncement()">
                            <i class="fa-solid fa-share-alt"></i>
                            Share This Announcement
                        </button>
                    </div>
                </footer>
            </article>

            <!-- Related Announcements -->
            <section class="related-announcements">
                <h2>
                    <i class="fa-solid fa-bullhorn"></i>
                    Other Recent Announcements
                </h2>
                
                <div class="related-grid">
                    <?php
                    // Fetch related announcements (excluding current one)
                    $related_query = "SELECT ta.username, tz.zone_name, tba.announcement_id, tba.content, tba.img_content, tba.title, tba.post_date 
                                     FROM tbl_announcements tba 
                                     JOIN tbl_user ta ON tba.user_id = ta.user_id 
                                     JOIN tbl_barangay tz ON tba.zone_id = tz.zone_id 
                                     WHERE tba.announcement_id != ? 
                                     ORDER BY tba.post_date DESC 
                                     LIMIT 3";
                    
                    $related_stmt = $conn->prepare($related_query);
                    $related_stmt->bind_param("i", $announcement_id);
                    $related_stmt->execute();
                    $related_result = $related_stmt->get_result();
                    
                    if ($related_result->num_rows > 0) {
                        while ($related = $related_result->fetch_assoc()) {
                    ?>
                        <article class="related-card">
                            <div class="related-image">
                                <img src="../assets/announcements/<?php echo htmlspecialchars($related['img_content']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['title']); ?>"
                                     onerror="this.style.display='none'">
                            </div>
                            <div class="related-content">
                                <div class="related-date">
                                    <i class="fa-solid fa-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($related['post_date'])); ?>
                                </div>
                                <h3><?php echo htmlspecialchars($related['title']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($related['content'], 0, 100)) . '...'; ?></p>
                                <a href="./announcement-detail.php?id=<?php echo $related['announcement_id']; ?>" class="related-link">
                                    Read More <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php
                        }
                    } else {
                        echo '<p class="no-related">No other announcements available at this time.</p>';
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>

    <?php include_once './footer.php'; ?>

    <script src="../js/navbar.js"></script>
    <script>
        // Share functionality
        function shareAnnouncement() {
            const title = <?php echo json_encode($announcement['title']); ?>;
            const url = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: 'Check out this announcement from NutritionTrack',
                    url: url
                }).catch(console.error);
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(url).then(() => {
                    showNotification('Link copied to clipboard!');
                }).catch(() => {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = url;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    showNotification('Link copied to clipboard!');
                });
            }
        }
        
        // Notification function
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                z-index: 10000;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Smooth scroll for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>