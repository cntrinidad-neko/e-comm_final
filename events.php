<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - AlDrive</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 1rem;
        }
        .modal-overlay.show { display: flex; }
        .modal-card {
            background: #fff;
            width: 100%;
            max-width: 650px;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalFadeIn 0.25s ease;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .modal-body { padding: 1.25rem; }
        .modal-close {
            background: transparent;
            border: none;
            font-size: 1.8rem;
            line-height: 1;
            cursor: pointer;
            color: #333;
        }
        .modal-close:hover { color: var(--primary-color); }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(15px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

$events = [];
$eventsTableExists = false;

$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
if ($checkTable && mysqli_num_rows($checkTable) > 0) {
    $eventsTableExists = true;
    $eventsQuery = mysqli_query($conn, "SELECT * FROM events WHERE status = 'active' ORDER BY event_date ASC, id DESC");
    if ($eventsQuery) {
        while ($row = mysqli_fetch_assoc($eventsQuery)) {
            $events[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'date' => $row['event_date'],
                'description' => $row['description'],
                'location' => $row['location'],
                'type' => $row['type'] ?: 'event',
                'contactName' => $row['contact_name'] ?: 'AMF Representative',
                'contactPhone' => $row['contact_phone'] ?: '+63 123 456 7890',
                'contactPerson' => $row['contact_person'] ?: 'AMF Secretariat (info@albanfoundation.org)'
            ];
        }
    }
}
?>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-hands-helping"></i>
                <span>AlDrive</span>
            </div>

            <ul class="nav-menu" id="navMenu">
                <a href="index.php">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="donate.php">Donate</a>
                <?php endif; ?>
                <a href="about.php">About Us</a>
                <a href="events.php">Events</a>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php else: ?>
                    <a href="user/dashboard.php">Dashboard</a>
                    <a href="actions/logout.php">Logout</a>
                <?php endif; ?>
            </ul>

            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <section style="padding: 3rem 0;">
        <div class="container">
            <h2 class="section-title">Upcoming Events & Calendar</h2>
            <p style="text-align: center; color: var(--text-light); margin-bottom: 3rem; max-width: 800px; margin-left: auto; margin-right: auto;">
                Join us for our upcoming events, volunteer activities, and community gatherings. Your participation makes a difference!
            </p>

            <?php if (!$eventsTableExists): ?>
                <div class="dashboard-table" style="padding: 1.5rem; text-align:center; color:#991b1b;">
                    Events table not found. Please set up the database first.
                </div>
            <?php endif; ?>

            <div class="events-list" id="eventsList"></div>

            <div style="background: var(--bg-white); padding: 2rem; border-radius: 12px; box-shadow: var(--shadow); margin-top: 3rem;">
                <h3 style="margin-bottom: 1.5rem; text-align: center;">Event Calendar</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;" id="calendarView"></div>
            </div>
        </div>
    </section>

    <div class="modal-overlay" id="eventModal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="eventModalTitle">
            <div class="modal-header">
                <h3 id="eventModalTitle" style="margin:0;">Event Details</h3>
                <button type="button" class="modal-close" id="eventModalClose" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body" id="eventModalBody"></div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>AlDrive</h4>
                    <p>Alban Memorial Foundation Inc.<br>Making a difference, one donation at a time.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="advocacies.php">Advocacies</a></li>
                        <li><a href="events.php">Events</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> info@albanfoundation.org</li>
                        <li><i class="fas fa-phone"></i> +63 123 456 7890</li>
                        <li><i class="fas fa-map-marker-alt"></i> Quezon City, Philippines</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Alban Memorial Foundation Inc. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const allEvents = <?php echo json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>.map(event => ({
                ...event,
                date: new Date(event.date + 'T00:00:00')
            }));

            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const eventsList = document.getElementById('eventsList');
            const calendarView = document.getElementById('calendarView');
            const eventModal = document.getElementById('eventModal');
            const eventModalBody = document.getElementById('eventModalBody');
            const eventModalClose = document.getElementById('eventModalClose');

            function formatDate(date) {
                return date.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            function openEventModal(event) {
                if (!eventModal || !eventModalBody) return;

                const typeLabel = (event.type || 'event').toString().toUpperCase();

                eventModalBody.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap: 0.9rem;">
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                            <span style="display:inline-block; padding:0.25rem 0.6rem; border-radius:999px; background:#eef2ff; color:var(--primary-color); font-weight:700; font-size:0.8rem;">
                                ${typeLabel}
                            </span>
                            <span style="color: var(--text-light); font-size: 0.9rem;">
                                <i class="fas fa-calendar"></i> ${formatDate(event.date)}
                            </span>
                        </div>

                        <h3 style="margin:0;">${event.title}</h3>

                        <p style="margin:0; color: var(--text-light);">
                            ${event.description}
                        </p>

                        <div style="display:grid; gap:0.65rem; margin-top:0.25rem;">
                            <div style="color: var(--text-dark);">
                                <i class="fas fa-map-marker-alt" style="color: var(--primary-color); width:18px;"></i>
                                ${event.location}
                            </div>
                            <div style="color: var(--text-dark);">
                                <i class="fas fa-user" style="color: var(--primary-color); width:18px;"></i>
                                <strong>Person to look for:</strong> ${event.contactName || 'AMF Representative'}
                            </div>
                            <div style="color: var(--text-dark);">
                                <i class="fas fa-phone" style="color: var(--primary-color); width:18px;"></i>
                                <strong>Phone:</strong> ${event.contactPhone || '+63 123 456 7890'}
                            </div>
                            <div style="color: var(--text-dark);">
                                <i class="fas fa-envelope" style="color: var(--primary-color); width:18px;"></i>
                                <strong>Email/Team:</strong> ${event.contactPerson || 'AMF Secretariat (info@albanfoundation.org)'}
                            </div>
                        </div>
                    </div>
                `;

                eventModal.classList.add('show');
                eventModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeEventModal() {
                if (!eventModal) return;
                eventModal.classList.remove('show');
                eventModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            if (eventModalClose) {
                eventModalClose.addEventListener('click', closeEventModal);
            }

            if (eventModal) {
                eventModal.addEventListener('click', function (e) {
                    if (e.target === eventModal) {
                        closeEventModal();
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeEventModal();
                }
            });

            if (eventsList) {
                if (allEvents.length === 0) {
                    eventsList.innerHTML = `
                        <div class="dashboard-table" style="padding: 1.5rem; text-align:center; color: var(--text-light);">
                            No active events available right now.
                        </div>
                    `;
                } else {
                    eventsList.innerHTML = allEvents.map(event => {
                        const date = event.date;
                        return `
                            <div class="event-card" style="cursor: pointer;" data-event-id="${event.id}">
                                <div class="event-date">
                                    <div class="event-day">${date.getDate()}</div>
                                    <div class="event-month">${months[date.getMonth()]}</div>
                                </div>
                                <div class="event-details">
                                    <h3>${event.title}</h3>
                                    <p>${event.description}</p>
                                    <p style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--primary-color); line-height: 1.7;">
                                        <i class="fas fa-map-marker-alt"></i> ${event.location}
                                        | <i class="fas fa-clock"></i> ${formatDate(date)}
                                        | <i class="fas fa-user"></i> ${event.contactName || 'AMF Representative'}
                                        | <i class="fas fa-phone"></i> ${event.contactPhone || '+63 123 456 7890'}
                                    </p>
                                    <p style="margin-top: 0.25rem; font-size: 0.85rem; color: var(--text-light);">
                                        Click to view event details.
                                    </p>
                                </div>
                            </div>
                        `;
                    }).join('');

                    eventsList.querySelectorAll('[data-event-id]').forEach(card => {
                        card.addEventListener('click', function () {
                            const id = parseInt(this.getAttribute('data-event-id'));
                            const selectedEvent = allEvents.find(event => event.id === id);
                            if (selectedEvent) {
                                openEventModal(selectedEvent);
                            }
                        });
                    });
                }
            }

            if (calendarView) {
                if (allEvents.length === 0) {
                    calendarView.innerHTML = `
                        <div style="grid-column: 1 / -1; text-align:center; color: var(--text-light);">
                            No calendar entries yet.
                        </div>
                    `;
                } else {
                    calendarView.innerHTML = allEvents.map(event => {
                        const date = event.date;
                        return `
                            <button type="button" class="calendar-card" data-event-id="${event.id}" style="text-align: center; padding: 1rem; background: var(--bg-light); border-radius: 8px; border: 1px solid var(--border-color); cursor: pointer;">
                                <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color);">${date.getDate()}</div>
                                <div style="color: var(--text-light); margin-bottom: 0.5rem;">${months[date.getMonth()]} ${date.getFullYear()}</div>
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">${event.title}</div>
                                <div style="font-size: 0.85rem; color: var(--text-light);">${event.description}</div>
                            </button>
                        `;
                    }).join('');

                    calendarView.querySelectorAll('[data-event-id]').forEach(card => {
                        card.addEventListener('click', function () {
                            const id = parseInt(this.getAttribute('data-event-id'));
                            const selectedEvent = allEvents.find(event => event.id === id);
                            if (selectedEvent) {
                                openEventModal(selectedEvent);
                            }
                        });
                    });
                }
            }
        });
    </script>
</body>
</html>