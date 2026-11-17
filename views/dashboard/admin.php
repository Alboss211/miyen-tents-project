<?php
session_start();
require_once '../../config/db_connect.php';

// SECURITY: Must be an admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/index.php");
    exit();
}

$message = "";

// --- HANDLE ACTIONS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_vendor'])) {
        $stmt = $conn->prepare("UPDATE users SET is_approved = TRUE WHERE id = ?");
        $stmt->bind_param("i", $_POST['vendor_id']);
        $stmt->execute();
        $message = "Vendor approved!";
    }
    if (isset($_POST['reject_vendor'])) {
        $stmt = $conn->prepare("UPDATE users SET is_approved = FALSE WHERE id = ?");
        $stmt->bind_param("i", $_POST['vendor_id']);
        $stmt->execute();
        $message = "Vendor access revoked.";
    }
    if (isset($_POST['delete_service'])) {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $_POST['product_id']);
        $stmt->execute();
        $message = "Service deleted.";
    }
    if (isset($_POST['confirm_booking'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $_POST['booking_id']);
        $stmt->execute();
        $message = "Booking marked as PAID.";
    }
}

// --- FETCH STATISTICS ---
$userCount = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];
$vendorCount = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='vendor'")->fetch_assoc()['total'];
$bookingCount = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$revenue = $conn->query("SELECT SUM(p.price) as total FROM bookings b JOIN products p ON b.tent_type = p.name WHERE b.status='confirmed'")->fetch_assoc()['total'] ?? 0;

// --- FETCH DATA ---
$vendors = $conn->query("SELECT * FROM users WHERE role='vendor'");
$bookings = $conn->query("SELECT b.*, u.first_name, u.surname FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC");
$services = $conn->query("SELECT p.*, u.first_name as vendor_name FROM products p JOIN users u ON p.vendor_id = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Miyen Tents</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .admin-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid #F15A24; }
        .stat-card h3 { color: #666; font-size: 0.9rem; margin-bottom: 10px; }
        .stat-card p { color: #F15A24; font-size: 2rem; font-weight: bold; }
        
        .section-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .section-box h3 { margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; }
        
        .status-paid { color: white; background: #28a745; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; }
        .status-pending { color: white; background: #dc3545; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; }
        .btn-action { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 0.8rem; margin-right: 5px; }
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-delete { background: #333; color: white; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="admin-container">
        <h2>Admin Control Panel</h2>
        <?php if($message): ?><p style="color: green; font-weight: bold; background: #d4edda; padding: 10px; border-radius: 5px;"><?php echo $message; ?></p><?php endif; ?>

        <!-- 5. View Platform Statistics -->
        <div class="stats-grid">
            <div class="stat-card"><h3>Total Customers</h3><p><?php echo $userCount; ?></p></div>
            <div class="stat-card"><h3>Total Vendors</h3><p><?php echo $vendorCount; ?></p></div>
            <div class="stat-card"><h3>Total Bookings</h3><p><?php echo $bookingCount; ?></p></div>
            <div class="stat-card"><h3>Confirmed Revenue</h3><p>KES <?php echo number_format($revenue); ?></p></div>
        </div>

        <!-- 1. Approve or Reject Vendors -->
        <div class="section-box">
            <h3>Vendor Approvals</h3>
            <table>
                <tr><th>Vendor Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
                <?php while($v = $vendors->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($v['first_name'] . ' ' . $v['surname']); ?></td>
                    <td><?php echo htmlspecialchars($v['email']); ?></td>
                    <td><?php echo $v['is_approved'] ? '<span style="color:green;">Approved</span>' : '<span style="color:red;">Pending</span>'; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="vendor_id" value="<?php echo $v['id']; ?>">
                            <?php if(!$v['is_approved']): ?>
                                <button type="submit" name="approve_vendor" class="btn-action btn-approve">Approve</button>
                            <?php else: ?>
                                <button type="submit" name="reject_vendor" class="btn-action btn-reject">Revoke</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- 3. Monitor Bookings -->
        <div class="section-box">
            <h3>Monitor All Bookings</h3>
            <table>
                <tr><th>Customer</th><th>Tent</th><th>Date</th><th>Location</th><th>Status</th><th>Action</th></tr>
                <?php while($b = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($b['first_name'] . ' ' . $b['surname']); ?></td>
                    <td><?php echo htmlspecialchars($b['tent_type']); ?></td>
                    <td><?php echo htmlspecialchars($b['event_date']); ?></td>
                    <td><?php echo htmlspecialchars($b['location']); ?></td>
                    <td>
                        <?php if($b['status'] == 'confirmed'): ?>
                            <span class="status-paid">PAID</span>
                        <?php else: ?>
                            <span class="status-pending">PENDING</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($b['status'] == 'pending' && $b['payment_proof']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                <button type="submit" name="confirm_booking" class="btn-action btn-approve">Confirm Payment</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- 4. Manage Uploaded Services -->
        <div class="section-box">
            <h3>Manage Platform Services</h3>
            <table>
                <tr><th>Service Name</th><th>Vendor</th><th>Price</th><th>Action</th></tr>
                <?php while($s = $services->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['name']); ?></td>
                    <td><?php echo htmlspecialchars($s['vendor_name']); ?></td>
                    <td>KES <?php echo htmlspecialchars($s['price']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $s['id']; ?>">
                            <button type="submit" name="delete_service" class="btn-action btn-delete" onclick="return confirm('Delete this service?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>
</body>
</html>