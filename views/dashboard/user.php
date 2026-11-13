<?php
session_start();
require_once '../../config/db_connect.php';

// SECURITY: Must be logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../views/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user's bookings
$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard - Miyen Tents</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .dash-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        
        /* Updated dashboard-header to handle buttons */
        .dashboard-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
        }
        
        .header-actions a {
            text-decoration: none;
            margin-left: 10px;
        }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 40px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: bold; }
        
        .status-paid { color: white; background: #28a745; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .status-pending { color: white; background: #dc3545; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        
        .reset-box { background: #f9f9f9; padding: 20px; border-radius: 10px; border: 1px solid #ddd; }
        .reset-box input { padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-right: 10px; }
        .reset-box button { padding: 10px 20px; background: #F15A24; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dash-container">
        
        <!-- UPDATED HEADER SECTION -->
        <div class="dashboard-header">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
            <div class="header-actions">
                <a href="../../views/index.php" class="btn btn-outline">Go to Homepage</a>
                <a href="../../views/book-now.php" class="btn btn-orange">Book a New Tent</a>
            </div>
        </div>

        <h3>My Bookings</h3>
        <table>
            <thead>
                <tr>
                    <th>Event Date</th>
                    <th>Tent Type</th>
                    <th>Location</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($bookings->num_rows > 0): ?>
                    <?php while($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['tent_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td>
                            <?php if($row['status'] == 'confirmed'): ?>
                                <span class="status-paid">PAID</span>
                            <?php else: ?>
                                <span class="status-pending">PENDING PAYMENT</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">You have no bookings yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="reset-box">
            <h3>Reset Password</h3>
            <form action="../../src/process_reset_password.php" method="POST">
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <button type="submit">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html>