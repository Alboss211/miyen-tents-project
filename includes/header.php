<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/miyen-tents-project/assets/css/style.css">
</head>
<body>
       <nav class="navbar">
        <div class="logo">
            <a href="/miyen-tents-project/views/index.php">
                <h2 style="color: #F15A24; margin: 0;">MIYEN <span style="color: #333; font-weight: 300;">TENTS</span></h2>
            </a>
        </div>

        <div class="nav-links">
            <?php if(isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'vendor'])): ?>
                
                <!-- ADMIN & VENDOR VIEW -->
                <?php if($_SESSION['role'] === 'admin'): ?>
                    <a href="/miyen-tents-project/views/dashboard/admin.php" style="color: #F15A24; font-weight: bold;">Admin Dashboard</a>
                <?php else: ?>
                    <a href="/miyen-tents-project/views/dashboard/vendor.php" style="color: #F15A24; font-weight: bold;">Vendor Dashboard</a>
                <?php endif; ?>
                
                <a href="/miyen-tents-project/src/process_logout.php" class="btn btn-outline">Logout</a>

            <?php else: ?>
                
                <!-- CUSTOMER & GUEST VIEW -->
                <a href="/miyen-tents-project/views/index.php">Home</a>
                <a href="/miyen-tents-project/views/book-now.php" style="color: #F15A24; font-weight: bold;">Book Now</a>
                <a href="/miyen-tents-project/views/gallery.php">Gallery</a>
                <a href="#">Contacts</a>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="/miyen-tents-project/views/dashboard/user.php" style="color: #F15A24; font-weight: bold;">My Dashboard</a>
                    <a href="/miyen-tents-project/src/process_logout.php" class="btn btn-outline">Logout</a>
                <?php else: ?>
                    <a href="/miyen-tents-project/views/login.php" class="btn btn-outline">Login</a>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </nav>
</body>
</html>