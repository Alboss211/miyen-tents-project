<?php
session_start();
require_once '../config/db_connect.php';

// SECURITY: Require login before processing a booking
if (!isset($_SESSION['user_id'])) {
    header("Location: /views/login.php?error=PleaseLoginToBook");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $userId = $_SESSION['user_id'];
    
    // Sanitize inputs from the payment page
    $eventDate = htmlspecialchars($_POST['event_date']);
    $location = htmlspecialchars($_POST['location']);
    $totalAmount = htmlspecialchars($_POST['total_amount'] ?? 0.00);
    $items = $_POST['items'] ?? [];
    
    // FIX: Provide a default value for the database requirement
    $numAttendees = 100; 

    // Determine the primary tent name (for the admin dashboard view)
    $primaryTent = 'Custom Package';
    $itemNames = [];
    
    foreach ($items as $itemId => $quantity) {
        if ($quantity > 0) {
            $stmt = $conn->prepare("SELECT name FROM event_items WHERE id = ?");
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result) {
                $itemNames[] = $result['name'] . " (x" . $quantity . ")";
            }
        }
    }
    
    if (!empty($itemNames)) {
        $primaryTent = implode(", ", $itemNames); // e.g., "Chairs (x2), Tables (x1)"
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

    // FIX: Added num_attendees to the INSERT statement to stop the 500 error
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, location, event_date, num_attendees, tent_type, payment_proof, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississd", $userId, $location, $eventDate, $numAttendees, $primaryTent, $paymentProof, $totalAmount);

    if ($stmt->execute()) {
        // Success! Redirect directly to the User Dashboard
        header("Location: /views/dashboard/user.php?success=BookingSubmitted");
        exit();
    } else {
        echo "Error processing booking: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>