<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: ../login.php');
    exit;
}

include '../includes/auth.php';
requireLogin();
include '../config/db.php';
include '../includes/functions.php';

ensureUserProfileColumns($conn);

$userId = (int)$_SESSION['user_id'];
$user = getUserById($conn, $userId);

if (!$user) {
    session_destroy();
    header('Location: ../login.php?error=User account not found.');
    exit;
}

updateSessionUserData($user);

$stats = getUserDonationStats($conn, $userId);
$donations = getUserDonations($conn, $userId);
$receiptProofs = array_values(array_filter($donations, function ($donation) {
    return !empty($donation['proof_image']);
}));

$allowedTabs = ['profile', 'donations', 'receipts', 'settings'];
$activeTab = $_GET['tab'] ?? 'profile';
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'profile';
}

$successMessage = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$errorMessage = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - AlDrive</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-section {
            display: none;
            padding-bottom: 2rem;
        }
        .dashboard-section.active {
            display: block;
        }
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .dashboard-tabs-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            list-style: none;
            margin: 1.5rem 0 0 0;
            padding: 0;
        }
        .dashboard-tabs-inline a {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 6px;
            transition: all 0.3s;
        }
        .dashboard-tabs-inline a:hover,
        .dashboard-tabs-inline a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: white;
        }
        .dashboard-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        .alert-box {
            text-align: center;
            margin-bottom: 15px;
            padding: 12px 16px;
            border-radius: 6px;
        }
        .alert-success {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #86efac;
        }
        .alert-error {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fca5a5;
        }
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-success {
            background: #dcfce7;
            color: #166534;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-default {
            background: #e5e7eb;
            color: #374151;
        }
        .empty-state {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
        }
        .receipt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }
        .receipt-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }
        .receipt-meta {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .receipt-thumb {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-light);
            box-shadow: 0 10px 22px rgba(0,0,0,0.08);
        }
        @media (max-width: 900px) {
            #settings > div {
                grid-template-columns: 1fr !important;
            }
        }
        @media (max-width: 768px) {
            .profile-header {
                padding: 1.5rem;
            }
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
            .profile-header h2 {
                font-size: 1.25rem;
            }
            .profile-header p {
                font-size: 0.9rem;
            }
            .dashboard-tabs-inline {
                flex-direction: column;
            }
            .dashboard-tabs-inline a {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><i class="fas fa-hands-helping"></i><span>AlDrive</span></div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../donate.php">Donate</a></li>
                <li><a href="../about.php">About Us</a></li>
                <li><a href="../events.php">Events</a></li>
                <li><a href="dashboard.php?tab=profile">Dashboard</a></li>
                <li><a href="../actions/logout.php">Logout</a></li>
            </ul>
            <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
        </div>
    </nav>

    <section class="dashboard" style="padding-top: 0;">
        <div class="container">
            <div style="margin-top: 30px;" class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <ul class="dashboard-tabs dashboard-tabs-inline">
                    <li><a href="?tab=profile" class="<?php echo $activeTab === 'profile' ? 'active' : ''; ?>">Profile</a></li>
                    <li><a href="?tab=donations" class="<?php echo $activeTab === 'donations' ? 'active' : ''; ?>">My Donations</a></li>
                    <li><a href="?tab=receipts" class="<?php echo $activeTab === 'receipts' ? 'active' : ''; ?>">Receipts</a></li>
                    <li><a href="?tab=settings" class="<?php echo $activeTab === 'settings' ? 'active' : ''; ?>">Settings</a></li>
                </ul>
            </div>

            <?php if (!empty($successMessage)): ?>
                <div class="alert-box alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert-box alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <div id="profile" class="dashboard-section <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="stat-card">
                        <div class="stat-card-title">Total Donated</div>
                        <div class="stat-card-value">₱<?php echo number_format((float)$stats['total_donated'], 2); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-title">Total Donations</div>
                        <div class="stat-card-value"><?php echo (int)$stats['total_donations']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-title">Pending Donations</div>
                        <div class="stat-card-value"><?php echo (int)$stats['pending_donations']; ?></div>
                    </div>
                </div>

                <div class="dashboard-table">
                    <h3 style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Profile Information</h3>
                    <div style="padding: 1.5rem;">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea rows="3" readonly><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div id="donations" class="dashboard-section <?php echo $activeTab === 'donations' ? 'active' : ''; ?>">
                <h2 style="margin-bottom: 1.5rem;">My Donation History</h2>
                <div class="dashboard-table">
                    <?php if (count($donations) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Reference No.</th>
                                    <th>Status</th>
                                    <th>Proof</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?php echo !empty($donation['created_at']) ? date('M d, Y h:i A', strtotime($donation['created_at'])) : 'N/A'; ?></td>
                                        <td>₱<?php echo number_format((float)$donation['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($donation['payment_method']); ?></td>
                                        <td><?php echo htmlspecialchars($donation['reference_number'] ?: 'N/A'); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo getStatusBadgeClass($donation['status'] ?? ''); ?>">
                                                <?php echo htmlspecialchars(ucfirst($donation['status'] ?? 'Unknown')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($donation['proof_image'])): ?>
                                                <a href="../uploads/proofs/<?php echo rawurlencode($donation['proof_image']); ?>" target="_blank" class="btn btn-outline btn-sm">View Proof</a>
                                            <?php else: ?>
                                                <span style="color: var(--text-light);">No file</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No donation records yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="receipts" class="dashboard-section <?php echo $activeTab === 'receipts' ? 'active' : ''; ?>">
                <h2 style="margin-bottom: 1.5rem;">Receipts</h2>
                <?php if (count($receiptProofs) > 0): ?>
                    <div class="receipt-grid">
                        <?php foreach ($receiptProofs as $donation): ?>
                            <div class="receipt-card">
                                <h3 style="margin-bottom: 0.5rem;">Proof of Payment</h3>
                                <div class="receipt-meta">
                                    <?php echo !empty($donation['created_at']) ? date('F d, Y h:i A', strtotime($donation['created_at'])) : 'N/A'; ?>
                                    · ₱<?php echo number_format((float)($donation['amount'] ?? 0), 2); ?>
                                </div>
                                <a href="../uploads/proofs/<?php echo rawurlencode($donation['proof_image']); ?>" target="_blank" style="display:block; text-decoration:none;">
                                    <img class="receipt-thumb" src="../uploads/proofs/<?php echo rawurlencode($donation['proof_image']); ?>" alt="Proof of payment screenshot">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="dashboard-table">
                        <div class="empty-state">
                            <p>No receipts records yet.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div id="settings" class="dashboard-section <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="dashboard-table">
                        <h3 style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Update Profile</h3>
                        <form id="profileForm" action="../actions/update_profile.php" method="POST" style="padding: 1.5rem;">
                            <div class="form-group">
                                <label for="profileName">Full Name</label>
                                <input type="text" id="profileName" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="profileEmail">Email Address</label>
                                <input type="email" id="profileEmail" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="profilePhone">Phone Number</label>
                                <input type="tel" id="profilePhone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="profileAddress">Address</label>
                                <textarea id="profileAddress" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>

                    <div class="dashboard-table">
                        <h3 style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Change Password</h3>
                        <form action="../actions/change_password.php" method="POST" style="padding: 1.5rem;">
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" name="new_password" minlength="8" required>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" name="confirm_password" minlength="8" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <div class="form-footer" style="margin-top: 1rem;">
                                <p style="margin:0;">Password must be at least 8 characters.</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col"><h4>AlDrive</h4><p>Alban Memorial Foundation Inc.<br>Making a difference, one donation at a time.</p></div>
                <div class="footer-col"><h4>Quick Links</h4><ul><li><a href="../about.php">About Us</a></li><li><a href="../advocacies.php">Advocacies</a></li><li><a href="../events.php">Events</a></li></ul></div>
                <div class="footer-col"><h4>Support</h4><ul><li><a href="#">FAQ</a></li><li><a href="#">Contact Us</a></li><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Service</a></li></ul></div>
                <div class="footer-col"><h4>Contact</h4><ul><li><i class="fas fa-envelope"></i> info@albanfoundation.org</li><li><i class="fas fa-phone"></i> +63 123 456 7890</li><li><i class="fas fa-map-marker-alt"></i> Quezon City, Philippines</li></ul></div>
            </div>
            <div class="footer-bottom"><p>&copy; 2026 Alban Memorial Foundation Inc. All rights reserved.</p></div>
        </div>
    </footer>

    <script src="../script.js"></script>
</body>
</html>