<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../admin/admin.php?error=Invalid user ID");
    exit;
}

$userId = (int) $_GET['id'];

// Check if user exists
$checkQuery = mysqli_query($conn, "SELECT role FROM users WHERE id = $userId LIMIT 1");

if (!$checkQuery || mysqli_num_rows($checkQuery) === 0) {
    header("Location: ../admin/admin.php?error=User not found");
    exit;
}

$user = mysqli_fetch_assoc($checkQuery);

// Prevent deleting admin
if (strtolower($user['role']) === 'admin') {
    header("Location: ../admin/admin.php?error=Admin user cannot be deleted");
    exit;
}

// Delete user's donations first
mysqli_query($conn, "DELETE FROM donations WHERE user_id = $userId");

// Delete user
$deleteQuery = mysqli_query($conn, "DELETE FROM users WHERE id = $userId");

if ($deleteQuery) {
    header("Location: ../admin/admin.php?success=User deleted successfully");
} else {
    header("Location: ../admin/admin.php?error=Failed to delete user");
}
exit;
?>