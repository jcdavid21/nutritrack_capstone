<?php
session_start();
include_once '../backend/config.php';
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
                    <div class="anc-item">
                        <div class="img-con">
                            <img src="../assets/announcements/anc-1.png" alt="announcement-1">
                        </div>
                        <div class="description">
                            <h2>
                                <i class="fa-solid fa-circle-info"></i>
                                Feeding Program
                            </h2>
                            <p>
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo voluptates obcaecati nobis odit ipsam enim consectetur quis est nisi error illo sapiente vitae numquam quos, veniam non ullam reiciendis natus?
                            </p>
                            <div class="view-more">
                                <a href="./announcement.php">
                                    <button>View More</button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="anc-item">
                        <div class="img-con">
                            <img src="../assets/announcements/anc-1.png" alt="announcement-1">
                        </div>
                        <div class="description">
                            <h2>
                                <i class="fa-solid fa-circle-info"></i>
                                Feeding Program
                            </h2>
                            <p>
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo voluptates obcaecati nobis odit ipsam enim consectetur quis est nisi error illo sapiente vitae numquam quos, veniam non ullam reiciendis natus?
                            </p>
                            <div class="view-more">
                                <a href="./announcement.php">
                                    <button>View More</button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="anc-item">
                        <div class="img-con">
                            <img src="../assets/announcements/anc-1.png" alt="announcement-1">
                        </div>
                        <div class="description">
                            <h2>
                                <i class="fa-solid fa-circle-info"></i>
                                Feeding Program
                            </h2>
                            <p>
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo voluptates obcaecati nobis odit ipsam enim consectetur quis est nisi error illo sapiente vitae numquam quos, veniam non ullam reiciendis natus?
                            </p>
                            <div class="view-more">
                                <a href="./announcement.php">
                                    <button>View More</button>
                                </a>
                            </div>
                        </div>
                    </div>
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
                    <div class="module-card">
                        <div class="module-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="module-content">
                            <h3>Basic Nutrition</h3>
                            <p>Learn the fundamentals of nutrition, including macronutrients, micronutrients, and their roles in maintaining good health.</p>
                            <div class="module-meta">
                                <span class="duration"><i class="fa-solid fa-clock"></i> Date Created: Apr 20, 2024</span>
                            </div>
                            <div class="module-action">
                                <a href="./modules/basic-nutrition.php">
                                    <button class="start-btn">View Module</button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="module-content">
                            <h3>Meal Planning</h3>
                            <p>Master the art of creating balanced, nutritious meal plans that fit your lifestyle and dietary preferences.</p>
                            <div class="module-meta">
                                <span class="duration"><i class="fa-solid fa-clock"></i> Date Created: Apr 20, 2024</span>
                            </div>
                            <div class="module-action">
                                <a href="./modules/meal-planning.php">
                                    <button class="start-btn">View Module</button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="module-content">
                            <h3>Calorie Management</h3>
                            <p>Understand calorie balance, energy expenditure, and how to manage your daily caloric intake effectively.</p>
                            <div class="module-meta">
                                <span class="duration"><i class="fa-solid fa-clock"></i> Date Created: Apr 20, 2024</span>
                            </div>
                            <div class="module-action">
                                <a href="./modules/calorie-management.php">
                                    <button class="start-btn">View Module</button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="module-content">
                            <h3>Sports Nutrition</h3>
                            <p>Discover how to fuel your body for optimal athletic performance and recovery through proper nutrition.</p>
                            <div class="module-meta">
                                <span class="duration"><i class="fa-solid fa-clock"></i> Date Created: Apr 20, 2024</span>
                            </div>
                            <div class="module-action">
                                <a href="./modules/sports-nutrition.php">
                                    <button class="start-btn">View Module</button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="module-content">
                            <h3>Heart-Healthy Eating</h3>
                            <p>Learn about foods and eating patterns that promote cardiovascular health and reduce disease risk.</p>
                            <div class="module-meta">
                                <span class="duration"><i class="fa-solid fa-clock"></i> Date Created: Apr 20, 2024</span>
                            </div>
                            <div class="module-action">
                                <a href="./modules/heart-healthy.php">
                                    <button class="start-btn">View Module</button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="module-content">
                            <h3>Plant-Based Nutrition</h3>
                            <p>Explore the benefits and principles of plant-based eating and how to meet all nutritional needs.</p>
                            <div class="module-meta">
                                <span class="duration"><i class="fa-solid fa-clock"></i> Date Created: Apr 20, 2024</span>
                            </div>
                            <div class="module-action">
                                <a href="./modules/plant-based.php">
                                    <button class="start-btn">View Module</button>
                                </a>
                            </div>
                        </div>
                    </div>
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
</body>

</html>