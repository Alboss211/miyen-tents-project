<?php
session_start();
require_once '../config/db_connect.php';

// SECURITY: Require login before processing a booking
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php?error=PleaseLoginToBook");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $userId = $_SESSION['user_id'];
    
    // Sanitize inputs from the payment page
    $eventDate = htmlspecialchars($_POST['event_date']);
    $location = htmlspecialchars($_POST['location']);
    $totalAmount = htmlspecialchars($_POST['total_amount']);
    $items = $_POST['items'] ?? [];

    // Determine the primary tent name (for the admin dashboard view)
    // Since we might have multiple items, we just take the first one for the 'tent_type' column
    $primaryTent = 'Custom Package';
    foreach ($items as $itemId => $quantity) {
        if ($quantity > 0) {
            $stmt = $conn->prepare("SELECT name FROM event_items WHERE id = ?");
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result) {
                $primaryTent = $result['name'];
                break; 
            }
        }
    }

    // --- FILE UPLOAD LOGIC (Payment Proof) ---
    $paymentProof = NULL;
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $targetDir = "../uploads/"; 
        
        if (!file_exists($targetDir)) { 
            mkdir($targetDir, 0777, true); 
        }
        
        $fileName = time() . "_" . basename($_FILES["payment_proof"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowTypes = array('jpg', 'png', 'jpeg', 'pdf');
        
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetFilePath)) {
                $paymentProof = $fileName; 
            }
        }
    }

    // Insert into database (now includes total_amount)
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, location, event_date, tent_type, payment_proof, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssd", $userId, $location, $eventDate, $primaryTent, $paymentProof, $totalAmount);

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