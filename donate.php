<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Donation - AlDrive</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/db.php';
include 'includes/functions.php';

$logged_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
$logged_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$logged_phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : '';

if (isset($_SESSION['user_id'])) {
    ensureUserProfileColumns($conn);
    $currentUser = getUserById($conn, (int) $_SESSION['user_id']);

    if ($currentUser) {
        updateSessionUserData($currentUser);
        $logged_name = $currentUser['full_name'];
        $logged_email = $currentUser['email'];
        $logged_phone = $currentUser['phone'] ?? '';
    }
}

if (isset($_GET['error'])): ?>
    <p style="color:red; text-align:center; margin-bottom: 15px;">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </p>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <p style="color:green; text-align:center; margin-bottom: 15px;">
        <?php echo htmlspecialchars($_GET['success']); ?>
    </p>
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
    </nav>

    <section style="padding: 3rem 0;">
        <div class="container donation-layout">
            <div class="card">
                <div class="card-header">
                    <h2 style="margin: 0 0 0.25rem 0;">Donate via QR Payment</h2>
                    <p class="subtle" style="margin: 0;">Scan using Maya or any supported e-wallet/banking app.</p>
                </div>
                <div class="card-body">
                    <div class="qr-wrap">
                        <img src="uploads/gcash.jpg" alt="Donation QR Code" class="qr-img">
                    </div>

                    <div style="display:grid; gap: 0.25rem; margin-bottom: 1rem;">
                        <div><strong>Account Name:</strong> Charity Foundation</div>
                        <div><strong>Accepted Method:</strong> Maya / QR Payment</div>
                    </div>

                    <div style="margin-top: 0.75rem;">
                        <strong style="color: var(--primary-color);">Steps</strong>
                        <ol class="list-steps" style="margin-top: 0.5rem;">
                            <li>Scan the QR code.</li>
                            <li>Complete your payment.</li>
                            <li>Enter the reference number.</li>
                            <li>Upload a screenshot of your proof (recommended).</li>
                            <li>Wait for admin verification.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="form-container" style="margin: 0; max-width: none;">
                <h1 class="form-title" style="margin-bottom: 0.75rem;">Donation</h1>
                <p style="text-align: center; color: var(--text-light); margin-bottom: 1.5rem; font-style: italic;">Building hope, changing lives.</p>
    <form id="donationForm" action="actions/donate_action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="campaignId" id="campaignId" value="general">
                <div class="form-group">
                    <label for="amount">Donation Amount (₱)</label>
                    <input type="number" name="amount" step="0.01" min="1" placeholder="Donation Amount" required>
                </div>
                <div class="form-group">
                    <label for="paymentMethod">Payment Method</label>
                    <select name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="Maya QR">Maya QR</option>
                        <option value="QR Payment">QR Payment</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="donorName">Full Name</label>
                    <input type="text" name="donor_name" value="<?php echo htmlspecialchars($logged_name); ?>" placeholder="Your Full Name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($logged_email); ?>" placeholder="Your Email Address" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($logged_phone); ?>" placeholder="Phone Number">
                </div>
                <div class="form-group">
                    <label for="referenceNumber">Reference Number</label>
                    <input type="text" name="reference_number" placeholder="Reference Number" required>
                </div>
                <div class="form-group">
                    <label>
                         <input type="checkbox" name="anonymous" value="1"> Donate Anonymously
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="recurring" value="1"> Make this a recurring donation
                    </label>
                </div>
                <div class="form-group">
                    <label for="message">Message (Optional)</label>
                    <textarea name="message" placeholder="Message (optional)"></textarea>
                </div>
                <div class="form-group">
                     <label for="proof_image">Upload Proof of Payment (optional)</label>
                     <input type="file" id="proof_image" name="proof_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Donation</button>
            </form>
            <div class="form-footer"><p>Your donation is secure and will be processed securely. You will receive an official receipt via email.</p></div>
            </div>
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
    <script>
        function setAmount(amount) { document.getElementById('amount').value = amount; }
    </script>
</body>
</html>
