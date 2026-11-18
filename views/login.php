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
        
        <!-- Show error message if user tried to book without logging in -->
        <?php if(isset($_GET['error']) && $_GET['error'] == 'PleaseLoginToBook'): ?>
            <p style="color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-weight: 500;">
                ⚠️ Please log in or create an account to make a booking.
            </p>
        <?php endif; ?>

        <!-- Show error message if login failed (wrong password) -->
        <?php if(isset($_GET['error']) && $_GET['error'] == 'IncorrectPassword'): ?>
            <p style="color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-weight: 500;">
                ❌ Incorrect password. Please try again.
            </p>
        <?php endif; ?>

        <!-- Show error message if user not found -->
        <?php if(isset($_GET['error']) && $_GET['error'] == 'UserNotFound'): ?>
            <p style="color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-weight: 500;">
                ❌ No account found with that email.
            </p>
        <?php endif; ?>

        <!-- Show error message if account is deactivated -->
        <?php if(isset($_GET['error']) && $_GET['error'] == 'AccountDeactivated'): ?>
            <p style="color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-weight: 500;">
                🚫 Your account has been deactivated. Please contact support.
            </p>
        <?php endif; ?>

        <!-- Show error message if vendor is pending approval -->
        <?php if(isset($_GET['error']) && $_GET['error'] == 'PendingApproval'): ?>
            <p style="color: #856404; background: #fff3cd; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-weight: 500;">
                ⏳ Your vendor account is pending admin approval. Please check back later.
            </p>
        <?php endif; ?>

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