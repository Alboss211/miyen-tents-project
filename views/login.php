<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Miyen Tents</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-wrapper">
    
    <!-- Left Side Image -->
    <div class="auth-image">
        <div class="auth-logo-box">
            <h1 style="font-size: 3rem; margin: 0; letter-spacing: 2px;">MIYEN</h1>
            <h2 style="font-weight: 300; letter-spacing: 5px; margin: 0;">TENTS</h2>
        </div>
    </div>

    <!-- Right Side Form -->
    <div class="auth-form-container">
        <h2>Login</h2>
        
        <!-- Action will point to backend later -->
        <form action="../src/process_login.php" method="POST">
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter Email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter Password" required>
            </div>

            <button type="submit" class="btn-submit">Login</button>
            
            <div class="auth-bottom-link">
                Don't have an account? <a href="register.php">Create Account</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>