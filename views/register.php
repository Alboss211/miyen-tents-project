<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Miyen Tents</title>
    
    <!-- Using standard relative paths -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-wrapper">
    
    <!-- Left Side Image -->
    <div class="auth-image">
        <div class="auth-logo-box">
            <!-- We will put your logo here later -->
            <h1 style="font-size: 3rem; margin: 0; letter-spacing: 2px;">MIYEN</h1>
            <h2 style="font-weight: 300; letter-spacing: 5px; margin: 0;">TENTS</h2>
        </div>
    </div>

    <!-- Right Side Form -->
    <div class="auth-form-container">
        <h2>Create Account</h2>
        
        <form action="../src/process_register.php" method="POST" id="registerForm">
            
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" placeholder="Enter First Name" required>
            </div>

            <div class="form-group">
                <label>Surname</label>
                <input type="text" name="surname" placeholder="Enter Surname" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter Email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password" placeholder="Enter Password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            </div>
            <div class="form-group">
    <label>Account Type</label>
    <select name="role" style="width: 100%; padding: 14px 20px; border: 1px solid #ccc; border-radius: 30px; outline: none;">
        <option value="user">I am a Customer (Looking to book)</option>
        <option value="vendor">I am a Vendor (Looking to sell)</option>
    </select>
</div>
            <button type="submit" class="btn-submit">Create Account</button>
            
            <div class="auth-bottom-link">
                Have an account? <a href="login.php">Login</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>