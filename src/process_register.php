<?php
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize Inputs
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'user'; // Capture role

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
        header("Location: ../views/register.php?error=EmailAlreadyExists");
        exit();
    }
    $stmt->close();

    // 4. Hash Password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert into Database (INCLUDING ROLE)
    // Vendors need admin approval, so is_approved defaults to FALSE for vendors
    $isApproved = ($role === 'vendor') ? 0 : 1;
    
    $stmt = $conn->prepare("INSERT INTO users (first_name, surname, email, password_hash, role, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $firstName, $surname, $email, $passwordHash, $role, $isApproved);

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