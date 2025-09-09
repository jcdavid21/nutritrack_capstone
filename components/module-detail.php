<?php
session_start();
include_once '../backend/config.php';

// Get module ID from URL
$module_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($module_id <= 0) {
    header('Location: ./modules.php');
    exit();
}

// Fetch module details
$query = "SELECT * FROM tbl_modules WHERE module_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ./modules.php');
    exit();
}

$module = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/module-detail.css">
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?php echo htmlspecialchars($module['module_title']); ?> - NutritionTrack</title>
</head>

<body>
    <?php include_once './navbar.php'; ?>

    <main>
        <div class="module-detail-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb-section">
                <div class="breadcrumb-container">
                    <nav class="breadcrumb">
                        <a href="./home.php">
                            <i class="fa-solid fa-home"></i>
                            Home
                        </a>
                        <span><i class="fa-solid fa-chevron-right"></i></span>
                        <a href="./modules.php">Learning Modules</a>
                        <span><i class="fa-solid fa-chevron-right"></i></span>
                        <span><?php echo htmlspecialchars(substr($module['module_title'], 0, 30)) . (strlen($module['module_title']) > 30 ? '...' : ''); ?></span>
                    </nav>

                    <a href="./modules.php" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Modules
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <article class="module-content">
                <header class="module-header">
                    <div class="header-meta">
                        <div class="category-badge">
                            <i class="fa-solid fa-graduation-cap"></i>
                            Learning Module
                        </div>
                        <div class="module-stats">
                            <div class="stat-item">
                                <i class="fa-solid fa-book-open"></i>
                                <span>Study Material</span>
                            </div>
                            <div class="stat-item">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>All Levels</span>
                            </div>
                        </div>
                    </div>

                    <h1 class="module-title">
                        <?php echo htmlspecialchars($module['module_title']); ?>
                    </h1>

                    <div class="module-meta">
                        <div class="date-info">
                            <i class="fa-solid fa-calendar"></i>
                            Published on <?php echo date('F d, Y', strtotime($module['posted_date'])); ?>
                        </div>

                        <div class="progress-container">
                            <div class="progress-info">
                                <i class="fa-solid fa-lightbulb"></i>
                                <span>Learning Progress</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Featured Image -->
                <div class="module-image">
                    <img src="../assets/modules/<?php echo htmlspecialchars($module['module_thumbnail']); ?>"
                        alt="<?php echo htmlspecialchars($module['module_title']); ?>"
                        onerror="this.style.display='none'">
                </div>

                <!-- Module Overview -->
                <section class="module-overview">
                    <h2>
                        <i class="fa-solid fa-list"></i>
                        What You'll Learn
                    </h2>
                    <div class="overview-grid">
                        <div class="overview-item">
                            <i class="fa-solid fa-lightbulb"></i>
                            <h3>Key Concepts</h3>
                            <p>Learn essential nutrition principles and their practical applications</p>
                        </div>
                        <div class="overview-item">
                            <i class="fa-solid fa-chart-line"></i>
                            <h3>Practical Tips</h3>
                            <p>Discover actionable strategies you can implement in your daily routine</p>
                        </div>
                        <div class="overview-item">
                            <i class="fa-solid fa-heart"></i>
                            <h3>Health Benefits</h3>
                            <p>Understand how proper nutrition impacts your overall well-being</p>
                        </div>
                    </div>
                </section>

                <!-- Reading Progress Indicator -->
                <div class="reading-progress">
                    <h3>
                        <i class="fa-solid fa-book"></i>
                        Please Read This Module Carefully
                    </h3>
                    <p>Take your time to absorb the information presented. This module contains valuable insights that will help you on your nutrition journey.</p>
                </div>

                <!-- Module Content -->
                <section class="module-body" id="moduleContent">
                    <div class="content-wrapper">
                        <?php echo nl2br(htmlspecialchars($module['module_content'])); ?>

                        <blockquote>
                            <strong>Remember:</strong> The information in this module is designed to provide you with a comprehensive understanding of the topic. Apply these concepts gradually and consistently for the best results.
                        </blockquote>
                    </div>
                </section>
            </article>

            <!-- Related Modules -->
            <section class="related-modules">
                <h2>
                    <i class="fa-solid fa-graduation-cap"></i>
                    Continue Learning
                </h2>

                <div class="related-grid">
                    <?php
                    // Fetch related modules (excluding current one)
                    $related_query = "SELECT * FROM tbl_modules WHERE module_id != ? ORDER BY posted_date DESC LIMIT 3";
                    $related_stmt = $conn->prepare($related_query);
                    $related_stmt->bind_param("i", $module_id);
                    $related_stmt->execute();
                    $related_result = $related_stmt->get_result();

                    if ($related_result->num_rows > 0) {
                        while ($related = $related_result->fetch_assoc()) {
                    ?>
                            <article class="related-card">
                                <div class="related-image">
                                    <img src="../assets/modules/<?php echo htmlspecialchars($related['module_thumbnail']); ?>"
                                        alt="<?php echo htmlspecialchars($related['module_title']); ?>"
                                        onerror="this.style.display='none'">
                                </div>
                                <div class="related-content">
                                    <div class="related-category">
                                        <i class="fa-solid fa-book-open"></i>
                                        Learning Module
                                    </div>
                                    <h3><?php echo htmlspecialchars($related['module_title']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($related['module_content'], 0, 100)) . '...'; ?></p>
                                    <a href="./module-detail.php?id=<?php echo $related['module_id']; ?>" class="related-link">
                                        Start Reading <i class="fa-solid fa-book-open"></i>
                                    </a>
                                </div>
                            </article>
                    <?php
                        }
                    } else {
                        echo '<p class="no-related">No other modules available at this time.</p>';
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>

    <?php include_once './footer.php'; ?>

    <script src="../js/navbar.js"></script>
    <script>
        let scrollProgress = 0;

        // Initialize module
        function initModule() {
            setupScrollListener();
        }

        // Start reading function
        function startReading() {
            document.getElementById('moduleContent').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }


        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;

            const bgColor = type === 'success' ? '#4CAF50' : '#f44336';

            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${bgColor};
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                z-index: 10000;
                font-size: 14px;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-width: 300px;
                word-wrap: break-word;
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }

        // Add reading time estimator
        function estimateReadingTime() {
            const contentWrapper = document.querySelector('.content-wrapper');
            if (!contentWrapper) return;

            const text = contentWrapper.textContent || contentWrapper.innerText;
            const wordCount = text.trim().split(/\s+/).length;
            const readingSpeed = 200; // Average reading speed: 200 words per minute
            const estimatedTime = Math.ceil(wordCount / readingSpeed);

            // Update the reading time in the header if needed
            const timeElement = document.querySelector('.stat-item span');
            if (timeElement && estimatedTime > 0) {
                timeElement.textContent = `${estimatedTime} min read`;
            }
        }

        // Smooth scroll for internal links
        function setupSmoothScrolling() {
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
        }

        // Add fade-in animation to content sections
        function addContentAnimations() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            const sections = document.querySelectorAll('.module-overview, .module-body, .related-card');
            sections.forEach(section => {
                observer.observe(section);
            });
        }

        // Add keyboard navigation support
        document.addEventListener('keydown', function(e) {
            // Enable keyboard navigation with arrow keys
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
                const scrollAmount = e.key === 'ArrowUp' ? -100 : 100;
                window.scrollBy({
                    top: scrollAmount,
                    behavior: 'smooth'
                });
            }

            // Home key - scroll to top
            if (e.key === 'Home') {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            // End key - scroll to bottom
            if (e.key === 'End') {
                e.preventDefault();
                window.scrollTo({
                    top: document.documentElement.scrollHeight,
                    behavior: 'smooth'
                });
            }
        });

        // Print functionality
        function printModule() {
            window.print();
        }

        // Share functionality
        function shareModule() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                }).then(() => {
                    showNotification('Module shared successfully!');
                }).catch((error) => {
                    console.log('Error sharing:', error);
                    fallbackShare();
                });
            } else {
                fallbackShare();
            }
        }

        function fallbackShare() {
            // Fallback: copy URL to clipboard
            navigator.clipboard.writeText(window.location.href).then(() => {
                showNotification('Link copied to clipboard!');
            }).catch(() => {
                showNotification('Unable to share. Please copy the URL manually.', 'error');
            });
        }
    </script>
</body>

</html>