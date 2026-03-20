<?php
include '../includes/auth.php';
requireAdmin();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donation_id = isset($_POST['donation_id']) ? (int) $_POST['donation_id'] : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    $allowed_statuses = ['pending', 'verified', 'completed'];

    if ($donation_id <= 0 || !in_array($status, $allowed_statuses)) {
        header("Location: ../admin/admin.php?error=Invalid donation update request.");
        exit;
    }

    $sql = "UPDATE donations SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $donation_id);

    if ($stmt->execute()) {
        header("Location: ../admin/admin.php?success=Donation status updated successfully.");
        exit;
    } else {
        header("Location: ../admin/admin.php?error=Failed to update donation status.");
        exit;
    }
}
?>