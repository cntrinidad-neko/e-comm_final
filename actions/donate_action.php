<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config/db.php';
include '../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

    $donor_name = sanitize($_POST['donor_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    $reference_number = sanitize($_POST['reference_number'] ?? '');
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $recurring = isset($_POST['recurring']) ? 1 : 0;
    $message = sanitize($_POST['message'] ?? '');
    $proof_image_name = null;

    if (empty($donor_name) || empty($email) || $amount <= 0 || empty($payment_method) || empty($reference_number)) {
        header("Location: ../donate.php?error=" . urlencode("Please fill in all required fields."));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../donate.php?error=" . urlencode("Invalid email address."));
        exit;
    }

    if ($amount <= 0) {
        header("Location: ../donate.php?error=" . urlencode("Donation amount must be greater than zero."));
        exit;
    }

    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = '../uploads/proofs/';

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                header("Location: ../donate.php?error=" . urlencode("Failed to create upload folder."));
                exit;
            }
        }

        if ($_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
            header("Location: ../donate.php?error=" . urlencode("Upload error code: " . $_FILES['proof_image']['error']));
            exit;
        }

        $file_tmp = $_FILES['proof_image']['tmp_name'];
        $original_name = $_FILES['proof_image']['name'];
        $file_size = $_FILES['proof_image']['size'];

        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($file_ext, $allowed_ext)) {
            header("Location: ../donate.php?error=" . urlencode("Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed."));
            exit;
        }

        if ($file_size > 5 * 1024 * 1024) {
            header("Location: ../donate.php?error=" . urlencode("File is too large. Maximum is 5MB."));
            exit;
        }

        $image_info = @getimagesize($file_tmp);
        if ($image_info === false) {
            header("Location: ../donate.php?error=" . urlencode("Uploaded file is not a valid image."));
            exit;
        }

        $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($image_info['mime'], $allowed_mime)) {
            header("Location: ../donate.php?error=" . urlencode("Invalid image format."));
            exit;
        }

        $file_name = 'proof_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;

        if (!move_uploaded_file($file_tmp, $target_file)) {
            header("Location: ../donate.php?error=" . urlencode("Failed to upload proof of payment."));
            exit;
        }

        $proof_image_name = $file_name;
    }

    $sql = "INSERT INTO donations 
            (user_id, donor_name, email, phone, amount, payment_method, reference_number, anonymous, recurring, message, proof_image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        header("Location: ../donate.php?error=" . urlencode("Database prepare failed."));
        exit;
    }

    $stmt->bind_param(
        "isssdssiiss",
        $user_id,
        $donor_name,
        $email,
        $phone,
        $amount,
        $payment_method,
        $reference_number,
        $anonymous,
        $recurring,
        $message,
        $proof_image_name
    );

    if ($stmt->execute()) {
        header("Location: ../donate.php?success=" . urlencode("Donation submitted successfully. Please wait for admin verification."));
        exit;
    } else {
        header("Location: ../donate.php?error=" . urlencode("Failed to submit donation."));
        exit;
    }
}
?>