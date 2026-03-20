<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - AlDrive</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><i class="fas fa-hands-helping"></i><span>AlDrive</span></div>
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
    </nav>
    <section style="padding: 3rem 0;">
        <div class="container">
            <div style="max-width: 900px; margin: 0 auto;">
                <h1 class="section-title" style="text-align: left; margin-bottom: 2rem;">About Alban Memorial Foundation Inc.</h1>
                <div style="background: var(--bg-white); padding: 2rem; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem; color: var(--primary-color);">Our Mission</h2>
                    <p style="color: var(--text-light); line-height: 1.8; font-size: 1.1rem;">The Foundation shall design programs and services that are integral to the well-being of the person; that will enhance the total functioning and wellness of the families and their children who are disadvantaged that shall contribute to the national development goals. It shall inculcate the vales of nationalism, integrity, spirituality, and patriotism as futute leaders of the Filipino nation; It shall contribute to the national program on poverty alleviation of the served marginalized sectors of the Filipino society</p>
                </div>
                <div style="background: var(--bg-white); padding: 2rem; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem; color: var(--primary-color);">Our Vision</h2>
                    <p style="color: var(--text-light); line-height: 1.8; font-size: 1.1rem;">The foundation envisions every Filipino child, their families and communities live a life with dignity, where the promotion of life is sustained, nurtured, cared and secured towards total Human development. </p>
                </div>
                <div style="background: var(--bg-white); padding: 2rem; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem; color: var(--primary-color);">Our Goals</h2>
                    <p style="color: var(--text-light); line-height: 1.8; margin-bottom: 1rem;">We aim to help disadvantaged children to enrich God given talent and potential through provision of care, teaching and healing process for self-direction and eventually integration to the mainstream of society. We aim to help and support financially/materially the program and services of organization and institution for effective and efficient service delivery towards the welfare and development of communities, families and children.</p>
                    <p style="color: var(--text-light); line-height: 1.8;">We aim to help communities and families by providing educational services and livelihood assistance towards self-sustaining and productive communities and families.</p>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col"><h4>AlDrive</h4><p>Alban Memorial Foundation Inc.<br>Making a difference, one donation at a time.</p><div class="social-links"><a href="#"><i class="fab fa-facebook"></i></a><a href="#"><i class="fab fa-twitter"></i></a><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-linkedin"></i></a></div></div>
                <div class="footer-col"><h4>Quick Links</h4><ul><li><a href="about.php">About Us</a></li><li><a href="advocacies.php"></a></li><li><a href="events.php">Events</a></li></ul></div>
                <div class="footer-col"><h4>Support</h4><ul><li><a href="#">FAQ</a></li><li><a href="#">Contact Us</a></li><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Service</a></li></ul></div>
                <div class="footer-col"><h4>Contact</h4><ul><li><i class="fas fa-envelope"></i> info@albanfoundation.org</li><li><i class="fas fa-phone"></i> +63 123 456 7890</li><li><i class="fas fa-map-marker-alt"></i> Quezon City, Philippines</li></ul></div>
            </div>
            <div class="footer-bottom"><p>&copy; 2026 Alban Memorial Foundation Inc. All rights reserved.</p></div>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>
