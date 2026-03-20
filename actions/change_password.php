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
    header('Location: ../user/dashboard.php?tab=settings');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    header('Location: ../user/dashboard.php?tab=settings&error=All password fields are required.');
    exit;
}

if ($newPassword !== $confirmPassword) {
    header('Location: ../user/dashboard.php?tab=settings&error=New passwords do not match.');
    exit;
}

if (strlen($newPassword) < 8) {
    header('Location: ../user/dashboard.php?tab=settings&error=New password must be at least 8 characters.');
    exit;
}

$user = getUserById($conn, $userId);
if (!$user) {
    session_destroy();
    header('Location: ../login.php?error=User account not found.');
    exit;
}

if (!password_verify($currentPassword, $user['password'] ?? '')) {
    header('Location: ../user/dashboard.php?tab=settings&error=Current password is incorrect.');
    exit;
}

$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->bind_param('si', $hashed, $userId);

if (!$stmt->execute()) {
    header('Location: ../user/dashboard.php?tab=settings&error=Failed to update password.');
    exit;
}

header('Location: ../user/dashboard.php?tab=settings&success=Password updated successfully.');
exit;
?>

