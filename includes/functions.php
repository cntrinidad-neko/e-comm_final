<?php
function sanitize($data) {
    return trim((string)$data);
}

function tableExists($conn, $tableName) {
    $safeTable = $conn->real_escape_string($tableName);
    $result = $conn->query("SHOW TABLES LIKE '{$safeTable}'");
    return $result && $result->num_rows > 0;
}

function columnExists($conn, $tableName, $columnName) {
    if (!tableExists($conn, $tableName)) {
        return false;
    }

    $safeTable = str_replace('`', '``', $tableName);
    $safeColumn = $conn->real_escape_string($columnName);
    $result = $conn->query("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'");
    return $result && $result->num_rows > 0;
}

function ensureUserProfileColumns($conn) {
    if (!columnExists($conn, 'users', 'phone')) {
        $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(30) DEFAULT '' AFTER email");
    }

    if (!columnExists($conn, 'users', 'address')) {
        $conn->query("ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER phone");
    }
}

function updateSessionUserData($user) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['phone'] = $user['phone'] ?? '';
    $_SESSION['address'] = $user['address'] ?? '';
}

function getUserById($conn, $userId) {
    ensureUserProfileColumns($conn);

    $stmt = $conn->prepare("SELECT id, full_name, email, phone, address, password, role, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result && $result->num_rows === 1 ? $result->fetch_assoc() : null;
}

function getUserDonationStats($conn, $userId) {
    $stats = [
        'total_donated' => 0,
        'total_donations' => 0,
        'pending_donations' => 0,
        'verified_donations' => 0,
    ];

    if (!tableExists($conn, 'donations')) {
        return $stats;
    }

    $sql = "
        SELECT
            COALESCE(SUM(CASE WHEN LOWER(status) IN ('verified', 'completed') THEN amount ELSE 0 END), 0) AS total_donated,
            COUNT(*) AS total_donations,
            SUM(CASE WHEN LOWER(status) = 'pending' THEN 1 ELSE 0 END) AS pending_donations,
            SUM(CASE WHEN LOWER(status) IN ('verified', 'completed') THEN 1 ELSE 0 END) AS verified_donations
        FROM donations
        WHERE user_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stats['total_donated'] = (float)($row['total_donated'] ?? 0);
        $stats['total_donations'] = (int)($row['total_donations'] ?? 0);
        $stats['pending_donations'] = (int)($row['pending_donations'] ?? 0);
        $stats['verified_donations'] = (int)($row['verified_donations'] ?? 0);
    }

    return $stats;
}

function getUserDonations($conn, $userId) {
    $donations = [];

    if (!tableExists($conn, 'donations')) {
        return $donations;
    }

    $stmt = $conn->prepare("SELECT id, donor_name, email, phone, amount, payment_method, reference_number, anonymous, recurring, message, status, created_at, proof_image FROM donations WHERE user_id = ? ORDER BY created_at DESC, id DESC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $donations[] = $row;
        }
    }

    return $donations;
}

function getStatusBadgeClass($status) {
    $status = strtolower((string)$status);

    if ($status === 'verified' || $status === 'completed') {
        return 'status-success';
    }

    if ($status === 'pending') {
        return 'status-pending';
    }

    return 'status-default';
}
