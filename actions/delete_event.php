<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: ../admin/admin.php?section=events&error=" . urlencode("Invalid event ID."));
    exit;
}

$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");

if (!$stmt) {
    header("Location: ../admin/admin.php?section=events&error=" . urlencode("Failed to prepare delete request."));
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../admin/admin.php?section=events&success=" . urlencode("Event deleted successfully."));
    exit;
}

header("Location: ../admin/admin.php?section=events&error=" . urlencode("Failed to delete event."));
exit;