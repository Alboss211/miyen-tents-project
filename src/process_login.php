<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        die("Please enter both email and password.");
    }

    // 1. Find the user by email (Make sure we select the role!)
    $stmt = $conn->prepare("SELECT id, first_name, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // 2. Verify the password
        if (password_verify($password, $user['password_hash'])) {
            
            // 3. Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role']; 
            
                        // 4. Redirect based on Role!
            if ($user['role'] === 'admin') {
                header("Location: ../views/dashboard/admin.php");
            } elseif ($user['role'] === 'vendor') {
                header("Location: ../views/dashboard/vendor.php");
            } else {
                // Change this line!
                header("Location: ../views/dashboard/user.php"); 
            }
            exit();
            
        } else {
            header("Location: ../views/login.php?error=IncorrectPassword");
            exit();
        }
    } else {
        header("Location: ../views/login.php?error=UserNotFound");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>