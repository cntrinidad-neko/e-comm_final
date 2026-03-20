<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AlDrive</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<?php
session_start();

 if (isset($_GET['error'])): ?>
    <p style="color:red; text-align:center;"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <p style="color:green; text-align:center;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif;

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
            </ul>
            <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
        </div>
    </nav>
    <section style="padding: 3rem 0; min-height: 70vh;">
        <div class="form-container">
            <h2 class="form-title">Create Your Account</h2>
        <form id="registerForm" action="actions/register_action.php" method="POST">                
            <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create a password" minlength="8">
                    <small style="color: var(--text-light); font-size: 0.85rem;">Must be at least 8 characters</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
                <div style="text-align: center; margin: 1.5rem 0; color: var(--text-light);"><span></span></div>
            </form>
            <div class="form-footer"><p>Already have an account? <a href="login.php">Login here</a></p></div>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col"><h4>AlDrive</h4><p>Alban Memorial Foundation Inc.<br>Making a difference, one donation at a time.</p></div>
                <div class="footer-col"><h4>Quick Links</h4><ul><li><a href="about.php">About Us</a></li><li><a href="advocacies.php">Advocacies</a></li><li><a href="events.php">Events</a></li></ul></div>
                <div class="footer-col"><h4>Support</h4><ul><li><a href="#">FAQ</a></li><li><a href="#">Contact Us</a></li><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Service</a></li></ul></div>
                <div class="footer-col"><h4>Contact</h4><ul><li><i class="fas fa-envelope"></i> info@albanfoundation.org</li><li><i class="fas fa-phone"></i> +63 123 456 7890</li><li><i class="fas fa-map-marker-alt"></i> Quezon City, Philippines</li></ul></div>
            </div>
            <div class="footer-bottom"><p>&copy; 2026 Alban Memorial Foundation Inc. All rights reserved.</p></div>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>
