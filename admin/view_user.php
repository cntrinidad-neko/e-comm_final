<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/auth.php';
requireAdmin();
include '../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php?error=Invalid user ID");
    exit;
}

$userId = (int) $_GET['id'];

$userQuery = "
    SELECT id, full_name, email, role, created_at
    FROM users
    WHERE id = $userId
    LIMIT 1
";
$userResult = mysqli_query($conn, $userQuery);

if (!$userResult || mysqli_num_rows($userResult) === 0) {
    header("Location: admin.php?error=User not found");
    exit;
}

$user = mysqli_fetch_assoc($userResult);

$donationsQuery = "
    SELECT 
        id,
        donor_name,
        email,
        phone,
        amount,
        payment_method,
        reference_number,
        anonymous,
        recurring,
        message,
        status,
        created_at,
        proof_image
    FROM donations
    WHERE user_id = $userId
    ORDER BY created_at DESC
";
$donationsResult = mysqli_query($conn, $donationsQuery);

$statsQuery = "
    SELECT
        COALESCE(SUM(CASE WHEN LOWER(status) IN ('verified', 'completed') THEN amount ELSE 0 END), 0) AS total_donated,
        COUNT(*) AS total_donations,
        MAX(created_at) AS last_donation
    FROM donations
    WHERE user_id = $userId
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = $statsResult ? mysqli_fetch_assoc($statsResult) : [
    'total_donated' => 0,
    'total_donations' => 0,
    'last_donation' => null
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - AlDrive Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-wrap {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
        }
        .info-card h3 {
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }
        .meta-label {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .meta-value {
            font-weight: 600;
            word-break: break-word;
        }
        .table-wrap {
            background: white;
            border-radius: 12px;
            overflow: auto;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
        }
        .table-wrap h2 {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            margin: 0;
        }
        .role-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .role-admin {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .role-user {
            background: #f3f4f6;
            color: #374151;
        }
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-verified,
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-other {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-hands-helping"></i>
                <span>AlDrive Admin</span>
            </div>
            <ul class="nav-menu">
                <li><a href="admin.php" class="btn-login">Back to Dashboard</a></li>
                <li><a href="../actions/logout.php" class="btn-login">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="page-wrap">
        <div class="top-bar">
            <div>
                <h1 style="margin-bottom: 0.25rem;">User Details</h1>
                <p style="color: var(--text-light); margin: 0;">View profile and donation records</p>
            </div>
            <a href="admin.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>Basic Information</h3>
                <div class="meta-label">User ID</div>
                <div class="meta-value"><?php echo (int)$user['id']; ?></div>

                <div class="meta-label" style="margin-top: 0.75rem;">Full Name</div>
                <div class="meta-value"><?php echo htmlspecialchars($user['full_name']); ?></div>

                <div class="meta-label" style="margin-top: 0.75rem;">Email</div>
                <div class="meta-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>

            <div class="info-card">
                <h3>Account Details</h3>
                <div class="meta-label">Role</div>
                <div class="meta-value">
                    <span class="role-badge <?php echo strtolower($user['role']) === 'admin' ? 'role-admin' : 'role-user'; ?>">
                        <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                    </span>
                </div>

                <div class="meta-label" style="margin-top: 0.75rem;">Joined</div>
                <div class="meta-value">
                    <?php echo !empty($user['created_at']) ? date('M d, Y h:i A', strtotime($user['created_at'])) : 'N/A'; ?>
                </div>
            </div>

            <div class="info-card">
                <h3>Donation Summary</h3>
                <div class="meta-label">Total Donated</div>
                <div class="meta-value">₱<?php echo number_format((float)$stats['total_donated'], 2); ?></div>

                <div class="meta-label" style="margin-top: 0.75rem;">Total Donations</div>
                <div class="meta-value"><?php echo (int)$stats['total_donations']; ?></div>

                <div class="meta-label" style="margin-top: 0.75rem;">Last Donation</div>
                <div class="meta-value">
                    <?php echo !empty($stats['last_donation']) ? date('M d, Y h:i A', strtotime($stats['last_donation'])) : 'No donations yet'; ?>
                </div>
            </div>
        </div>

        <div class="table-wrap">
            <h2>Donation History</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Donor Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Reference No.</th>
                        <th>Anonymous</th>
                        <th>Recurring</th>
                        <th>Message</th>
                        <th>Proof</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($donationsResult && mysqli_num_rows($donationsResult) > 0): ?>
                        <?php while ($donation = mysqli_fetch_assoc($donationsResult)): ?>
                            <?php
                                $status = strtolower($donation['status'] ?? '');
                                $statusClass = 'status-other';
                                if ($status === 'verified') {
                                    $statusClass = 'status-verified';
                                } elseif ($status === 'completed') {
                                    $statusClass = 'status-completed';
                                } elseif ($status === 'pending') {
                                    $statusClass = 'status-pending';
                                }
                            ?>
                            <tr>
                                <td><?php echo (int)$donation['id']; ?></td>
                                <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                <td><?php echo htmlspecialchars($donation['email']); ?></td>
                                <td><?php echo htmlspecialchars($donation['phone']); ?></td>
                                <td>₱<?php echo number_format((float)$donation['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($donation['payment_method']); ?></td>
                                <td><?php echo htmlspecialchars($donation['reference_number']); ?></td>
                                <td><?php echo !empty($donation['anonymous']) ? 'Yes' : 'No'; ?></td>
                                <td><?php echo !empty($donation['recurring']) ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($donation['message']); ?></td>
                                <td>
                                    <?php if (!empty($donation['proof_image'])): ?>
                                        <a href="../uploads/proofs/<?php echo htmlspecialchars($donation['proof_image']); ?>" target="_blank">
                                            <img src="../uploads/proofs/<?php echo htmlspecialchars($donation['proof_image']); ?>" alt="Proof" width="80">
                                        </a>
                                    <?php else: ?>
                                        No Proof
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst($donation['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo !empty($donation['created_at']) ? date('M d, Y h:i A', strtotime($donation['created_at'])) : 'N/A'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13">No donation records found for this user.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../script.js"></script>
</body>
</html>