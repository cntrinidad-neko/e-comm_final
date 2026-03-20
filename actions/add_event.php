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

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');
$type = trim($_POST['type'] ?? 'event');
$contact_person = trim($_POST['contact_person'] ?? '');
$contact_name = trim($_POST['contact_name'] ?? '');
$contact_phone = trim($_POST['contact_phone'] ?? '');
$status = trim($_POST['status'] ?? 'active');

if ($title === '' || $description === '' || $location === '' || $event_date === '') {
    header("Location: ../admin/admin.php?section=events&error=" . urlencode("Please fill in all required event fields."));
    exit;
}

$sql = "INSERT INTO events (title, description, location, event_date, type, contact_person, contact_name, contact_phone, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../admin/admin.php?section=events&error=" . urlencode("Failed to prepare event creation."));
    exit;
}

$stmt->bind_param(
    "sssssssss",
    $title,
    $description,
    $location,
    $event_date,
    $type,
    $contact_person,
    $contact_name,
    $contact_phone,
    $status
);

if ($stmt->execute()) {
    header("Location: ../admin/admin.php?section=events&success=" . urlencode("Event created successfully."));
    exit;
}

header("Location: ../admin/admin.php?section=events&error=" . urlencode("Failed to create event."));
exit;