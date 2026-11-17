<?php
session_start();
require_once '../config/db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

// Check if a booking ID was passed
if (!isset($_GET['booking_id'])) {
    die("No booking selected. <a href='dashboard/user.php'>Go back</a>");
}

$bookingId = $_GET['booking_id'];
$userId = $_SESSION['user_id'];

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $comment = htmlspecialchars($_POST['comment']);
    $vendorId = 1; // For simplicity, we assign this review to vendor ID 1

    // Insert the review into the database
    $stmt = $conn->prepare("INSERT INTO reviews (booking_id, vendor_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $bookingId, $vendorId, $rating, $comment);
    
    if ($stmt->execute()) {
        header("Location: dashboard/user.php?success=ReviewSubmitted");
        exit();
    } else {
        echo "Error submitting review: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave a Review - Miyen Tents</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .review-container { max-width: 500px; margin: 60px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .review-container h2 { color: #F15A24; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-family: inherit; }
        .btn-submit { width: 100%; background: #F15A24; color: white; padding: 12px; border: none; border-radius: 30px; font-size: 1rem; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-submit:hover { background: #d1491c; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="review-container">
        <h2>Review Your Event</h2>
        <p style="color: #666; margin-bottom: 20px;">We'd love to hear how your event went!</p>

        <form method="POST">
            <div class="form-group">
                <label>Rating (1-5 Stars)</label>
                <select name="rating" required>
                    <option value="5">5 - Excellent ⭐⭐⭐⭐⭐</option>
                    <option value="4">4 - Good ⭐⭐⭐⭐</option>
                    <option value="3">3 - Average ⭐⭐⭐</option>
                    <option value="2">2 - Poor ⭐⭐</option>
                    <option value="1">1 - Terrible ⭐</option>
                </select>
            </div>
            <div class="form-group">
                <label>Comment</label>
                <textarea name="comment" rows="4" placeholder="Tell us about the tents, service, and setup..." required></textarea>
            </div>
            <button type="submit" class="btn-submit">Submit Review</button>
        </form>
    </div>
</body>
</html>