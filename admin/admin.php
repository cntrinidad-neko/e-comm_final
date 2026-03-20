<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/auth.php';
requireAdmin();
include '../config/db.php';

function tableExists($conn, $tableName) {
    $tableName = mysqli_real_escape_string($conn, $tableName);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return $result && mysqli_num_rows($result) > 0;
}

function columnExists($conn, $tableName, $columnName) {
    $tableName = mysqli_real_escape_string($conn, $tableName);
    $columnName = mysqli_real_escape_string($conn, $columnName);
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
    return $result && mysqli_num_rows($result) > 0;
}

$hasDonationsTable = tableExists($conn, 'donations');
$hasUsersTable = tableExists($conn, 'users');
$hasEventsTable = tableExists($conn, 'events');

$totalDonations = 0;
$totalDonors = 0;
$pendingVerifications = 0;
$recentDonations = [];
$eventsList = [];

$activeSection = $_GET['section'] ?? 'dashboard';
$allowedSections = ['dashboard', 'donations', 'donors', 'events'];
if (!in_array($activeSection, $allowedSections, true)) {
    $activeSection = 'dashboard';
}

$donations_sql = "SELECT * FROM donations ORDER BY created_at DESC";
$donations_result = $hasDonationsTable ? $conn->query($donations_sql) : false;

if ($hasDonationsTable) {
    $amountColumn = columnExists($conn, 'donations', 'amount');
    $statusColumn = columnExists($conn, 'donations', 'status');
    $emailColumn = columnExists($conn, 'donations', 'email');
    $createdAtColumn = columnExists($conn, 'donations', 'created_at');
    $donorNameColumn = columnExists($conn, 'donations', 'donor_name');
    $anonymousColumn = columnExists($conn, 'donations', 'anonymous');

    if ($amountColumn) {
        $verifiedFilter = $statusColumn ? "WHERE LOWER(status) IN ('verified', 'completed')" : "";
        $result = mysqli_query($conn, "SELECT COALESCE(SUM(amount), 0) AS total_donations FROM donations $verifiedFilter");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $totalDonations = (float)($row['total_donations'] ?? 0);
        }
    }

    if ($emailColumn) {
        $anonymousFilter = $anonymousColumn ? "AND (anonymous IS NULL OR anonymous = 0)" : "";
        $result = mysqli_query($conn, "SELECT COUNT(DISTINCT email) AS total_donors FROM donations WHERE email IS NOT NULL AND email != '' $anonymousFilter");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $totalDonors = (int)($row['total_donors'] ?? 0);
        }
    } elseif ($donorNameColumn) {
        $anonymousFilter = $anonymousColumn ? "AND (anonymous IS NULL OR anonymous = 0)" : "";
        $result = mysqli_query($conn, "SELECT COUNT(DISTINCT donor_name) AS total_donors FROM donations WHERE donor_name IS NOT NULL AND donor_name != '' $anonymousFilter");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $totalDonors = (int)($row['total_donors'] ?? 0);
        }
    }

    if ($statusColumn) {
        $result = mysqli_query($conn, "SELECT COUNT(*) AS pending_count FROM donations WHERE LOWER(status) = 'pending'");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $pendingVerifications = (int)($row['pending_count'] ?? 0);
        }
    }

    $recentDonationQuery = "SELECT * FROM donations";
    if ($createdAtColumn) {
        $recentDonationQuery .= " ORDER BY created_at DESC";
    } else {
        $recentDonationQuery .= " ORDER BY id DESC";
    }
    $recentDonationQuery .= " LIMIT 5";

    $recentResult = mysqli_query($conn, $recentDonationQuery);
    if ($recentResult) {
        while ($row = mysqli_fetch_assoc($recentResult)) {
            $recentDonations[] = $row;
        }
    }
}

if ($hasEventsTable) {
    $eventsResult = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC, id DESC");
    if ($eventsResult) {
        while ($row = mysqli_fetch_assoc($eventsResult)) {
            $eventsList[] = $row;
        }
    }
}

