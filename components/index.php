<?php
session_start();
include_once '../backend/config.php';

// Fetch nutrition status data for analytics (latest records only)
$nutrition_status_query = "SELECT ns.status_name, COUNT(*) as count 
                          FROM (
                              SELECT DISTINCT nr1.child_id, nr1.status_id
                              FROM tbl_nutritrion_record nr1
                              INNER JOIN (
                                  SELECT child_id, MAX(date_recorded) as max_date
                                  FROM tbl_nutritrion_record
                                  GROUP BY child_id
                              ) nr2 ON nr1.child_id = nr2.child_id AND nr1.date_recorded = nr2.max_date
                          ) latest_records
                          JOIN tbl_nutrition_status ns ON latest_records.status_id = ns.status_id 
                          GROUP BY ns.status_name";
$nutrition_status_result = mysqli_query($conn, $nutrition_status_query);
$nutrition_data = [];
while ($row = mysqli_fetch_assoc($nutrition_status_result)) {
    $nutrition_data[] = $row;
}

// Fetch zones for filter
$zones_query = "SELECT DISTINCT zone_id, zone_name FROM tbl_barangay ORDER BY zone_name";
$zones_result = mysqli_query($conn, $zones_query);
$zones = [];
while ($row = mysqli_fetch_assoc($zones_result)) {
    $zones[] = $row;
}

