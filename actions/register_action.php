<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

ensureUserProfileColumns($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = '';

    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        header('Location: ../register.php?error=All fields are required.');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../register.php?error=Invalid email format.');
        exit;
    }

    if ($password !== $confirm_password) {
        header('Location: ../register.php?error=Passwords do not match.');
        exit;
    }

    if (strlen($password) < 8) {
        header('Location: ../register.php?error=Password must be at least 8 characters.');
        exit;
    }

    $check_sql = 'SELECT id FROM users WHERE email = ? LIMIT 1';
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        header('Location: ../register.php?error=Email already registered.');
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (full_name, email, phone, address, password, role) VALUES (?, ?, ?, ?, ?, 'user')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $full_name, $email, $phone, $address, $hashed_password);

    if ($stmt->execute()) {
        header('Location: ../login.php?success=Registration successful. Please login.');
        exit;
    }

    header('Location: ../register.php?error=Registration failed.');
    exit;
}
?>