$successMessage = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$errorMessage = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AlDrive</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 70px;
            width: 250px;
            height: calc(100vh - 70px);
            background: var(--text-dark);
            color: white;
            padding: 2rem 0;
            overflow-y: auto;
        }
        .admin-sidebar ul { list-style: none; }
        .admin-sidebar ul li { margin-bottom: 0.5rem; }
        .admin-sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        .admin-sidebar ul li a:hover,
        .admin-sidebar ul li a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .admin-sidebar ul li a i { width: 20px; }

        .admin-content {
            margin-left: 250px;
            padding: 2rem;
        }

        .admin-section { display: none; }
        .admin-section.active { display: block; }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover { background: #dc2626; }

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

        .events-admin-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .inline-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 1rem;
        }

        .inline-form-grid-full {
            grid-column: 1 / -1;
        }

        .status-pill {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .events-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }

        .event-admin-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .event-admin-top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }

        .event-admin-title {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .event-admin-date {
            color: var(--primary-color);
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.2rem;
        }

        .event-admin-desc {
            color: var(--text-light);
            line-height: 1.7;
            margin: 0;
            font-size: 0.95rem;
        }

        .event-admin-meta {
            display: grid;
            gap: 0.45rem;
            color: var(--text-dark);
            font-size: 0.92rem;
        }

        .event-admin-meta i {
            color: var(--primary-color);
            width: 18px;
        }

        .event-admin-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 0.25rem;
        }

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

        .modal-overlay.show {
            display: flex;
        }

        .modal-card {
            background: #fff;
            width: 100%;
            max-width: 760px;
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            overflow: hidden;
            animation: modalFadeIn 0.25s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 2;
        }

        .modal-body {
            padding: 1.25rem;
        }

        .modal-close {
            background: transparent;
            border: none;
            font-size: 1.8rem;
            line-height: 1;
            cursor: pointer;
            color: #333;
        }

        .modal-close:hover {
            color: var(--primary-color);
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(15px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 900px) {
            .inline-form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .admin-sidebar.active {
                transform: translateX(0);
            }

            .admin-content {
                margin-left: 0;
            }

            .events-card-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><i class="fas fa-hands-helping"></i><span>AlDrive Admin</span></div>
            <ul class="nav-menu">
                <li><a href="../actions/logout.php" class="btn-login">Logout</a></li>
                <li><a href="#" onclick="toggleSidebar(); return false;" style="cursor: pointer;"><i class="fas fa-bars"></i></a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-sidebar" id="adminSidebar">
        <ul>
            <li><a href="#" onclick="showSection('dashboard', this); return false;" class="<?php echo $activeSection === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="#" onclick="showSection('donations', this); return false;" class="<?php echo $activeSection === 'donations' ? 'active' : ''; ?>"><i class="fas fa-hand-holding-heart"></i> Donations</a></li>
            <li><a href="#" onclick="showSection('donors', this); return false;" class="<?php echo $activeSection === 'donors' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Donors</a></li>
            <li><a href="#" onclick="showSection('events', this); return false;" class="<?php echo $activeSection === 'events' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> Events</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <?php if ($errorMessage): ?>
            <div id="error-alert" class="alert-box alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div id="success-alert" class="alert-box alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; margin-bottom: 1.5rem; border-radius: 6px;">
            <strong style="color: #92400e;"><i class="fas fa-shield-alt"></i> Admin Access Only</strong>
        </div>

        <div id="dashboard" class="admin-section <?php echo $activeSection === 'dashboard' ? 'active' : ''; ?>">
            <div class="dashboard-header"><h1>Admin Dashboard Overview</h1></div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-card-title">Total Donations</div>
                    <div class="stat-card-value">₱<?php echo number_format($totalDonations, 2); ?></div>
                    <div style="color: var(--text-light); font-size: 0.9rem; margin-top: 0.5rem;">Based on verified/completed donations</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-title">Total Donors</div>
                    <div class="stat-card-value"><?php echo number_format($totalDonors); ?></div>
                    <div style="color: var(--text-light); font-size: 0.9rem; margin-top: 0.5rem;">Counted from non-anonymous donor emails/names</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-title">Pending Verifications</div>
                    <div class="stat-card-value"><?php echo number_format($pendingVerifications); ?></div>
                    <div style="color: var(--text-light); font-size: 0.9rem; margin-top: 0.5rem;">Requires attention</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <div class="dashboard-table">
                    <h3 style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Recent Donations</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Amount</th>
                                <th>Campaign</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentDonations)): ?>
                                <?php foreach ($recentDonations as $donation): ?>
                                    <?php
                                        $recentDonorName = !empty($donation['anonymous']) && (int)$donation['anonymous'] === 1
                                            ? 'Anonymous'
                                            : (!empty($donation['donor_name']) ? $donation['donor_name'] : 'Unknown');

                                        $recentCampaign = 'N/A';
                                        if (isset($donation['campaign_name']) && $donation['campaign_name'] !== '') {
                                            $recentCampaign = $donation['campaign_name'];
                                        } elseif (isset($donation['campaign_title']) && $donation['campaign_title'] !== '') {
                                            $recentCampaign = $donation['campaign_title'];
                                        }

                                        $recentStatus = isset($donation['status']) ? ucfirst($donation['status']) : 'N/A';
                                        $recentStatusColor = '#6b7280';

                                        if (isset($donation['status'])) {
                                            $statusLower = strtolower($donation['status']);
                                            if ($statusLower === 'verified' || $statusLower === 'completed') {
                                                $recentStatusColor = 'var(--secondary-color)';
                                            } elseif ($statusLower === 'pending') {
                                                $recentStatusColor = '#f59e0b';
                                            } else {
                                                $recentStatusColor = '#ef4444';
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($recentDonorName); ?></td>
                                        <td>₱<?php echo number_format((float)($donation['amount'] ?? 0), 2); ?></td>
                                        <td><?php echo htmlspecialchars($recentCampaign); ?></td>
                                        <td><?php echo !empty($donation['created_at']) ? date('M d, Y', strtotime($donation['created_at'])) : 'N/A'; ?></td>
                                        <td><span style="color: <?php echo $recentStatusColor; ?>;"><?php echo htmlspecialchars($recentStatus); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5">No donation records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="donations" class="admin-section <?php echo $activeSection === 'donations' ? 'active' : ''; ?>">
            <div class="dashboard-header">
                <h1>Donation Records</h1>
                <div style="color: var(--text-light);">Review proof uploads and update statuses.</div>
            </div>

            <div class="dashboard-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($donations_result && $donations_result->num_rows > 0): ?>
                            <?php while ($donation = $donations_result->fetch_assoc()): ?>
                                <?php
                                    $isAnonymous = !empty($donation['anonymous']) && (int)$donation['anonymous'] === 1;
                                    $displayDonor = $isAnonymous ? 'Anonymous' : ($donation['donor_name'] ?? '');
                                    $displayEmail = $isAnonymous ? 'Hidden' : ($donation['email'] ?? '');
                                    $displayPhone = $isAnonymous ? 'Hidden' : ($donation['phone'] ?? '');
                                ?>
                                <tr>
                                    <td><?php echo (int)$donation['id']; ?></td>
                                    <td><?php echo htmlspecialchars($displayDonor); ?></td>
                                    <td><?php echo htmlspecialchars($displayEmail); ?></td>
                                    <td><?php echo htmlspecialchars($displayPhone); ?></td>
                                    <td>₱<?php echo number_format((float)($donation['amount'] ?? 0), 2); ?></td>
                                    <td><?php echo htmlspecialchars($donation['payment_method'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($donation['reference_number'] ?? ''); ?></td>
                                    <td>
                                        <?php if (!empty($donation['proof_image'])): ?>
                                            <a href="../uploads/proofs/<?php echo rawurlencode($donation['proof_image']); ?>" target="_blank" class="btn btn-outline btn-sm">View</a>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(ucfirst($donation['status'] ?? '')); ?></td>
                                    <td><?php echo !empty($donation['created_at']) ? htmlspecialchars($donation['created_at']) : 'N/A'; ?></td>
                                    <td>
                                        <form action="../actions/update_donation_status.php" method="POST" style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                                            <input type="hidden" name="donation_id" value="<?php echo (int)$donation['id']; ?>">
                                            <select name="status" required style="padding: 0.5rem 0.75rem; border-radius: 8px; border: 2px solid var(--border-color);">
                                                <option value="pending" <?php if (($donation['status'] ?? '') === 'pending') echo 'selected'; ?>>Pending</option>
                                                <option value="verified" <?php if (($donation['status'] ?? '') === 'verified') echo 'selected'; ?>>Verified</option>
                                                <option value="completed" <?php if (($donation['status'] ?? '') === 'completed') echo 'selected'; ?>>Completed</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="11" style="text-align:center; color: var(--text-light); padding: 2rem;">No donation records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="donors" class="admin-section <?php echo $activeSection === 'donors' ? 'active' : ''; ?>">
            <div class="dashboard-header">
                <h1>User Management</h1>
                <input type="text" id="userSearch" placeholder="Search users..." style="padding: 0.5rem 1rem; border-radius: 6px; border: 2px solid var(--border-color);">
            </div>

            <div class="dashboard-table">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Total Donated</th>
                            <th>Total Donations</th>
                            <th>Last Donation</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($hasUsersTable): ?>
                            <?php
                            $usersQuery = "
                                SELECT 
                                    u.id,
                                    u.full_name,
                                    u.email,
                                    u.role,
                                    u.created_at,
                                    COALESCE(SUM(CASE 
                                        WHEN LOWER(d.status) IN ('verified', 'completed') THEN d.amount 
                                        ELSE 0 
                                    END), 0) AS total_donated,
                                    COUNT(d.id) AS total_donations,
                                    MAX(d.created_at) AS last_donation
                                FROM users u
                                LEFT JOIN donations d 
                                    ON u.id = d.user_id
                                    AND (d.anonymous IS NULL OR d.anonymous = 0)
                                GROUP BY u.id, u.full_name, u.email, u.role, u.created_at
                                HAVING u.role = 'admin' OR COUNT(d.id) > 0
                                ORDER BY u.id DESC
                            ";

                            $usersResult = mysqli_query($conn, $usersQuery);
                            ?>

                            <?php if ($usersResult && mysqli_num_rows($usersResult) > 0): ?>
                                <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
                                    <tr>
                                        <td><?php echo (int)$user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span style="
                                                padding: 0.25rem 0.5rem;
                                                border-radius: 4px;
                                                font-size: 0.85rem;
                                                background: <?php echo strtolower($user['role']) === 'admin' ? '#dbeafe' : '#f3f4f6'; ?>;
                                                color: <?php echo strtolower($user['role']) === 'admin' ? '#1d4ed8' : '#374151'; ?>;
                                            ">
                                                <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                            </span>
                                        </td>
                                        <td>₱<?php echo number_format((float)$user['total_donated'], 2); ?></td>
                                        <td><?php echo (int)$user['total_donations']; ?></td>
                                        <td><?php echo !empty($user['last_donation']) ? date('M d, Y h:i A', strtotime($user['last_donation'])) : 'No donations yet'; ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view_user.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-outline btn-sm">View</a>
                                                <?php if (strtolower($user['role']) !== 'admin'): ?>
                                                    <a href="../actions/delete_user.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                                <?php else: ?>
                                                    <span style="color: #6b7280; font-size: 0.85rem;">Protected</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="9">No users found.</td></tr>
                            <?php endif; ?>
                        <?php else: ?>
                            <tr><td colspan="9">Users table not found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="events" class="admin-section <?php echo $activeSection === 'events' ? 'active' : ''; ?>">
            <div class="dashboard-header">
                <h1>Manage Events</h1>
                <div style="color: var(--text-light);">Create, edit, and remove public events.</div>
            </div>

            <?php if (!$hasEventsTable): ?>
                <div class="dashboard-table">
                    <div style="padding: 1.5rem;">
                        <p style="margin: 0; color: #991b1b;">Events table not found. Please create the <strong>events</strong> table first in your database.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="events-admin-grid">
                    <div class="dashboard-table">
                        <h3 style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Create New Event</h3>
                        <form action="../actions/add_event.php" method="POST" style="padding: 1.5rem;">
                            <div class="inline-form-grid">
                                <div class="form-group">
                                    <label>Event Title</label>
                                    <input type="text" name="title" required>
                                </div>
                                <div class="form-group">
                                    <label>Event Date</label>
                                    <input type="date" name="event_date" required>
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" required>
                                </div>
                                <div class="form-group">
                                    <label>Type</label>
                                    <input type="text" name="type" placeholder="e.g. outreach, training, ceremony">
                                </div>
                                <div class="form-group">
                                    <label>Contact Name</label>
                                    <input type="text" name="contact_name">
                                </div>
                                <div class="form-group">
                                    <label>Contact Phone</label>
                                    <input type="text" name="contact_phone">
                                </div>
                                <div class="form-group inline-form-grid-full">
                                    <label>Team / Email</label>
                                    <input type="text" name="contact_person" placeholder="e.g. Scholarship Desk (info@albanfoundation.org)">
                                </div>
                                <div class="form-group inline-form-grid-full">
                                    <label>Description</label>
                                    <textarea name="description" rows="4" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Event</button>
                        </form>
                    </div>

                    <div class="dashboard-table">
                        <h3 style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Existing Events</h3>

                        <?php if (!empty($eventsList)): ?>
                            <div class="events-card-grid">
                                <?php foreach ($eventsList as $event): ?>
                                    <div class="event-admin-card">
                                        <div class="event-admin-top">
                                            <div>
                                                <h4 class="event-admin-title"><?php echo htmlspecialchars($event['title']); ?></h4>
                                                <div class="event-admin-date">
                                                    <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                                </div>
                                            </div>
                                            <span class="status-pill <?php echo ($event['status'] === 'active') ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo htmlspecialchars(ucfirst($event['status'])); ?>
                                            </span>
                                        </div>

                                        <p class="event-admin-desc"><?php echo htmlspecialchars($event['description']); ?></p>

                                        <div class="event-admin-meta">
                                            <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></div>
                                            <div><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['type'] ?: 'event'); ?></div>
                                            <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($event['contact_name'] ?: 'AMF Representative'); ?></div>
                                            <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($event['contact_phone'] ?: 'No phone provided'); ?></div>
                                            <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($event['contact_person'] ?: 'No team/email provided'); ?></div>
                                        </div>

                                        <div class="event-admin-actions">
                                            <button
                                                type="button"
                                                class="btn btn-primary btn-sm edit-event-btn"
                                                data-id="<?php echo (int)$event['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?>"
                                                data-description="<?php echo htmlspecialchars($event['description'], ENT_QUOTES); ?>"
                                                data-location="<?php echo htmlspecialchars($event['location'], ENT_QUOTES); ?>"
                                                data-date="<?php echo htmlspecialchars($event['event_date'], ENT_QUOTES); ?>"
                                                data-type="<?php echo htmlspecialchars($event['type'] ?? '', ENT_QUOTES); ?>"
                                                data-contact-person="<?php echo htmlspecialchars($event['contact_person'] ?? '', ENT_QUOTES); ?>"
                                                data-contact-name="<?php echo htmlspecialchars($event['contact_name'] ?? '', ENT_QUOTES); ?>"
                                                data-contact-phone="<?php echo htmlspecialchars($event['contact_phone'] ?? '', ENT_QUOTES); ?>"
                                                data-status="<?php echo htmlspecialchars($event['status'], ENT_QUOTES); ?>"
                                            >
                                                Edit
                                            </button>

                                            <a href="../actions/delete_event.php?id=<?php echo (int)$event['id']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this event?');">
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="padding: 1.5rem; color: var(--text-light);">No events created yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal-overlay" id="editEventModal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editEventModalTitle">
            <div class="modal-header">
                <h3 id="editEventModalTitle" style="margin:0;">Edit Event</h3>
                <button type="button" class="modal-close" id="editEventModalClose" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form action="../actions/update_event.php" method="POST">
                    <input type="hidden" name="event_id" id="modalEventId">

                    <div class="inline-form-grid">
                        <div class="form-group">
                            <label>Event Title</label>
                            <input type="text" name="title" id="modalTitle" required>
                        </div>

                        <div class="form-group">
                            <label>Event Date</label>
                            <input type="date" name="event_date" id="modalDate" required>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" id="modalLocation" required>
                        </div>

                        <div class="form-group">
                            <label>Type</label>
                            <input type="text" name="type" id="modalType">
                        </div>

                        <div class="form-group">
                            <label>Contact Name</label>
                            <input type="text" name="contact_name" id="modalContactName">
                        </div>

                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="contact_phone" id="modalContactPhone">
                        </div>

                        <div class="form-group inline-form-grid-full">
                            <label>Team / Email</label>
                            <input type="text" name="contact_person" id="modalContactPerson">
                        </div>

                        <div class="form-group inline-form-grid-full">
                            <label>Description</label>
                            <textarea name="description" id="modalDescription" rows="4" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="modalStatus" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center; margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-outline" id="editEventCancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        setTimeout(function () {
            const success = document.getElementById("success-alert");
            const error = document.getElementById("error-alert");

            if (success) {
                success.style.transition = "opacity 0.5s";
                success.style.opacity = "0";
                setTimeout(() => success.remove(), 500);
            }

            if (error) {
                error.style.transition = "opacity 0.5s";
                error.style.opacity = "0";
                setTimeout(() => error.remove(), 500);
            }
        }, 3000);

        function showSection(sectionId, element) {
            document.querySelectorAll('.admin-section').forEach(section => {
                section.classList.remove('active');
            });

            document.querySelectorAll('.admin-sidebar a').forEach(link => {
                link.classList.remove('active');
            });

            document.getElementById(sectionId).classList.add('active');

            if (element) {
                element.classList.add('active');
            }

            const url = new URL(window.location.href);
            url.searchParams.set('section', sectionId);
            window.history.replaceState({}, '', url);
        }

        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('active');
        }

        const userSearch = document.getElementById('userSearch');
        if (userSearch) {
            userSearch.addEventListener('keyup', function () {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#usersTable tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        const editEventModal = document.getElementById('editEventModal');
        const editEventModalClose = document.getElementById('editEventModalClose');
        const editEventCancelBtn = document.getElementById('editEventCancelBtn');

        function openEditEventModal(button) {
            document.getElementById('modalEventId').value = button.dataset.id || '';
            document.getElementById('modalTitle').value = button.dataset.title || '';
            document.getElementById('modalDescription').value = button.dataset.description || '';
            document.getElementById('modalLocation').value = button.dataset.location || '';
            document.getElementById('modalDate').value = button.dataset.date || '';
            document.getElementById('modalType').value = button.dataset.type || '';
            document.getElementById('modalContactPerson').value = button.dataset.contactPerson || '';
            document.getElementById('modalContactName').value = button.dataset.contactName || '';
            document.getElementById('modalContactPhone').value = button.dataset.contactPhone || '';
            document.getElementById('modalStatus').value = button.dataset.status || 'active';

            editEventModal.classList.add('show');
            editEventModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeEditEventModal() {
            editEventModal.classList.remove('show');
            editEventModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.edit-event-btn').forEach(button => {
            button.addEventListener('click', function () {
                openEditEventModal(this);
            });
        });

        if (editEventModalClose) {
            editEventModalClose.addEventListener('click', closeEditEventModal);
        }

        if (editEventCancelBtn) {
            editEventCancelBtn.addEventListener('click', closeEditEventModal);
        }

        if (editEventModal) {
            editEventModal.addEventListener('click', function (e) {
                if (e.target === editEventModal) {
                    closeEditEventModal();
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && editEventModal.classList.contains('show')) {
                closeEditEventModal();
            }
        });
    </script>
    <script src="../script.js"></script>
</body>
</html>