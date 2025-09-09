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
    <link rel="stylesheet" href="../styles/event.css">
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>All Events - NutritionTrack</title>
</head>

<body>
    <?php include_once './navbar.php'; ?>
    
    <main>
        <div class="hero-section">
            <div class="hero-content">
                <div class="breadcrumb">
                    <a href="./home.php">Home</a>
                    <span><i class="fa-solid fa-chevron-right"></i></span>
                    <span>Events</span>
                </div>
                <h1>
                    <i class="fa-solid fa-calendar-alt"></i>
                    All Events
                </h1>
                <p>Discover upcoming health programs, workshops, and community events designed to improve nutrition and wellness</p>
            </div>
        </div>

        <div class="filter-section">
            <div class="filter-container">
                <div class="search-box">
                    <input type="text" placeholder="Search events..." id="searchInput">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="filter-options">
                    <select id="statusFilter">
                        <option value="all">All Events</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="past">Past Events</option>
                    </select>
                    <select id="dateFilter">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="events-section">
            <div class="events-container">
                <div class="events-grid" id="eventsGrid">
                    <?php 
                        $query_events = "SELECT * FROM tbl_events ORDER BY event_date DESC";
                        $result_events = $conn->query($query_events);
                        if ($result_events && $result_events->num_rows > 0) {
                            while ($data_event = $result_events->fetch_assoc()) {
                                $event_date = new DateTime($data_event['event_date']);
                                $current_date = new DateTime();
                                $is_upcoming = $event_date > $current_date;
                                $status = $is_upcoming ? 'upcoming' : 'past';
                    ?>
                    <article class="event-card" 
                             data-status="<?php echo $status; ?>" 
                             data-date="<?php echo htmlspecialchars($data_event['event_date']); ?>" 
                             data-title="<?php echo htmlspecialchars($data_event['title']); ?>" 
                             data-description="<?php echo htmlspecialchars($data_event['description']); ?>">
                        <div class="card-header">
                            <div class="status-badge <?php echo $status; ?>">
                                <i class="fa-solid fa-<?php echo $is_upcoming ? 'clock' : 'check'; ?>"></i>
                                <?php echo $is_upcoming ? 'Upcoming' : 'Completed'; ?>
                            </div>
                            <div class="date-badge">
                                <div class="date-day"><?php echo $event_date->format('d'); ?></div>
                                <div class="date-month"><?php echo $event_date->format('M'); ?></div>
                            </div>
                        </div>
                        <div class="card-content">
                            <h2><?php echo htmlspecialchars($data_event['title']); ?></h2>
                            <p><?php echo htmlspecialchars(substr($data_event['description'], 0, 150)) . (strlen($data_event['description']) > 150 ? '...' : ''); ?></p>
                            <div class="event-details">
                                <div class="detail-item">
                                    <i class="fa-solid fa-calendar"></i>
                                    <span><?php echo $event_date->format('F j, Y'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fa-solid fa-clock"></i>
                                    <span><?php echo $event_date->format('g:i A'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="./event-detail.php?id=<?php echo $data_event['event_id']; ?>" class="view-details-btn">
                                View Details
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    <?php
                            }
                        } else {
                    ?>
                    <div class="no-events-message">
                        <i class="fa-solid fa-calendar-times"></i>
                        <h3>No events found</h3>
                        <p>There are currently no events scheduled. Check back later for updates!</p>
                    </div>
                    <?php
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
        class EventManager {
            constructor() {
                this.allEvents = [];
                this.filteredEvents = [];
                this.currentPage = 1;
                this.itemsPerPage = 6;
                this.searchTimeout = null;
                this.init();
            }

            init() {
                this.loadEvents();
                this.setupEventListeners();
                this.applyInitialSort();
                this.displayEvents();
                this.setupPagination();
            }

            loadEvents() {
                const cards = document.querySelectorAll('.event-card');
                this.allEvents = Array.from(cards).map(card => ({
                    element: card.cloneNode(true),
                    status: card.getAttribute('data-status') || 'upcoming',
                    date: card.getAttribute('data-date'),
                    title: (card.getAttribute('data-title') || '').toLowerCase(),
                    description: (card.getAttribute('data-description') || '').toLowerCase(),
                    originalElement: card
                }));
                
                cards.forEach(card => card.style.display = 'none');
                this.filteredEvents = [...this.allEvents];
            }

            setupEventListeners() {
                const searchInput = document.getElementById('searchInput');
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.filterEvents();
                    }, 300);
                });

                const statusFilter = document.getElementById('statusFilter');
                statusFilter.addEventListener('change', (e) => {
                    this.filterEvents();
                });

                const dateFilter = document.getElementById('dateFilter');
                dateFilter.addEventListener('change', (e) => {
                    this.sortEvents();
                });

                document.getElementById('prevBtn').addEventListener('click', () => {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.displayEvents();
                        this.updatePagination();
                    }
                });

                document.getElementById('nextBtn').addEventListener('click', () => {
                    const totalPages = Math.ceil(this.filteredEvents.length / this.itemsPerPage);
                    if (this.currentPage < totalPages) {
                        this.currentPage++;
                        this.displayEvents();
                        this.updatePagination();
                    }
                });
            }

            applyInitialSort() {
                this.filteredEvents.sort((a, b) => {
                    const dateA = new Date(a.date);
                    const dateB = new Date(b.date);
                    return dateB - dateA;
                });
            }

            filterEvents() {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
                const selectedStatus = document.getElementById('statusFilter').value;

                this.filteredEvents = this.allEvents.filter(event => {
                    const matchesSearch = searchTerm === '' || 
                                        event.title.includes(searchTerm) || 
                                        event.description.includes(searchTerm);
                    
                    const matchesStatus = selectedStatus === 'all' || 
                                         event.status === selectedStatus;
                    
                    return matchesSearch && matchesStatus;
                });

                this.currentPage = 1;
                this.sortEvents();
            }

            sortEvents() {
                const sortOrder = document.getElementById('dateFilter').value;
                
                this.filteredEvents.sort((a, b) => {
                    const dateA = new Date(a.date);
                    const dateB = new Date(b.date);
                    
                    if (sortOrder === 'newest') {
                        return dateB - dateA;
                    } else {
                        return dateA - dateB;
                    }
                });

                this.displayEvents();
            }

            displayEvents() {
                const grid = document.getElementById('eventsGrid');
                const startIndex = (this.currentPage - 1) * this.itemsPerPage;
                const endIndex = startIndex + this.itemsPerPage;
                
                const noResultsMsg = document.getElementById('noResultsMessage');
                grid.innerHTML = '';
                if (noResultsMsg) {
                    grid.appendChild(noResultsMsg);
                }

                if (this.filteredEvents.length === 0) {
                    this.showNoResults();
                    this.hidePagination();
                    return;
                }

                this.hideNoResults();

                const currentPageEvents = this.filteredEvents.slice(startIndex, endIndex);
                currentPageEvents.forEach(event => {
                    grid.appendChild(event.element);
                });

                this.updatePagination();
            }

            showNoResults() {
                const grid = document.getElementById('eventsGrid');
                let noResultsMsg = document.getElementById('noResultsMessage');
                
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noResultsMessage';
                    noResultsMsg.className = 'no-results-message';
                    noResultsMsg.innerHTML = `
                        <div style="text-align: center; padding: 60px 20px; color: #666; grid-column: 1 / -1;">
                            <i class="fa-solid fa-search" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                            <h3 style="margin-bottom: 10px;">No events found</h3>
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
                const totalPages = Math.ceil(this.filteredEvents.length / this.itemsPerPage);
                
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

                this.createPageButton(1, paginationNumbers);

                if (totalPages <= 7) {
                    for (let i = 2; i <= totalPages; i++) {
                        this.createPageButton(i, paginationNumbers);
                    }
                } else {
                    if (this.currentPage <= 4) {
                        for (let i = 2; i <= 5; i++) {
                            this.createPageButton(i, paginationNumbers);
                        }
                        this.createDots(paginationNumbers);
                        this.createPageButton(totalPages, paginationNumbers);
                    } else if (this.currentPage >= totalPages - 3) {
                        this.createDots(paginationNumbers);
                        for (let i = totalPages - 4; i <= totalPages; i++) {
                            this.createPageButton(i, paginationNumbers);
                        }
                    } else {
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
                    this.displayEvents();
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
                const totalPages = Math.ceil(this.filteredEvents.length / this.itemsPerPage);
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

                if (totalPages > 7) {
                    this.renderPaginationNumbers(totalPages);
                }

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const cards = document.querySelectorAll('.event-card');
                if (cards.length === 0) {
                    console.error('No event cards found!');
                    return;
                }
                new EventManager();
            }, 100);
        });
    </script>
</body>

</html>