<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in (Optional: you can force them to login before booking)
// if(!isset($_SESSION['user_id'])) {
//     header("Location: ../views/login.php");
//     exit();
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize inputs
    $tentType = htmlspecialchars($_POST['tent_type']);
    $attendees = htmlspecialchars($_POST['attendees']);
    $eventDate = htmlspecialchars($_POST['event_date']);
    $location = htmlspecialchars($_POST['location']);
    
    // For now, let's use a dummy user ID if they aren't logged in.
    // In a real app, you'd use $_SESSION['user_id']
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, location, event_date, num_attendees, tent_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $userId, $location, $eventDate, $attendees, $tentType);

    if ($stmt->execute()) {
        // Success! Redirect to confirmation page
        header("Location: ../views/booking-confirmed.php");
        exit();
    } else {
        echo "Error processing booking: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>