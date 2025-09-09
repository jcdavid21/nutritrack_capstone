<?php
session_start();
include_once '../backend/config.php';

// Get event ID from URL parameter
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id <= 0) {
    header('Location: ./events.php');
    exit();
}

// Fetch event details
$query_event = "SELECT * FROM tbl_events WHERE event_id = ?";
$stmt = $conn->prepare($query_event);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result_event = $stmt->get_result();

if ($result_event->num_rows === 0) {
    header('Location: ./events.php');
    exit();
}

$event = $result_event->fetch_assoc();
$event_date = new DateTime($event['event_date']);
$current_date = new DateTime();
$is_upcoming = $event_date > $current_date;

// Fetch related events (other events excluding current one)
$query_related = "SELECT * FROM tbl_events WHERE event_id != ? ORDER BY event_date DESC LIMIT 3";
$stmt_related = $conn->prepare($query_related);
$stmt_related->bind_param("i", $event_id);
$stmt_related->execute();
$result_related = $stmt_related->get_result();
$related_events = [];
while ($row = $result_related->fetch_assoc()) {
    $related_events[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/event-detail.css">
    <link rel="stylesheet" href="../styles/general.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?php echo htmlspecialchars($event['title']); ?> - NutritionTrack</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($event['description'], 0, 155)); ?>">
</head>

<body>
    <?php include_once './navbar.php'; ?>
    
    <main>
        <div class="hero-section">
            <div class="hero-content">
                <div class="breadcrumb">
                    <a href="./home.php">Home</a>
                    <span><i class="fa-solid fa-chevron-right"></i></span>
                    <a href="./events.php">Events</a>
                    <span><i class="fa-solid fa-chevron-right"></i></span>
                    <span><?php echo htmlspecialchars($event['title']); ?></span>
                </div>
                
                <div class="event-header">
                    <div class="event-status <?php echo $is_upcoming ? 'upcoming' : 'past'; ?>">
                        <i class="fa-solid fa-<?php echo $is_upcoming ? 'clock' : 'check'; ?>"></i>
                        <?php echo $is_upcoming ? 'Upcoming Event' : 'Past Event'; ?>
                    </div>
                    
                    <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                    
                    <div class="event-meta">
                        <div class="meta-item">
                            <i class="fa-solid fa-calendar"></i>
                            <span><?php echo $event_date->format('F j, Y'); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-clock"></i>
                            <span><?php echo $event_date->format('g:i A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="event-detail-section">
            <div class="event-container">
                <div class="event-content">
                    <div class="main-content">
                        <div class="event-description">
                            <h2>
                                <i class="fa-solid fa-info-circle"></i>
                                Event Description
                            </h2>
                            <div class="description-content">
                                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                            </div>
                        </div>

                        <div class="event-details-grid">
                            <div class="detail-card">
                                <div class="detail-header">
                                    <i class="fa-solid fa-calendar-day"></i>
                                    <h3>Date & Time</h3>
                                </div>
                                <div class="detail-content">
                                    <p><strong>Date:</strong> <?php echo $event_date->format('l, F j, Y'); ?></p>
                                    <p><strong>Time:</strong> <?php echo $event_date->format('g:i A'); ?></p>
                                    <?php if ($is_upcoming): ?>
                                        <div class="countdown" id="countdown">
                                            <div class="countdown-item">
                                                <span class="countdown-value" id="days">0</span>
                                                <span class="countdown-label">Days</span>
                                            </div>
                                            <div class="countdown-item">
                                                <span class="countdown-value" id="hours">0</span>
                                                <span class="countdown-label">Hours</span>
                                            </div>
                                            <div class="countdown-item">
                                                <span class="countdown-value" id="minutes">0</span>
                                                <span class="countdown-label">Minutes</span>
                                            </div>
                                            <div class="countdown-item">
                                                <span class="countdown-value" id="seconds">0</span>
                                                <span class="countdown-label">Seconds</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="detail-card">
                                <div class="detail-header">
                                    <i class="fa-solid fa-users"></i>
                                    <h3>Event Type</h3>
                                </div>
                                <div class="detail-content">
                                    <p>Health & Nutrition Event</p>
                                    <p><strong>Category:</strong> Community Program</p>
                                    <p><strong>Target:</strong> All Community Members</p>
                                </div>
                            </div>

                            <?php if ($is_upcoming): ?>
                            <div class="detail-card highlight">
                                <div class="detail-header">
                                    <i class="fa-solid fa-bell"></i>
                                    <h3>Reminder</h3>
                                </div>
                                <div class="detail-content">
                                    <p>Don't miss this important event! Mark your calendar and set a reminder.</p>
                                    <button class="reminder-btn" onclick="setReminder()">
                                        <i class="fa-solid fa-bell"></i>
                                        Set Reminder
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="event-actions">
                            <a href="./events.php" class="back-btn">
                                <i class="fa-solid fa-arrow-left"></i>
                                Back to Events
                            </a>
                            <?php if ($is_upcoming): ?>
                            <button class="share-btn" onclick="shareEvent()">
                                <i class="fa-solid fa-share"></i>
                                Share Event
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="sidebar">
                        <div class="event-summary-card">
                            <h3>
                                <i class="fa-solid fa-info"></i>
                                Quick Info
                            </h3>
                            <div class="summary-item">
                                <i class="fa-solid fa-calendar"></i>
                                <span><?php echo $event_date->format('M j, Y'); ?></span>
                            </div>
                            <div class="summary-item">
                                <i class="fa-solid fa-clock"></i>
                                <span><?php echo $event_date->format('g:i A'); ?></span>
                            </div>
                            <div class="summary-item">
                                <i class="fa-solid fa-tag"></i>
                                <span><?php echo $is_upcoming ? 'Upcoming' : 'Completed'; ?></span>
                            </div>
                        </div>

                        <?php if (count($related_events) > 0): ?>
                        <div class="related-events-card">
                            <h3>
                                <i class="fa-solid fa-calendar-alt"></i>
                                Other Events
                            </h3>
                            <div class="related-events-list">
                                <?php foreach ($related_events as $related_event): 
                                    $related_date = new DateTime($related_event['event_date']);
                                    $related_is_upcoming = $related_date > $current_date;
                                ?>
                                <a href="./event-detail.php?id=<?php echo $related_event['event_id']; ?>" class="related-event-item">
                                    <div class="related-event-date">
                                        <span class="date-day"><?php echo $related_date->format('d'); ?></span>
                                        <span class="date-month"><?php echo $related_date->format('M'); ?></span>
                                    </div>
                                    <div class="related-event-info">
                                        <h4><?php echo htmlspecialchars($related_event['title']); ?></h4>
                                        <p><?php echo htmlspecialchars(substr($related_event['description'], 0, 80)) . '...'; ?></p>
                                        <span class="related-event-status <?php echo $related_is_upcoming ? 'upcoming' : 'past'; ?>">
                                            <?php echo $related_is_upcoming ? 'Upcoming' : 'Past'; ?>
                                        </span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once './footer.php'; ?>

    <script src="../js/navbar.js"></script>
    <script>
        // Countdown timer for upcoming events
        <?php if ($is_upcoming): ?>
        function updateCountdown() {
            const eventDate = new Date('<?php echo $event['event_date']; ?>').getTime();
            const now = new Date().getTime();
            const distance = eventDate - now;

            if (distance < 0) {
                document.getElementById('countdown').innerHTML = '<p>Event has started!</p>';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = days;
            document.getElementById('hours').textContent = hours;
            document.getElementById('minutes').textContent = minutes;
            document.getElementById('seconds').textContent = seconds;
        }

        // Update countdown every second
        updateCountdown();
        setInterval(updateCountdown, 1000);
        <?php endif; ?>

        // Set reminder function
        function setReminder() {
            const eventTitle = '<?php echo addslashes($event['title']); ?>';
            const eventDate = '<?php echo $event['event_date']; ?>';
            
            if ('Notification' in window) {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        // Calculate time until event (for demonstration, set reminder for 1 hour before)
                        const reminderTime = new Date(eventDate).getTime() - (60 * 60 * 1000);
                        const now = new Date().getTime();
                        
                        if (reminderTime > now) {
                            setTimeout(function() {
                                new Notification('Event Reminder', {
                                    body: `${eventTitle} starts in 1 hour!`,
                                    icon: '../assets/logo.png'
                                });
                            }, reminderTime - now);
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Reminder Set',
                                text: `You will be reminded about ${eventTitle} in 1 hour.`,
                            });
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Event Soon',
                                text: 'The event is starting in less than an hour!',
                            });
                        }
                    }
                });
            } else {
                // Fallback: Add to calendar (simplified)
                const calendarUrl = `data:text/calendar;charset=utf8,BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTART:${new Date(eventDate).toISOString().replace(/[-:]/g, '').split('.')[0]}Z
SUMMARY:${eventTitle}
DESCRIPTION:<?php echo addslashes($event['description']); ?>
END:VEVENT
END:VCALENDAR`;
                
                const link = document.createElement('a');
                link.href = calendarUrl;
                link.download = 'event-reminder.ics';
                link.click();
            }
        }

        // Share event function
        function shareEvent() {
            const eventTitle = '<?php echo addslashes($event['title']); ?>';
            const eventUrl = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: eventTitle,
                    text: 'Check out this upcoming health event!',
                    url: eventUrl
                }).then(() => {
                    console.log('Event shared successfully');
                }).catch((error) => {
                    console.log('Error sharing event:', error);
                    fallbackShare();
                });
            } else {
                fallbackShare();
            }
        }

        function fallbackShare() {
            const eventTitle = '<?php echo addslashes($event['title']); ?>';
            const eventUrl = window.location.href;
            const shareText = `Check out this event: ${eventTitle} - ${eventUrl}`;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shareText).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Link Copied',
                        text: 'Event link copied to clipboard!',
                    });
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = shareText;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                Swal.fire({
                    icon: 'success',
                    title: 'Link Copied',
                    text: 'Event link copied to clipboard!',
                });
            }
        }

        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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
    </script>
</body>

</html>