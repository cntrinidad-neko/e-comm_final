<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlDrive - Alban Memorial Foundation Inc.</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .home-alert {
            max-width: 1100px;
            margin: 12px auto 0;
            padding: 12px 16px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
        }

        .home-alert.success {
            background: #ecfdf5;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .home-alert.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .featured-campaigns {
            padding: 4rem 0;
        }

        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            align-items: stretch;
        }

        .campaign-card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .campaign-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12);
        }

        .campaign-image {
            min-height: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
        }

        .campaign-image .main-icon {
            font-size: 4.25rem;
            color: #fff;
            margin-bottom: 1rem;
            text-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .campaign-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            padding: 0.7rem 1.1rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.95rem;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.10);
        }

        .campaign-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .campaign-title {
            font-size: 1.7rem;
            margin-bottom: 0.85rem;
            color: var(--text-dark);
            line-height: 1.3;
        }

        .campaign-description {
            color: var(--text-light);
            line-height: 1.85;
            margin: 0;
            flex: 1;
        }

        .campaign-link {
            display: inline-block;
            margin-top: 1.25rem;
            color: var(--primary-color);
            font-weight: 700;
            text-decoration: none;
        }

        .campaign-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .campaign-image {
                min-height: 190px;
                padding: 1.75rem 1rem;
            }

            .campaign-image .main-icon {
                font-size: 3.5rem;
            }

            .campaign-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/db.php';
?>

<?php if (isset($_GET['success'])): ?>
    <p id="success-alert" class="home-alert success">
        <?php echo htmlspecialchars($_GET['success']); ?>
    </p>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <p id="error-alert" class="home-alert error">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </p>
<?php endif; ?>

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

    <section class="hero">
        <div class="hero-content">
            <h1>Support Our Advocacies</h1>
            <p>Alban Memorial Foundation Inc. serves communities through education, shelter, welfare, and disaster relief. Your single donation supports all our advocacies.</p>
            <div class="hero-buttons">
                <a href="advocacies.php" class="btn btn-primary">View Our Advocacies</a>
            </div>
        </div>
    </section>

    <section class="featured-campaigns">
        <div class="container">
            <h2 class="section-title">Our Advocacies</h2>

            <div class="campaigns-grid">
                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                        <i class="fas fa-graduation-cap main-icon" aria-hidden="true"></i>
                        <span class="campaign-badge">
                            <i class="fas fa-book" aria-hidden="true"></i>
                            Education
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Education & Scholarships</h3>
                        <p class="campaign-description">
                            Providing access to quality education for underprivileged youth and children through scholarships,
                            school supplies, and learning support.
                        </p>
                        <a href="advocacies.php" class="campaign-link">Learn more</a>
                    </div>
                </div>

                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #10b981, #34d399);">
                        <i class="fas fa-house-chimney main-icon" aria-hidden="true"></i>
                        <span class="campaign-badge">
                            <i class="fas fa-house" aria-hidden="true"></i>
                            Shelter
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Shelter & Housing</h3>
                        <p class="campaign-description">
                            Offering safe shelter and housing assistance for families in need, especially those affected by
                            disasters and unsafe living conditions.
                        </p>
                        <a href="advocacies.php" class="campaign-link">Learn more</a>
                    </div>
                </div>

                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                        <i class="fas fa-box-open main-icon" aria-hidden="true"></i>
                        <span class="campaign-badge">
                            <i class="fas fa-shirt" aria-hidden="true"></i>
                            Basic Needs
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Clothing & Basic Needs</h3>
                        <p class="campaign-description">
                            Distributing clothing, food, and essential items to families and individuals in our partner communities.
                        </p>
                        <a href="advocacies.php" class="campaign-link">Learn more</a>
                    </div>
                </div>

                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #06b6d4, #22d3ee);">
                        <i class="fas fa-kit-medical main-icon" aria-hidden="true"></i>
                        <span class="campaign-badge">
                            <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                            Relief
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Disaster Relief & Community Welfare</h3>
                        <p class="campaign-description">
                            Responding to emergencies and supporting long-term recovery and welfare programs for affected communities.
                        </p>
                        <a href="advocacies.php" class="campaign-link">Learn more</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
        setTimeout(function () {
            const success = document.getElementById('success-alert');
            const error = document.getElementById('error-alert');

            if (success) {
                success.style.transition = 'opacity 0.5s ease';
                success.style.opacity = '0';
                setTimeout(() => success.remove(), 500);
            }

            if (error) {
                error.style.transition = 'opacity 0.5s ease';
                error.style.opacity = '0';
                setTimeout(() => error.remove(), 500);
            }
        }, 3000);
    </script>
</body>
</html>