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
$count_query = "SELECT COUNT(*) as total FROM tbl_events te 
    INNER JOIN tbl_barangay tz ON tz.zone_id = te.zone_id";
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

    <title>Event Scheduler</title>
</head>

<body>
    <?php include_once "./sidebar.php" ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fa-solid fa-calendar-days"></i>
                Event Scheduler
            </h1>
            <p class="dashboard-subtitle">Schedule and manage community events across different zones.</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="view-toggle">
                <button id="calendarViewBtn" class="active">
                    <i class="fa-solid fa-calendar"></i>
                    Calendar View
                </button>
                <button id="tableViewBtn">
                    <i class="fa-solid fa-table"></i>
                    Table View
                </button>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                <i class="fa-solid fa-plus"></i>
                Add New Event
            </button>
        </div>

        <div id="calendarView" class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-nav">
                    <button id="prevMonth">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button id="todayBtn">Today</button>
                    <button id="nextMonth">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                <h2 class="calendar-title" id="calendarTitle">March 2024</h2>
            </div>
            <div class="calendar-grid" id="calendarGrid">

            </div>
        </div>


        <div id="tableView" class="table-container" style="display: none;">
            <div class="table-header">
                <div class="table-actions">
                    <div class="d-flex align-items-center gap-3">
                        <h3 class="mb-0">Events List</h3>
                        <span class="badge bg-secondary" id="totalEventsCount"><?php echo $total_items; ?> Events</span>
                    </div>
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search events...">
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="announcements-table" id="eventsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Zone</th>
                            <th>Description</th>
                            <th>Event Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventsTableBody">

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


    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addEventModalLabel">
                        <i class="fa-solid fa-plus"></i>
                        Schedule New Event
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="content-input mb-3">
                                <label for="addEventTitle" class="form-label">
                                    <i class="fa-solid fa-calendar-check"></i>
                                    Event Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="addEventTitle" placeholder="Enter event title">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="content-input mb-3">
                                <label for="addEventZone" class="form-label">
                                    <i class="fa-solid fa-map-marker-alt"></i>
                                    Barangay Zone <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="addEventZone">
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
                        <div class="col-md-6">
                            <div class="content-input mb-3">
                                <label for="addEventDateTime" class="form-label">
                                    <i class="fa-solid fa-clock"></i>
                                    Event Date & Time <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control datetime-input" id="addEventDateTime">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="content-input mb-3">
                                <label for="addEventDescription" class="form-label">
                                    <i class="fa-solid fa-file-lines"></i>
                                    Description <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="addEventDescription" rows="4" placeholder="Enter event description..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addEventBtn"
                        onclick="addEvent()">
                        <i class="fa-solid fa-calendar-plus"></i>
                        Schedule Event
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
        class EventCalendar {
            constructor() {
                this.currentDate = new Date();
                this.events = [];
                this.currentPage = 1;
                this.itemsPerPage = 5;
                this.totalEvents = 0;
                this.init();
            }

            async init() {
                this.setupEventListeners();
                await this.loadEvents();
                this.renderCalendar();
                this.loadTableView();
            }

            setupEventListeners() {

                document.getElementById('calendarViewBtn').addEventListener('click', () => this.showCalendarView());
                document.getElementById('tableViewBtn').addEventListener('click', () => this.showTableView());


                document.getElementById('prevMonth').addEventListener('click', () => this.previousMonth());
                document.getElementById('nextMonth').addEventListener('click', () => this.nextMonth());
                document.getElementById('todayBtn').addEventListener('click', () => this.goToToday());


                document.getElementById('searchInput').addEventListener('input', (e) => this.searchEvents(e.target.value));
            }

            async loadEvents() {
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.itemsPerPage,
                        search: document.getElementById('searchInput')?.value || ''
                    });

                    const response = await fetch(`./get_data/get_events.php?${params}`);
                    const data = await response.json();
                    this.events = data.events || [];
                    this.totalEvents = data.total || 0;

                    // Update total events display
                    document.getElementById('totalEventsCount').textContent = `${this.totalEvents} Events`;
                } catch (error) {
                    console.error('Error loading events:', error);
                }
            }

            renderCalendar() {
                const year = this.currentDate.getFullYear();
                const month = this.currentDate.getMonth();

                // Update calendar title
                document.getElementById('calendarTitle').textContent =
                    this.currentDate.toLocaleDateString('en-US', {
                        month: 'long',
                        year: 'numeric'
                    });

                // Get first day of month and number of days
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const today = new Date();

                let html = '';

                // Day headers
                const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                dayHeaders.forEach(day => {
                    html += `<div class="calendar-day-header">${day}</div>`;
                });

                // Previous month days
                const prevMonthDays = new Date(year, month, 0).getDate();
                for (let i = firstDay - 1; i >= 0; i--) {
                    html += `<div class="calendar-day other-month">
                        <div class="day-number">${prevMonthDays - i}</div>
                    </div>`;
                }

                // Current month days
                for (let day = 1; day <= daysInMonth; day++) {
                    const currentDay = new Date(year, month, day);
                    const isToday = currentDay.toDateString() === today.toDateString();
                    const dayEvents = this.getEventsForDay(currentDay);

                    html += `<div class="calendar-day ${isToday ? 'today' : ''}" data-date="${year}-${(month+1).toString().padStart(2,'0')}-${day.toString().padStart(2,'0')}">
                        <div class="day-number">${day}</div>`;

                    dayEvents.forEach(event => {
                        html += `<div class="event-item" title="${event.title}">${event.title}</div>`;
                    });

                    html += '</div>';
                }

                // Next month days
                const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
                const remainingCells = totalCells - (firstDay + daysInMonth);
                for (let day = 1; day <= remainingCells; day++) {
                    html += `<div class="calendar-day other-month">
                        <div class="day-number">${day}</div>
                    </div>`;
                }

                document.getElementById('calendarGrid').innerHTML = html;
            }

            getEventsForDay(date) {
                return this.events.filter(event => {
                    const eventDate = new Date(event.event_date);
                    return eventDate.toDateString() === date.toDateString();
                });
            }

            loadTableView() {
                const tbody = document.getElementById('eventsTableBody');

                let html = '';
                if (this.events.length === 0) {
                    html = `<tr class="no-data">
            <td colspan="7">
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-days"></i>
                    <h3>No events found</h3>
                    <p>Start by scheduling your first event</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fa-solid fa-plus"></i>
                        Schedule Event
                    </button>
                </div>
            </td>
        </tr>`;
                } else {
                    this.events.forEach(event => {
                        const eventDate = new Date(event.event_date);
                        const now = new Date();
                        const status = this.getEventStatus(eventDate, now);
                        const formattedDate = eventDate.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        html += `<tr data-event-id="${event.event_id}">
                <td class="id-cell">#${event.event_id.toString().padStart(3, '0')}</td>
                <td class="title-cell">
                    <div class="title-content">
                        <span class="title">${event.title}</span>
                    </div>
                </td>
                <td class="zone-cell">
                    <span class="zone-badge">${event.zone_name}</span>
                </td>
                <td class="content-cell">
                    <div class="content-preview" title="${event.description}">
                        ${event.description.length > 50 ? event.description.substring(0, 50) + '...' : event.description}
                    </div>
                </td>
                <td class="date-cell">
                    <div class="date-info">
                        <span class="date">${formattedDate}</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge status-${status.class}">${status.text}</span>
                </td>
                <td class="actions-cell">
                    <div class="action-buttons">
                        <button class="btn-action btn-edit" title="Edit" onclick="editEvent(${event.event_id})">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <button class="btn-action btn-delete" title="Delete" onclick="deleteEvent(${event.event_id})">
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

            getEventStatus(eventDate, now) {
                const daysDiff = Math.ceil((eventDate - now) / (1000 * 60 * 60 * 24));

                if (daysDiff < 0) {
                    return {
                        class: 'completed',
                        text: 'Completed'
                    };
                } else if (daysDiff === 0) {
                    return {
                        class: 'ongoing',
                        text: 'Today'
                    };
                } else if (daysDiff <= 7) {
                    return {
                        class: 'upcoming',
                        text: 'This Week'
                    };
                } else {
                    return {
                        class: 'upcoming',
                        text: 'Upcoming'
                    };
                }
            }

            updatePagination() {
                const totalPages = Math.ceil(this.totalEvents / this.itemsPerPage);
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
                            onclick="calendar.changePage(${this.currentPage - 1})">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                `;

                // Page numbers
                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    html += `<span class="page-number ${i === this.currentPage ? 'active' : ''}" 
                             onclick="calendar.changePage(${i})">${i}</span>`;
                }

                html += `
                    <button class="btn-pagination" ${this.currentPage >= totalPages ? 'disabled' : ''} 
                            onclick="calendar.changePage(${this.currentPage + 1})">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                `;

                container.innerHTML = html;
            }

            updateShowingInfo() {
                const startItem = (this.currentPage - 1) * this.itemsPerPage + 1;
                const endItem = Math.min(this.currentPage * this.itemsPerPage, this.totalEvents);
                document.getElementById('showingInfo').textContent =
                    `Showing ${startItem} to ${endItem} of ${this.totalEvents} entries`;
            }

            async changePage(page) {
                const totalPages = Math.ceil(this.totalEvents / this.itemsPerPage);
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    await this.loadEvents();
                    this.loadTableView();
                    this.renderCalendar(); // Update calendar as well
                }
            }

            showCalendarView() {
                document.getElementById('calendarView').style.display = 'block';
                document.getElementById('tableView').style.display = 'none';
                document.getElementById('calendarViewBtn').classList.add('active');
                document.getElementById('tableViewBtn').classList.remove('active');
            }

            showTableView() {
                document.getElementById('calendarView').style.display = 'none';
                document.getElementById('tableView').style.display = 'block';
                document.getElementById('tableViewBtn').classList.add('active');
                document.getElementById('calendarViewBtn').classList.remove('active');
                this.loadTableView();
            }

            previousMonth() {
                this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                this.renderCalendar();
            }

            nextMonth() {
                this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                this.renderCalendar();
            }

            goToToday() {
                this.currentDate = new Date();
                this.renderCalendar();
            }

            async searchEvents(query) {
                this.currentPage = 1; // Reset to first page
                await this.loadEvents(); // This will now include the search parameter
                this.loadTableView();
            }
        }

        // Initialize calendar
        let calendar;
        document.addEventListener('DOMContentLoaded', function() {
            calendar = new EventCalendar();
        });

        // Global functions for event actions
        async function editEvent(eventId) {
            const event = calendar.events.find(e => e.event_id == eventId);
            if (!event) return;

            // Create and show edit modal
            const editModalHtml = `
                <div class="modal fade" id="editEventModal${eventId}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5">
                                    <i class="fa-solid fa-edit"></i>
                                    Edit Event
                                </h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="content-input mb-3">
                                            <label class="form-label">
                                                <i class="fa-solid fa-calendar-check"></i>
                                                Event Title <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="editEventTitle${eventId}" value="${event.title}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="content-input mb-3">
                                            <label class="form-label">
                                                <i class="fa-solid fa-map-marker-alt"></i>
                                                Barangay Zone <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="editEventZone${eventId}">
                                                <option value="">Select Zone</option>
                                                <?php
                                                $query_zones = "SELECT * FROM tbl_barangay ORDER BY zone_name";
                                                $result_zones = mysqli_query($conn, $query_zones);
                                                if ($result_zones && $result_zones->num_rows > 0) {
                                                    while ($zone = $result_zones->fetch_assoc()) {
                                                ?>
                                                        <option value="<?php echo $zone['zone_id']; ?>" >
                                                            <?php echo htmlspecialchars($zone['zone_name']); ?>
                                                        </option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="content-input mb-3">
                                            <label class="form-label">
                                                <i class="fa-solid fa-clock"></i>
                                                Event Date & Time <span class="text-danger">*</span>
                                            </label>
                                            <input type="datetime-local" class="form-control datetime-input" id="editEventDateTime${eventId}" value="${new Date(event.event_date).toISOString().slice(0, 16)}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="content-input mb-3">
                                            <label class="form-label">
                                                <i class="fa-solid fa-file-lines"></i>
                                                Description <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="editEventDescription${eventId}" rows="4">${event.description}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="updateEvent(${eventId})">
                                    <i class="fa-solid fa-save"></i>
                                    Update Event
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to DOM
            document.body.insertAdjacentHTML('beforeend', editModalHtml);

            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById(`editEventModal${eventId}`));
            editModal.show();

            document.getElementById(`editEventZone${eventId}`).value = event.zone_id;

            // Remove modal from DOM when closed
            document.getElementById(`editEventModal${eventId}`).addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        async function updateEvent(eventId) {
            const title = document.getElementById(`editEventTitle${eventId}`).value.trim();
            const zoneId = document.getElementById(`editEventZone${eventId}`).value;
            const datetime = document.getElementById(`editEventDateTime${eventId}`).value;
            const description = document.getElementById(`editEventDescription${eventId}`).value.trim();

            if (!title || !zoneId || !datetime || !description) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.'
                });
                return;
            }

            // Show loading state
            const updateButton = document.querySelector(`#editEventModal${eventId} .btn-primary`);
            const originalText = updateButton.innerHTML;
            updateButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
            updateButton.disabled = true;

            $.ajax({
                url: '../backend/admin/update_event.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    event_id: eventId,
                    title: title,
                    zone_id: zoneId,
                    event_date: datetime,
                    description: description
                },
                success: function(result) {
                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Event updated successfully.'
                        }).then(() => {
                            // Close modal
                            bootstrap.Modal.getInstance(document.getElementById(`editEventModal${eventId}`)).hide();

                            // Reload data
                            calendar.loadEvents().then(() => {
                                calendar.renderCalendar();
                                calendar.loadTableView();
                            });
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to update event.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        xhr,
                        status,
                        error
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating the event. Please try again.'
                    });
                },
                complete: function() {
                    // Restore button state
                    updateButton.innerHTML = originalText;
                    updateButton.disabled = false;
                }
            });
        }

        async function deleteEvent(eventId) {
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
                const deleteButton = document.querySelector(`tr[data-event-id="${eventId}"] .btn-delete`);
                const originalContent = deleteButton.innerHTML;
                deleteButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                deleteButton.disabled = true;

                $.ajax({
                    url: '../backend/admin/delete_event.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        event_id: eventId
                    },
                    success: function(deleteResult) {
                        if (deleteResult.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Your event has been deleted.'
                            }).then(() => {
                                // Reload data
                                calendar.loadEvents().then(() => {
                                    calendar.renderCalendar();
                                    calendar.loadTableView();
                                });

                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: deleteResult.message || 'Failed to delete event.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {
                            xhr,
                            status,
                            error
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the event. Please try again.'
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

        async function addEvent() {
            const title = document.getElementById('addEventTitle').value.trim();
            const zoneId = document.getElementById('addEventZone').value;
            const datetime = document.getElementById('addEventDateTime').value;
            const description = document.getElementById('addEventDescription').value.trim();

            if (!title || !zoneId || !datetime || !description) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields.'
                });
                return;
            }

            // Show loading state
            const addButton = document.getElementById('addEventBtn');
            const originalText = addButton.innerHTML;
            addButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Scheduling...';
            addButton.disabled = true;

            const self = this; // Store reference to 'this' for use in callbacks

            $.ajax({
                url: '../backend/admin/add_event.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    title: title,
                    zone_id: zoneId,
                    event_date: datetime,
                    description: description
                },
                success: function(result) {
                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Event scheduled successfully.'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to schedule event.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        xhr,
                        status,
                        error
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while scheduling the event. Please try again.'
                    });
                },
                complete: function() {
                    // Restore button state
                    addButton.innerHTML = originalText;
                    addButton.disabled = false;
                }
            });
        }

        // Set minimum datetime to current date/time
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const minDateTime = now.toISOString().slice(0, 16);
            document.getElementById('addEventDateTime').setAttribute('min', minDateTime);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>

</html>