// Fetch upcoming events
$events_query = "SELECT * FROM tbl_events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 6";
$events_result = mysqli_query($conn, $events_query);
$upcoming_events = [];
while ($row = mysqli_fetch_assoc($events_result)) {
    $upcoming_events[] = $row;
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../styles/modules.css">
    <link rel="stylesheet" href="../styles/general.css">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Home</title>
</head>

<body>
    <?php include_once './navbar.php'; ?>
    <main>
        <div class="banner-container">
            <div class="banner-content">
                <div class="slider-wrapper">
                    <div class="slider-container" id="sliderContainer">
                        <div class="slide">
                            <div class="img-con">
                                <img src="../assets/banner-1.avif" alt="Healthy Food">
                            </div>
                            <div class="content-text">
                                <h1>Welcome to NutritionTrack</h1>
                                <p>Your personal nutrition assistant. Get started by exploring our features and learn more about healthy eating habits.</p>
                                <a href="./login.php"><button>Login up</button></a>
                            </div>
                        </div>
                        <div class="slide">
                            <div class="img-con">
                                <img src="../assets/banner-2.avif" alt="Fresh Vegetables">
                            </div>
                            <div class="content-text">
                                <h1>Track Your Nutrition</h1>
                                <p>Monitor your daily intake and make informed decisions about your health with our comprehensive tracking tools.</p>
                                <a href="./login.php"><button>Get Started</button></a>
                            </div>
                        </div>
                        <div class="slide">
                            <div class="img-con">
                                <img src="../assets/banner-3.avif" alt="Meal Planning">
                            </div>
                            <div class="content-text">
                                <h1>Plan Your Meals</h1>
                                <p>
                                    We help you create balanced meal plans tailored to your dietary needs and preferences.
                                </p>
                                <a href="./login.php"><button>Start Planning</button></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-dots">
                    <div class="dot active" onclick="goToSlide(0)"></div>
                    <div class="dot" onclick="goToSlide(1)"></div>
                    <div class="dot" onclick="goToSlide(2)"></div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="analytics-container">
            <div class="analytics-child-container">
                <h1>
                    <i class="fa-solid fa-chart-pie"></i>
                    Nutrition Analytics
                </h1>
                <p class="subtitle">
                    View current nutrition status distribution in our community
                </p>
                <div class="analytics-content">
                    <div class="chart-section">
                        <div class="chart-filters">
                            <select id="nutritionYearFilter" class="filter-select">
                                <option value="all">All Years</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                            <select id="nutritionZoneFilter" class="filter-select">
                                <option value="all">All Zones</option>
                                <?php foreach ($zones as $zone): ?>
                                    <option value="<?php echo $zone['zone_id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="homeNutritionChart"></canvas>
                        </div>
                        <div class="chart-stats">
                            <div class="stat-item">
                                <div class="stat-number" id="totalChildren">0</div>
                                <div class="stat-label">Total Children</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="healthyChildren">0</div>
                                <div class="stat-label">Healthy</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="atRiskChildren">0</div>
                                <div class="stat-label">At Risk</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events Section -->
        <div class="bg-color">
            <div class="events-container">
                <div class="events-child-container">
                    <h1>
                        <i class="fa-solid fa-calendar-alt"></i>
                        Upcoming Events
                    </h1>
                    <p class="subtitle">
                        Don't miss out on important health and nutrition events in your community
                    </p>
                    <div class="events-grid">
                        <?php if (count($upcoming_events) > 0): ?>
                            <?php foreach ($upcoming_events as $event): ?>
                                <div class="event-card">
                                    <div class="event-date">
                                        <div class="date-day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                        <div class="date-month"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                                    </div>
                                    <div class="event-content">
                                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($event['description']); ?></p>
                                        <div class="event-time">
                                            <i class="fa-solid fa-clock"></i>
                                            <?php echo date('g:i A', strtotime($event['event_date'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-events">
                                <i class="fa-solid fa-calendar-times"></i>
                                <p>No upcoming events at the moment</p>
                                <small>Check back later for new events</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($upcoming_events) > 0){?>
                    <div class="event-btn">
                        <a href="./events.php" class="view-more-btn">
                            <button>View All Events</button>
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="anc-container">
            <div class="anc-child-container">
                <h1>
                    <i class="fa-solid fa-bullhorn"></i>
                    Announcements
                </h1>
                <p class="subtitle">
                    Stay tuned for upcoming features and updates!
                </p>
                <div class="anc-list">
                    <?php
                    $query_anc = "SELECT ta.username, tz.zone_name, tba.announcement_id, tba.content, tba.img_content, tba.title, tba.post_date FROM tbl_announcements tba JOIN tbl_user ta ON tba.user_id = ta.user_id JOIN tbl_barangay tz ON tba.zone_id = tz.zone_id ORDER BY tba.post_date DESC LIMIT 3";
                    $result_anc = $conn->query($query_anc);
                    if ($result_anc && $result_anc->num_rows > 0) {
                        while ($data_anc = $result_anc->fetch_assoc()) {
                    ?>
                            <div class="anc-item">
                                <div class="img-con">
                                    <img src="../assets/announcements/<?php echo $data_anc["img_content"]; ?>" alt="announcement-1">
                                </div>
                                <div class="description">
                                    <h2>
                                        <i class="fa-solid fa-circle-info"></i>
                                        <?php echo $data_anc["title"]; ?>
                                    </h2>
                                    <p>
                                        <?php echo $data_anc["content"]; ?>
                                    </p>
                                    <div class="view-more">
                                        <a href="./announcement.php">
                                            <button>View More</button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>

                </div>
                <div class="see-more">
                    <a href="./announcements.php">
                        <button>View All Announcements
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-color">
            <div class="modules-container">
                <div class="modules-child-container">
                    <h1>
                        <i class="fa-solid fa-puzzle-piece"></i>
                        Nutrition Modules
                    </h1>
                    <p class="subtitle">
                        Explore our comprehensive nutrition education modules
                    </p>
                    <div class="modules-grid">
                        <?php
                        $query_modules = "SELECT tm.*, ta.username FROM tbl_modules tm JOIN tbl_user ta ON tm.created_by = ta.user_id ORDER BY tm.posted_date DESC LIMIT 6";
                        $result_modules = $conn->query($query_modules);
                        if ($result_modules && $result_modules->num_rows > 0) {
                            while ($data_modules = $result_modules->fetch_assoc()) {
                        ?>
                                <div class="module-card">
                                    <div class="module-icon">
                                        <i class="fa-solid fa-book"></i>
                                    </div>
                                    <div class="module-content">
                                        <h3>
                                            <?php echo $data_modules["module_title"]; ?>
                                        </h3>
                                        <p>
                                            <?php echo $data_modules["module_content"]; ?>
                                        </p>
                                        <div class="module-meta">
                                            <span class="duration"><i class="fa-solid fa-clock"></i> Date Created: <?php
                                                                                                                    $formatted_date = date('M d, Y', strtotime($data_modules["posted_date"]));
                                                                                                                    echo $formatted_date;
                                                                                                                    ?></span>
                                        </div>
                                        <div class="module-action">
                                            <a href="./modules/basic-nutrition.php">
                                                <button class="start-btn">View Module</button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="see-all-modules">
                        <a href="./modules.php">
                            <button>View All Modules
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include_once './footer.php'; ?>

    <script src="../js/navbar.js"></script>
    <script>
        let currentSlide = 0;
        const totalSlides = 3;
        const sliderContainer = document.getElementById('sliderContainer');
        const dots = document.querySelectorAll('.dot');

        function updateSlider() {
            const translateX = -(currentSlide * 33.333);
            sliderContainer.style.transform = `translateX(${translateX}%)`;

            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }

        function goToSlide(slideIndex) {
            currentSlide = slideIndex;
            updateSlider();
        }

        setInterval(nextSlide, 5000);

        updateSlider();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Nutrition analytics chart
        let homeNutritionChart;
        const originalHomeNutritionData = <?php echo json_encode($nutrition_data); ?>;

        function initializeHomeNutritionChart(data) {
            const nutritionLabels = data.map(item => item.status_name);
            const nutritionValues = data.map(item => {
                const count = parseInt(item.count);
                return isNaN(count) ? 0 : count;
            });

            if (nutritionLabels.length === 0) {
                nutritionLabels.push('Normal Weight', 'Underweight', 'Severely Underweight');
                nutritionValues.push(0, 0, 0);
            }

            const nutritionCtx = document.getElementById('homeNutritionChart').getContext('2d');

            if (homeNutritionChart) {
                homeNutritionChart.destroy();
            }

            homeNutritionChart = new Chart(nutritionCtx, {
                type: 'bar',
                data: {
                    labels: nutritionLabels,
                    datasets: [{
                        data: nutritionValues,
                        backgroundColor: [
                            '#4CAF50',
                            '#FF9800',
                            '#f44336',
                            '#9C27B0',
                            '#2196F3'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Hide legend for bar chart
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.parsed.y; // Use .y for bar charts instead of just .parsed

                                    // Check for valid numbers and non-zero total
                                    if (isNaN(value) || isNaN(total) || total === 0) {
                                        return context.label + ': ' + (value || 0);
                                    }

                                    const percentage = ((value * 100) / total).toFixed(1);
                                    return context.label + ': ' + value + ' (' + percentage + '%)';
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
                        },
                        x: {
                            ticks: {
                                maxRotation: 45
                            }
                        }
                    }
                    // Remove cutout property - this is only for doughnut charts
                }
            });

            // Update statistics
            updateNutritionStats(data);
        }

        function updateNutritionStats(data) {
            const total = data.reduce((sum, item) => sum + parseInt(item.count), 0);
            const healthy = data.find(item => item.status_name === 'Normal Weight')?.count || 0;
            const atRisk = total - healthy;

            document.getElementById('totalChildren').textContent = total;
            document.getElementById('healthyChildren').textContent = healthy;
            document.getElementById('atRiskChildren').textContent = atRisk;
        }

        function filterHomeNutrition() {
            const yearFilter = document.getElementById('nutritionYearFilter').value;
            const zoneFilter = document.getElementById('nutritionZoneFilter').value;

            if (yearFilter === 'all' && zoneFilter === 'all') {
                initializeHomeNutritionChart(originalHomeNutritionData);
                return;
            }

            // Build query parameters
            const params = new URLSearchParams();
            if (yearFilter !== 'all') params.append('year', yearFilter);
            if (zoneFilter !== 'all') params.append('zone_id', zoneFilter);

            // Fetch filtered data via AJAX
            fetch(`../backend/filter_home_nutrition.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    initializeHomeNutritionChart(data);
                })
                .catch(error => {
                    console.error('Error filtering nutrition data:', error);
                    initializeHomeNutritionChart([]);
                });
        }

        // Event listeners
        document.getElementById('nutritionYearFilter').addEventListener('change', filterHomeNutrition);
        document.getElementById('nutritionZoneFilter').addEventListener('change', filterHomeNutrition);

        // Initialize chart on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeHomeNutritionChart(originalHomeNutritionData);
        });
    </script>
</body>

</html>