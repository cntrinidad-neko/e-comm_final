<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Details - AlDrive</title>
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
                <<a href="index.php">Home</a>

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
                <div style="background: var(--bg-white); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-lg);">
                    <div class="campaign-image" style="height: 400px; background: linear-gradient(135deg, #2563eb, #10b981);"></div>
                    <div style="padding: 2rem;">
                        <h1 id="campaignTitle" style="font-size: 2rem; margin-bottom: 1rem;">Campaign Title</h1>
                        <div class="campaign-progress" style="margin-bottom: 2rem;">
                            <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width: 0%"></div></div>
                            <div class="progress-info"><span id="raisedAmount">₱0</span><span class="campaign-goal" id="goalAmount">Goal: ₱0</span></div>
                        </div>
                        <p id="campaignDescription" style="color: var(--text-light); margin-bottom: 2rem; line-height: 1.8;">Campaign description will appear here.</p>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <a href="donate.php" class="btn btn-primary">Donate Now</a>
                            <button class="btn btn-outline" onclick="shareCampaign()"><i class="fas fa-share-alt"></i> Share Campaign</button>
                        </div>
                    </div>
                </div>
                <div style="background: var(--bg-white); padding: 2rem; border-radius: 12px; margin-top: 2rem; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 1.5rem;">Campaign Information</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <div><strong style="color: var(--text-light);">Start Date</strong><p id="startDate">January 1, 2026</p></div>
                        <div><strong style="color: var(--text-light);">End Date</strong><p id="endDate">March 31, 2026</p></div>
                        <div><strong style="color: var(--text-light);">Donors</strong><p id="donorCount">234</p></div>
                        <div><strong style="color: var(--text-light);">Status</strong><p style="color: var(--secondary-color); font-weight: 600;">Active</p></div>
                    </div>
                </div>
                <div style="background: var(--bg-white); padding: 2rem; border-radius: 12px; margin-top: 2rem; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 1.5rem;">Impact Stories</h2>
                    <div style="color: var(--text-light); line-height: 1.8;">
                        <p>This campaign has already made a significant impact in Quezon City communities. Through your generous donations, we have been able to support numerous families and individuals in need.</p>
                        <p style="margin-top: 1rem;">Your contribution helps Alban Memorial Foundation Inc. continue its mission of providing welfare services, education, and support to those who need it most. Every donation, no matter the size, makes a difference.</p>
                    </div>
                </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const campaignId = urlParams.get('id');
            const campaign = sampleCampaigns.find(c => c.id == campaignId) || sampleCampaigns[0];
            if (campaign) {
                document.getElementById('campaignTitle').textContent = campaign.title;
                document.getElementById('campaignDescription').textContent = campaign.description;
                document.getElementById('raisedAmount').textContent = `₱${campaign.raised.toLocaleString()}`;
                document.getElementById('goalAmount').textContent = `Goal: ₱${campaign.goal.toLocaleString()}`;
                document.getElementById('progressFill').style.width = `${(campaign.raised / campaign.goal) * 100}%`;
                const donateBtn = document.querySelector('.btn-primary');
                if (donateBtn) { donateBtn.href = `donate.php?id=${campaign.id}`; }
            }
        });
        function shareCampaign() {
            if (navigator.share) {
                navigator.share({title: document.getElementById('campaignTitle').textContent, text: document.getElementById('campaignDescription').textContent, url: window.location.href});
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Campaign link copied to clipboard!');
            }
        }
    </script>
</body>
</html>
