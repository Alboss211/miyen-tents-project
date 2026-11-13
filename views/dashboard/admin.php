<?php
session_start();
require_once '../../config/db_connect.php';

// SECURITY: Check if user is logged in AND is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // If not admin, kick them back to the homepage
    header("Location: ../../views/index.php");
    exit();
}
// ... rest of the file
// Fetch Stats
$userCount = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$bookingCount = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];

// Fetch Recent Bookings
$bookings = $conn->query("SELECT b.*, u.first_name, u.surname FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Miyen Tents</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .dash-container { max-width: 1100px; margin: 40px auto; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; }
        .stat-card h3 { color: #666; font-size: 1rem; margin-bottom: 10px; }
        .stat-card p { color: #F15A24; font-size: 2.5rem; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: bold; }
        
        .status-paid { color: white; background: #28a745; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .status-pending { color: white; background: #dc3545; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dash-container">
        <h2>Admin Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Registered Users</h3>
                <p><?php echo $userCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p><?php echo $bookingCount; ?></p>
            </div>
        </div>

        <h3>Recent Bookings</h3>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Event Date</th>
                    <th>Tent Type</th>
                    <th>Location</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?></td>
                    <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['tent_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td>
                        <?php if($row['status'] == 'confirmed'): ?>
                            <span class="status-paid">PAID / CONFIRMED</span>
                        <?php else: ?>
                            <span class="status-pending">PENDING / UNPAID</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>