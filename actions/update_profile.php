<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';
include '../includes/functions.php';

ensureUserProfileColumns($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../user/dashboard.php?tab=profile');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$fullName = sanitize($_POST['full_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$address = sanitize($_POST['address'] ?? '');

if (empty($fullName) || empty($email) || empty($phone)) {
    header('Location: ../user/dashboard.php?tab=profile&error=Full name, email, and phone are required.');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../user/dashboard.php?tab=profile&error=Invalid email format.');
    exit;
}

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
$checkStmt->bind_param('si', $email, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult && $checkResult->num_rows > 0) {
    header('Location: ../user/dashboard.php?tab=profile&error=Email is already used by another account.');
    exit;
}

$updateStmt = $conn->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
$updateStmt->bind_param('ssssi', $fullName, $email, $phone, $address, $userId);

if (!$updateStmt->execute()) {
    header('Location: ../user/dashboard.php?tab=profile&error=Failed to update profile.');
    exit;
}

if (tableExists($conn, 'donations')) {
    $donationSyncStmt = $conn->prepare('UPDATE donations SET donor_name = ?, email = ?, phone = ? WHERE user_id = ?');
    $donationSyncStmt->bind_param('sssi', $fullName, $email, $phone, $userId);
    $donationSyncStmt->execute();
}

$freshUser = getUserById($conn, $userId);
if ($freshUser) {
    updateSessionUserData($freshUser);
}

header('Location: ../user/dashboard.php?tab=profile&success=Profile updated successfully.');
exit;
?>
