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
    
    // Get User ID
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

    // --- FILE UPLOAD LOGIC ---
    $paymentProof = NULL;
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $targetDir = "../uploads/"; 
        
        // Create the folder if it doesn't exist
        if (!file_exists($targetDir)) { 
            mkdir($targetDir, 0777, true); 
        }
        
        // Create a unique filename to prevent overwriting
        $fileName = time() . "_" . basename($_FILES["payment_proof"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Allow only specific file types
        $allowTypes = array('jpg', 'png', 'jpeg', 'pdf');
        
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetFilePath)) {
                $paymentProof = $fileName; // Save the filename for the database
            }
        }
    }

    // Insert into database (Now includes payment_proof)
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, location, event_date, num_attendees, tent_type, payment_proof) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $userId, $location, $eventDate, $attendees, $tentType, $paymentProof);

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