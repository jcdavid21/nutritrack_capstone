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
                    <?php 
                        $query_anc = "SELECT ta.username, tz.zone_name, tba.announcement_id, tba.content, tba.img_content, tba.title, tba.post_date FROM tbl_announcements tba JOIN tbl_user ta ON tba.user_id = ta.user_id JOIN tbl_barangay tz ON tba.zone_id = tz.zone_id ORDER BY tba.post_date DESC";
                        $result_anc = $conn->query($query_anc);
                        if ($result_anc && $result_anc->num_rows > 0) {
                            while ($data_anc = $result_anc->fetch_assoc()) {
                    ?>
                    <article class="announcement-card" 
                             data-category="programs" 
                             data-date="<?php echo htmlspecialchars($data_anc['post_date']); ?>" 
                             data-title="<?php echo htmlspecialchars($data_anc['title']); ?>" 
                             data-content="<?php echo htmlspecialchars($data_anc['content']); ?>">
                        <div class="card-header">
                            <div class="category-badge announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </div>
                            <div class="date-badge">
                                <i class="fa-solid fa-calendar"></i>
                                <?php 
                                    $formatted_date = date('M d, Y', strtotime($data_anc["post_date"]));
                                    echo $formatted_date;
                                ?>
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/announcements/<?php echo htmlspecialchars($data_anc['img_content']); ?>" alt="<?php echo htmlspecialchars($data_anc['title']); ?>">
                        </div>
                        <div class="card-content">
                            <h2><?php echo htmlspecialchars($data_anc['title']); ?></h2>
                            <p><?php echo htmlspecialchars(substr($data_anc['content'], 0, 150)) . (strlen($data_anc['content']) > 150 ? '...' : ''); ?></p>
                        </div>
                        <div class="card-actions">
                            <a href="./announcement-detail.php?id=<?php echo $data_anc['announcement_id']; ?>" class="read-more-btn">
                                Read More
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    <?php
                        } 
                    }
                    ?>
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
                this.searchTimeout = null;
                this.init();
            }

            init() {
                this.loadAnnouncements();
                this.setupEventListeners();
                this.applyInitialSort();
                this.displayAnnouncements();
                this.setupPagination();
            }

            loadAnnouncements() {
                const cards = document.querySelectorAll('.announcement-card');
                this.allAnnouncements = Array.from(cards).map(card => ({
                    element: card.cloneNode(true), // Clone to preserve original
                    category: card.getAttribute('data-category') || 'programs',
                    date: card.getAttribute('data-date'),
                    title: (card.getAttribute('data-title') || '').toLowerCase(),
                    content: (card.getAttribute('data-content') || '').toLowerCase(),
                    originalElement: card
                }));
                
                // Hide all original cards initially
                cards.forEach(card => card.style.display = 'none');
                
                this.filteredAnnouncements = [...this.allAnnouncements];
            }

            setupEventListeners() {
                // Search input with debouncing
                const searchInput = document.getElementById('searchInput');
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.filterAnnouncements();
                    }, 300); // 300ms delay for better performance
                });

                // Category filter
                const categoryFilter = document.getElementById('categoryFilter');
                categoryFilter.addEventListener('change', (e) => {
                    this.filterAnnouncements();
                });

                // Date filter
                const dateFilter = document.getElementById('dateFilter');
                dateFilter.addEventListener('change', (e) => {
                    this.sortAnnouncements();
                });

                // Pagination buttons
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

            applyInitialSort() {
                // Sort by latest first initially
                this.filteredAnnouncements.sort((a, b) => {
                    const dateA = new Date(a.date);
                    const dateB = new Date(b.date);
                    return dateB - dateA; // Latest first
                });
            }

            filterAnnouncements() {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
                const selectedCategory = document.getElementById('categoryFilter').value;

                this.filteredAnnouncements = this.allAnnouncements.filter(announcement => {
                    // Search match
                    const matchesSearch = searchTerm === '' || 
                                        announcement.title.includes(searchTerm) || 
                                        announcement.content.includes(searchTerm);
                    
                    // Category match
                    const matchesCategory = selectedCategory === 'all' || 
                                          announcement.category === selectedCategory;
                    
                    return matchesSearch && matchesCategory;
                });

                // Reset to first page when filtering
                this.currentPage = 1;
                
                // Apply current sorting
                this.sortAnnouncements();
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
                
                // Clear grid except for no results message
                const noResultsMsg = document.getElementById('noResultsMessage');
                grid.innerHTML = '';
                if (noResultsMsg) {
                    grid.appendChild(noResultsMsg);
                }

                // Check if there are any results
                if (this.filteredAnnouncements.length === 0) {
                    this.showNoResults();
                    this.hidePagination();
                    return;
                }

                this.hideNoResults();

                // Display current page announcements
                const currentPageAnnouncements = this.filteredAnnouncements.slice(startIndex, endIndex);
                currentPageAnnouncements.forEach(announcement => {
                    grid.appendChild(announcement.element);
                });

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
                        <div style="text-align: center; padding: 60px 20px; color: #666; grid-column: 1 / -1;">
                            <i class="fa-solid fa-search" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                            <h3 style="margin-bottom: 10px;">No announcements found</h3>
                            <p>Try adjusting your search terms or filters</p>
                        </div>
                    `;
                }
                
                noResultsMsg.style.display = 'block';
                if (!grid.contains(noResultsMsg)) {
                    grid.appendChild(noResultsMsg);
                }
            }

            hideNoResults() {
                const noResultsMsg = document.getElementById('noResultsMessage');
                if (noResultsMsg) {
                    noResultsMsg.style.display = 'none';
                }
            }

            hidePagination() {
                document.getElementById('pagination').style.display = 'none';
            }

            setupPagination() {
                const totalPages = Math.ceil(this.filteredAnnouncements.length / this.itemsPerPage);
                
                if (totalPages <= 1) {
                    this.hidePagination();
                    return;
                }

                document.getElementById('pagination').style.display = 'flex';
                this.renderPaginationNumbers(totalPages);
                this.updatePagination();
            }

            renderPaginationNumbers(totalPages) {
                const paginationNumbers = document.getElementById('paginationNumbers');
                paginationNumbers.innerHTML = '';

                // Always show first page
                this.createPageButton(1, paginationNumbers);

                if (totalPages <= 7) {
                    // Show all pages if 7 or fewer
                    for (let i = 2; i <= totalPages; i++) {
                        this.createPageButton(i, paginationNumbers);
                    }
                } else {
                    // Complex pagination for more than 7 pages
                    if (this.currentPage <= 4) {
                        // Show pages 2-5 and then dots + last page
                        for (let i = 2; i <= 5; i++) {
                            this.createPageButton(i, paginationNumbers);
                        }
                        this.createDots(paginationNumbers);
                        this.createPageButton(totalPages, paginationNumbers);
                    } else if (this.currentPage >= totalPages - 3) {
                        // Show first + dots + last 5 pages
                        this.createDots(paginationNumbers);
                        for (let i = totalPages - 4; i <= totalPages; i++) {
                            this.createPageButton(i, paginationNumbers);
                        }
                    } else {
                        // Show first + dots + current-1, current, current+1 + dots + last
                        this.createDots(paginationNumbers);
                        for (let i = this.currentPage - 1; i <= this.currentPage + 1; i++) {
                            this.createPageButton(i, paginationNumbers);
                        }
                        this.createDots(paginationNumbers);
                        this.createPageButton(totalPages, paginationNumbers);
                    }
                }
            }

            createPageButton(pageNum, container) {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'pagination-number';
                pageBtn.textContent = pageNum;
                
                if (pageNum === this.currentPage) {
                    pageBtn.classList.add('active');
                }

                pageBtn.addEventListener('click', () => {
                    this.currentPage = pageNum;
                    this.displayAnnouncements();
                    this.updatePagination();
                });

                container.appendChild(pageBtn);
            }

            createDots(container) {
                const dots = document.createElement('span');
                dots.className = 'pagination-dots';
                dots.textContent = '...';
                container.appendChild(dots);
            }

            updatePagination() {
                const totalPages = Math.ceil(this.filteredAnnouncements.length / this.itemsPerPage);
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');

                // Update button states
                prevBtn.disabled = this.currentPage === 1;
                nextBtn.disabled = this.currentPage === totalPages || totalPages === 0;

                // Update active page number
                document.querySelectorAll('.pagination-number').forEach(btn => {
                    btn.classList.remove('active');
                    if (parseInt(btn.textContent) === this.currentPage) {
                        btn.classList.add('active');
                    }
                });

                // Re-render pagination if needed (for complex pagination)
                if (totalPages > 7) {
                    this.renderPaginationNumbers(totalPages);
                }

                // Smooth scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // Initialize when DOM is loaded with additional safety checks
        document.addEventListener('DOMContentLoaded', () => {
            // Wait a bit more to ensure all elements are rendered
            setTimeout(() => {
                console.log('Initializing AnnouncementManager...');
                const cards = document.querySelectorAll('.announcement-card');
                console.log('Found cards:', cards.length);
                
                if (cards.length === 0) {
                    console.error('No announcement cards found! Check your PHP query and HTML structure.');
                    return;
                }
                
                new AnnouncementManager();
            }, 100);
        });
    </script>
</body>

</html>