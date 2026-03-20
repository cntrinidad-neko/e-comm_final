<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

ensureUserProfileColumns($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: ../login.php?error=All fields are required.');
        exit;
    }

    $sql = 'SELECT id, full_name, email, phone, address, password, role FROM users WHERE email = ? LIMIT 1';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        header('Location: ../login.php?error=No account found with that email.');
        exit;
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        header('Location: ../login.php?error=Incorrect password.');
        exit;
    }

    updateSessionUserData($user);

    if ($user['role'] === 'admin') {
        header('Location: ../admin/admin.php');
        exit;
    }

    header('Location: ../index.php?success=Welcome back, ' . urlencode($user['full_name']) . '!');
    exit;
}
?>
