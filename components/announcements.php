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
    <link rel="stylesheet" href="../styles/announcements.css">
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>All Announcements - NutritionTrack</title>
</head>

<body>
    <?php include_once './navbar.php'; ?>
    
    <main>
        <div class="hero-section">
            <div class="hero-content">
                <div class="breadcrumb">
                    <a href="./home.php">Home</a>
                    <span><i class="fa-solid fa-chevron-right"></i></span>
                    <span>Announcements</span>
                </div>
                <h1>
                    <i class="fa-solid fa-bullhorn"></i>
                    All Announcements
                </h1>
                <p>Stay updated with the latest news, updates, and programs from NutritionTrack</p>
            </div>
        </div>

        <div class="filter-section">
            <div class="filter-container">
                <div class="search-box">
                    <input type="text" placeholder="Search announcements..." id="searchInput">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="filter-options">
                    <select id="categoryFilter">
                        <option value="all">All Categories</option>
                        <option value="programs">Programs</option>
                        <option value="updates">Updates</option>
                        <option value="events">Events</option>
                        <option value="news">News</option>
                    </select>
                    <select id="dateFilter">
                        <option value="latest">Latest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="announcements-section">
            <div class="announcements-container">
                <div class="announcements-grid" id="announcementsGrid">
                    <article class="announcement-card" data-category="programs" data-date="2024-03-15" data-title="Community Feeding Program Launch" data-content="We're excited to announce the launch of our new community feeding program designed to provide nutritious meals to families in need. This initiative aims to combat malnutrition and promote healthy eating habits within our community.">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                March 15, 2024
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/anc-1.png" alt="Feeding Program">
                        </div>
                        <div class="card-content">
                            <h2>Community Feeding Program Launch</h2>
                            <p>We're excited to announce the launch of our new community feeding program designed to provide nutritious meals to families in need. This initiative aims to combat malnutrition and promote healthy eating habits within our community.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=1" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>

                    <article class="announcement-card" data-category="updates" data-date="2024-03-12" data-title="NutritionTrack App Update 2.1" data-content="Our latest app update includes new features for better meal tracking, improved user interface, and enhanced nutrition analytics. Download the update to experience improved performance and new functionalities.">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                March 12, 2024
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/anc-2.png" alt="App Update">
                        </div>
                        <div class="card-content">
                            <h2>NutritionTrack App Update 2.1</h2>
                            <p>Our latest app update includes new features for better meal tracking, improved user interface, and enhanced nutrition analytics. Download the update to experience improved performance and new functionalities.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=2" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>

                    <article class="announcement-card" data-category="events" data-date="2024-03-10" data-title="Nutrition Workshop Series" data-content="Join our comprehensive nutrition workshop series featuring expert nutritionists and dietitians. Learn about balanced diets, meal planning, and healthy cooking techniques in interactive sessions.">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                March 10, 2024
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/anc-3.png" alt="Nutrition Workshop">
                        </div>
                        <div class="card-content">
                            <h2>Nutrition Workshop Series</h2>
                            <p>Join our comprehensive nutrition workshop series featuring expert nutritionists and dietitians. Learn about balanced diets, meal planning, and healthy cooking techniques in interactive sessions.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=3" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>

                    <article class="announcement-card" data-category="news" data-date="2024-03-08" data-title="Partnership with Local Health Centers" data-content="We're proud to announce our new partnership with local health centers to provide comprehensive nutrition counseling and support services to community members seeking healthier lifestyles.">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                March 8, 2024
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/anc-4.png" alt="Partnership News">
                        </div>
                        <div class="card-content">
                            <h2>Partnership with Local Health Centers</h2>
                            <p>We're proud to announce our new partnership with local health centers to provide comprehensive nutrition counseling and support services to community members seeking healthier lifestyles.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=4" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>

                    <!-- Announcement Item 5 -->
                    <article class="announcement-card" data-category="programs" data-date="2024-03-05" data-title="School Nutrition Education Program" data-content="Introducing our new school-based nutrition education program aimed at teaching children about healthy eating habits, food groups, and the importance of balanced nutrition from an early age.">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                March 5, 2024
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/anc-5.png" alt="School Program">
                        </div>
                        <div class="card-content">
                            <h2>School Nutrition Education Program</h2>
                            <p>Introducing our new school-based nutrition education program aimed at teaching children about healthy eating habits, food groups, and the importance of balanced nutrition from an early age.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=5" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>

                    <article class="announcement-card" data-category="updates" data-date="2024-03-01" data-title="New Recipe Database Launch" data-content="Explore our newly launched recipe database featuring over 1000 healthy recipes with detailed nutritional information, cooking instructions, and dietary classifications to support your meal planning.">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                March 1, 2024
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/anc-6.png" alt="Platform Update">
                        </div>
                        <div class="card-content">
                            <h2>New Recipe Database Launch</h2>
                            <p>Explore our newly launched recipe database featuring over 1000 healthy recipes with detailed nutritional information, cooking instructions, and dietary classifications to support your meal planning.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=6" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>

                    <article class="announcement-card" data-category="events" data-date="2024-02-28" data-title="Health Screening Event" data-content="Join us for a free community health screening event where you can check your BMI, blood pressure, and receive nutritional guidance from our certified professionals.">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                February 28, 2024
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/anc-1.png" alt="Health Screening">
                        </div>
                        <div class="card-content">
                            <h2>Health Screening Event</h2>
                            <p>Join us for a free community health screening event where you can check your BMI, blood pressure, and receive nutritional guidance from our certified professionals.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=7" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>

                    <article class="announcement-card" data-category="news" data-date="2024-02-25" data-title="New Research Findings" data-content="Recent studies show significant improvements in community health metrics following our nutrition programs. Read about the positive impact we're making together.">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                February 25, 2024
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/anc-2.png" alt="Research">
                        </div>
                        <div class="card-content">
                            <h2>New Research Findings</h2>
                            <p>Recent studies show significant improvements in community health metrics following our nutrition programs. Read about the positive impact we're making together.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=8" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                </div>

                <div class="pagination" id="pagination">
                    <button class="pagination-btn prev" id="prevBtn">
                        <i class="fa-solid fa-chevron-left"></i>
                        Previous
                    </button>
                    <div class="pagination-numbers" id="paginationNumbers">
                    </div>
                    <button class="pagination-btn next" id="nextBtn">
                        Next
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php include_once './footer.php'; ?>

    <script src="../js/navbar.js"></script>
    <script>
        class AnnouncementManager {
            constructor() {
                this.allAnnouncements = [];
                this.filteredAnnouncements = [];
                this.currentPage = 1;
                this.itemsPerPage = 6;
                this.init();
            }

            init() {
                this.loadAnnouncements();
                this.setupEventListeners();
                this.displayAnnouncements();
                this.setupPagination();
            }

            loadAnnouncements() {
                const cards = document.querySelectorAll('.announcement-card');
                this.allAnnouncements = Array.from(cards).map(card => ({
                    element: card,
                    category: card.getAttribute('data-category'),
                    date: card.getAttribute('data-date'),
                    title: card.getAttribute('data-title').toLowerCase(),
                    content: card.getAttribute('data-content').toLowerCase()
                }));
                this.filteredAnnouncements = [...this.allAnnouncements];
            }

            setupEventListeners() {

                const searchInput = document.getElementById('searchInput');
                searchInput.addEventListener('input', (e) => {
                    this.filterAnnouncements();
                });


                const categoryFilter = document.getElementById('categoryFilter');
                categoryFilter.addEventListener('change', (e) => {
                    this.filterAnnouncements();
                });


                const dateFilter = document.getElementById('dateFilter');
                dateFilter.addEventListener('change', (e) => {
                    this.sortAnnouncements();
                });


                document.getElementById('prevBtn').addEventListener('click', () => {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.displayAnnouncements();
                        this.updatePagination();
                    }
                });

                document.getElementById('nextBtn').addEventListener('click', () => {
                    const totalPages = Math.ceil(this.filteredAnnouncements.length / this.itemsPerPage);
                    if (this.currentPage < totalPages) {
                        this.currentPage++;
                        this.displayAnnouncements();
                        this.updatePagination();
                    }
                });
            }

            filterAnnouncements() {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                const selectedCategory = document.getElementById('categoryFilter').value;

                this.filteredAnnouncements = this.allAnnouncements.filter(announcement => {
                    const matchesSearch = announcement.title.includes(searchTerm) || 
                                        announcement.content.includes(searchTerm);
                    const matchesCategory = selectedCategory === 'all' || 
                                          announcement.category === selectedCategory;
                    
                    return matchesSearch && matchesCategory;
                });

                this.currentPage = 1;
                this.sortAnnouncements();
                this.displayAnnouncements();
                this.setupPagination();
            }

            sortAnnouncements() {
                const sortOrder = document.getElementById('dateFilter').value;
                
                this.filteredAnnouncements.sort((a, b) => {
                    const dateA = new Date(a.date);
                    const dateB = new Date(b.date);
                    
                    if (sortOrder === 'latest') {
                        return dateB - dateA;
                    } else {
                        return dateA - dateB;
                    }
                });

                this.displayAnnouncements();
            }

            displayAnnouncements() {
                const grid = document.getElementById('announcementsGrid');
                const startIndex = (this.currentPage - 1) * this.itemsPerPage;
                const endIndex = startIndex + this.itemsPerPage;
                

                this.allAnnouncements.forEach(announcement => {
                    announcement.element.style.display = 'none';
                });


                this.filteredAnnouncements.slice(startIndex, endIndex).forEach(announcement => {
                    announcement.element.style.display = 'block';
                });


                if (this.filteredAnnouncements.length === 0) {
                    this.showNoResults();
                } else {
                    this.hideNoResults();
                }

                this.updatePagination();
            }

            showNoResults() {
                const grid = document.getElementById('announcementsGrid');
                let noResultsMsg = document.getElementById('noResultsMessage');
                
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noResultsMessage';
                    noResultsMsg.className = 'no-results-message';
                    noResultsMsg.innerHTML = `
                        <div style="text-align: center; padding: 60px 20px; color: #666;">
                            <i class="fa-solid fa-search" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                            <h3>No announcements found</h3>
                            <p>Try adjusting your search terms or filters</p>
                        </div>
                    `;
                    grid.appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            }

            hideNoResults() {
                const noResultsMsg = document.getElementById('noResultsMessage');
                if (noResultsMsg) {
                    noResultsMsg.style.display = 'none';
                }
            }

            setupPagination() {
                const totalPages = Math.ceil(this.filteredAnnouncements.length / this.itemsPerPage);
                const paginationNumbers = document.getElementById('paginationNumbers');
                
                paginationNumbers.innerHTML = '';

                if (totalPages <= 1) {
                    document.getElementById('pagination').style.display = 'none';
                    return;
                }

                document.getElementById('pagination').style.display = 'flex';


                for (let i = 1; i <= Math.min(totalPages, 5); i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.className = 'pagination-number';
                    pageBtn.textContent = i;
                    
                    if (i === this.currentPage) {
                        pageBtn.classList.add('active');
                    }

                    pageBtn.addEventListener('click', () => {
                        this.currentPage = i;
                        this.displayAnnouncements();
                        this.updatePagination();
                    });

                    paginationNumbers.appendChild(pageBtn);
                }


                if (totalPages > 5) {
                    const dots = document.createElement('span');
                    dots.className = 'pagination-dots';
                    dots.textContent = '...';
                    paginationNumbers.appendChild(dots);

                    const lastPageBtn = document.createElement('button');
                    lastPageBtn.className = 'pagination-number';
                    lastPageBtn.textContent = totalPages;
                    
                    if (totalPages === this.currentPage) {
                        lastPageBtn.classList.add('active');
                    }

                    lastPageBtn.addEventListener('click', () => {
                        this.currentPage = totalPages;
                        this.displayAnnouncements();
                        this.updatePagination();
                    });

                    paginationNumbers.appendChild(lastPageBtn);
                }

                this.updatePagination();
            }

            updatePagination() {
                const totalPages = Math.ceil(this.filteredAnnouncements.length / this.itemsPerPage);
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');

                prevBtn.disabled = this.currentPage === 1;
                nextBtn.disabled = this.currentPage === totalPages || totalPages === 0;

                document.querySelectorAll('.pagination-number').forEach(btn => {
                    btn.classList.remove('active');
                    if (parseInt(btn.textContent) === this.currentPage) {
                        btn.classList.add('active');
                    }
                });

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            new AnnouncementManager();
        });
    </script>
</body>

</html>