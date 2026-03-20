<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advocacies - AlDrive</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .advocacy-hero {
            text-align: center;
            margin-bottom: 3rem;
        }

        .advocacy-hero p {
            color: var(--text-light);
            max-width: 800px;
            margin: 0 auto;
        }

        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }

        .campaign-card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .campaign-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 40px rgba(0,0,0,0.12);
        }

        .campaign-image {
            min-height: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            position: relative;
            text-align: center;
        }

        .campaign-image .main-icon {
            font-size: 4.5rem;
            color: #fff;
            margin-bottom: 1rem;
            text-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .advocacy-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.18);
            color: #fff;
            padding: 0.7rem 1.1rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.95rem;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 6px 18px rgba(0,0,0,0.10);
        }

        .advocacy-badge i {
            font-size: 1rem;
        }

        .campaign-content {
            padding: 1.6rem;
        }

        .campaign-title {
            font-size: 1.7rem;
            margin-bottom: 0.9rem;
            color: var(--text-dark);
        }

        .campaign-description {
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 1.2rem;
        }

        .initiative-title {
            display: inline-block;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.65rem;
        }

        .initiative-list {
            margin: 0;
            padding-left: 1.25rem;
            color: var(--text-light);
            line-height: 1.9;
        }

        .initiative-list li::marker {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .campaign-image {
                min-height: 190px;
                padding: 1.75rem 1rem;
            }

            .campaign-image .main-icon {
                font-size: 3.6rem;
            }

            .campaign-title {
                font-size: 1.45rem;
            }
        }
    </style>
</head>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
            <div class="advocacy-hero">
                <h2 class="section-title">Our Advocacies</h2>
                <p>
                    Alban Memorial Foundation Inc. is committed to addressing critical issues in our communities.
                    Learn more about the causes we support and how you can get involved.
                </p>
            </div>

            <div class="campaigns-grid">
                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                        <i class="fas fa-graduation-cap main-icon" aria-hidden="true"></i>
                        <span class="advocacy-badge">
                            <i class="fas fa-book" aria-hidden="true"></i> Education
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Education & Scholarships</h3>
                        <p class="campaign-description">
                            We believe education is a fundamental right. Our advocacy focuses on providing access to quality
                            education for underprivileged youth and children, supporting schools in underserved areas,
                            and providing scholarships and educational materials.
                        </p>
                        <strong class="initiative-title">Key Initiatives:</strong>
                        <ul class="initiative-list">
                            <li>Scholarship programs for youth</li>
                            <li>School supply distribution</li>
                            <li>Educational support services</li>
                            <li>Learning materials provision</li>
                        </ul>
                    </div>
                </div>

                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #10b981, #34d399);">
                        <i class="fas fa-house-chimney main-icon" aria-hidden="true"></i>
                        <span class="advocacy-badge">
                            <i class="fas fa-house" aria-hidden="true"></i> Shelter
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Shelter & Housing</h3>
                        <p class="campaign-description">
                            Providing safe shelter and housing assistance for families in need, particularly those affected
                            by disasters or living in unsafe conditions. We work to ensure every family has a place to call home.
                        </p>
                        <strong class="initiative-title">Key Initiatives:</strong>
                        <ul class="initiative-list">
                            <li>Housing assistance programs</li>
                            <li>Shelter for disaster victims</li>
                            <li>Rental support</li>
                            <li>Home repair assistance</li>
                        </ul>
                    </div>
                </div>

                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                        <i class="fas fa-box-open main-icon" aria-hidden="true"></i>
                        <span class="advocacy-badge">
                            <i class="fas fa-shirt" aria-hidden="true"></i> Basic Needs
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Clothing & Basic Needs</h3>
                        <p class="campaign-description">
                            Distributing clothing, food, and essential items to families and individuals in Quezon City
                            communities. We ensure basic needs are met so families can focus on building better futures.
                        </p>
                        <strong class="initiative-title">Key Initiatives:</strong>
                        <ul class="initiative-list">
                            <li>Clothing drives</li>
                            <li>Food distribution</li>
                            <li>Essential items provision</li>
                            <li>Community support programs</li>
                        </ul>
                    </div>
                </div>

                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                        <i class="fas fa-people-group main-icon" aria-hidden="true"></i>
                        <span class="advocacy-badge">
                            <i class="fas fa-seedling" aria-hidden="true"></i> Youth
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Youth Development</h3>
                        <p class="campaign-description">
                            Empowering youth through education, mentorship, and opportunities. We focus on developing
                            the next generation of leaders and change-makers in our communities.
                        </p>
                        <strong class="initiative-title">Key Initiatives:</strong>
                        <ul class="initiative-list">
                            <li>Youth mentorship programs</li>
                            <li>Leadership development</li>
                            <li>Skills training</li>
                            <li>Career guidance</li>
                        </ul>
                    </div>
                </div>

                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                        <i class="fas fa-hand-holding-heart main-icon" aria-hidden="true"></i>
                        <span class="advocacy-badge">
                            <i class="fas fa-heart" aria-hidden="true"></i> Welfare
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Community Welfare</h3>
                        <p class="campaign-description">
                            Comprehensive welfare services for communities in Quezon City, addressing various needs
                            and supporting families through challenging times.
                        </p>
                        <strong class="initiative-title">Key Initiatives:</strong>
                        <ul class="initiative-list">
                            <li>Community outreach</li>
                            <li>Welfare assistance</li>
                            <li>Family support services</li>
                            <li>Emergency aid</li>
                        </ul>
                    </div>
                </div>

                <div class="campaign-card">
                    <div class="campaign-image" style="background: linear-gradient(135deg, #06b6d4, #22d3ee);">
                        <i class="fas fa-kit-medical main-icon" aria-hidden="true"></i>
                        <span class="advocacy-badge">
                            <i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Relief
                        </span>
                    </div>
                    <div class="campaign-content">
                        <h3 class="campaign-title">Disaster Relief</h3>
                        <p class="campaign-description">
                            Rapid response and long-term recovery support for communities affected by natural disasters
                            and emergencies in Quezon City and surrounding areas.
                        </p>
                        <strong class="initiative-title">Key Initiatives:</strong>
                        <ul class="initiative-list">
                            <li>Emergency relief distribution</li>
                            <li>Disaster response</li>
                            <li>Recovery assistance</li>
                            <li>Preparedness training</li>
                        </ul>
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
</body>
</html>