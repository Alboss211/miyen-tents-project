<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $newPassword = $_POST['new_password'];
    $userId = $_SESSION['user_id'];

    if (strlen($newPassword) < 6) {
        die("Password must be at least 6 characters.");
    }

    // Hash the new password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update database
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $newHash, $userId);
    
    if ($stmt->execute()) {
        header("Location: ../views/dashboard/user.php?success=PasswordUpdated");
        exit();
    } else {
        echo "Error updating password.";
    }
}
?>