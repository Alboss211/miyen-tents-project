<?php
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize Inputs (Prevent XSS)
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // 2. Server-side Validation
    if (empty($firstName) || empty($surname) || empty($email) || empty($password)) {
        die("Please fill in all fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }

    // 3. Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Email exists, redirect back with error
        header("Location: ../views/register.php?error=EmailAlreadyExists");
        exit();
    }
    $stmt->close();

    // 4. Hash Password (RUBRIC REQUIREMENT: Secure sensitive data)
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert into Database
    $stmt = $conn->prepare("INSERT INTO users (first_name, surname, email, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstName, $surname, $email, $passwordHash);

    if ($stmt->execute()) {
        // Success! Redirect to login page
        header("Location: ../views/login.php?success=AccountCreated");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>