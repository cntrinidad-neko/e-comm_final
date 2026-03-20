<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/admin.php?error=" . urlencode("Invalid request."));
    exit;
}

$id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');
$type = trim($_POST['type'] ?? 'event');
$contact_person = trim($_POST['contact_person'] ?? '');
$contact_name = trim($_POST['contact_name'] ?? '');
$contact_phone = trim($_POST['contact_phone'] ?? '');
$status = trim($_POST['status'] ?? 'active');

if ($id <= 0 || $title === '' || $description === '' || $location === '' || $event_date === '') {
    header("Location: ../admin/admin.php?section=events&error=" . urlencode("Please fill in all required event fields."));
    exit;
}

$sql = "UPDATE events
        SET title = ?, description = ?, location = ?, event_date = ?, type = ?, contact_person = ?, contact_name = ?, contact_phone = ?, status = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../admin/admin.php?section=events&error=" . urlencode("Failed to prepare event update."));
    exit;
}

$stmt->bind_param(
    "sssssssssi",
    $title,
    $description,
    $location,
    $event_date,
    $type,
    $contact_person,
    $contact_name,
    $contact_phone,
    $status,
    $id
);

if ($stmt->execute()) {
    header("Location: ../admin/admin.php?section=events&success=" . urlencode("Event updated successfully."));
    exit;
}

header("Location: ../admin/admin.php?section=events&error=" . urlencode("Failed to update event."));
exit;