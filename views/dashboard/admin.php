<?php
session_start();
require_once '../../config/db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/index.php");
    exit();
}

$message = "";
$searchTerm = "";

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
    if (isset($_POST['deactivate_user'])) {
        $stmt = $conn->prepare("UPDATE users SET account_status = 'deactivated' WHERE id = ?");
        $stmt->bind_param("i", $_POST['user_id']);
        $stmt->execute();
        $message = "User account deactivated.";
    }
    if (isset($_POST['reactivate_user'])) {
        $stmt = $conn->prepare("UPDATE users SET account_status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $_POST['user_id']);
        $stmt->execute();
        $message = "User account reactivated.";
    }
    
    // NEW: Confirm Booking & Start Delivery
    if (isset($_POST['confirm_booking'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed', delivery_status = 'out for delivery' WHERE id = ?");
        $stmt->bind_param("i", $_POST['booking_id']);
        $stmt->execute();
        $message = "Booking confirmed! Delivery is now out for delivery.";
    }

    // NEW: Reject Booking
    if (isset($_POST['reject_booking'])) {
        $reason = htmlspecialchars($_POST['rejection_reason']);
        $stmt = $conn->prepare("UPDATE bookings SET status = 'pending', rejection_reason = ?, delivery_status = 'pending' WHERE id = ?");
        $stmt->bind_param("si", $reason, $_POST['booking_id']);
        $stmt->execute();
        $message = "Booking rejected. Reason sent to customer.";
    }

    // NEW: Update Delivery Status manually
    if (isset($_POST['update_delivery'])) {
        $newStatus = $_POST['delivery_status'];
        $stmt = $conn->prepare("UPDATE bookings SET delivery_status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $_POST['booking_id']);
        $stmt->execute();
        $message = "Delivery status updated.";
    }

    if (isset($_POST['delete_service'])) {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $_POST['product_id']);
        $stmt->execute();
        $message = "Service deleted.";
    }
}

// --- FETCH STATISTICS ---
$userCount = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];
$vendorCount = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='vendor'")->fetch_assoc()['total'];
$bookingCount = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$revenue = $conn->query("SELECT SUM(p.price) as total FROM bookings b JOIN products p ON b.tent_type = p.name WHERE b.status='confirmed'")->fetch_assoc()['total'] ?? 0;

// --- FETCH DATA WITH SEARCH ---
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchWildcard = "%" . $searchTerm . "%";
    
    // Search Users
    $stmt = $conn->prepare("SELECT * FROM users WHERE first_name LIKE ? OR surname LIKE ? OR email LIKE ? OR id = ?");
    $stmt->bind_param("sssi", $searchWildcard, $searchWildcard, $searchWildcard, $searchTerm);
    $stmt->execute();
    $allUsers = $stmt->get_result();
    
    // Search Bookings
    $stmt = $conn->prepare("SELECT b.*, u.first_name, u.surname FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.id = ? OR u.first_name LIKE ? OR u.surname LIKE ?");
    $stmt->bind_param("iss", $searchTerm, $searchWildcard, $searchWildcard);
    $stmt->execute();
    $bookings = $stmt->get_result();
} else {
    $allUsers = $conn->query("SELECT * FROM users ORDER BY role, first_name");
    $bookings = $conn->query("SELECT b.*, u.first_name, u.surname FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC");
}

$vendors = $conn->query("SELECT * FROM users WHERE role='vendor'");
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
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid #F15A24; cursor: pointer; transition: 0.2s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-card.active-card { border: 2px solid #F15A24; background: #fff8f5; }
        .stat-card h3 { color: #666; font-size: 0.9rem; margin-bottom: 10px; }
        .stat-card p { color: #F15A24; font-size: 2rem; font-weight: bold; }
        
        .search-bar { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-bar input { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 8px; }
        .search-bar button { padding: 12px 25px; background: #F15A24; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
        
        .section-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; display: none; }
        .section-box.active-section { display: block; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .section-box h3 { margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { background: #f9f9f9; }
        
        .status-paid { color: white; background: #28a745; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; }
        .status-pending { color: white; background: #dc3545; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; }
        .status-active { color: green; font-weight: bold; }
        .status-deactivated { color: red; font-weight: bold; }
        
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

        <!-- SEARCH BAR -->
        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search by User ID, Name, Email, or Booking ID..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Search</button>
            <?php if($searchTerm): ?>
                <a href="admin.php" style="padding: 12px 20px; background: #333; color: white; text-decoration: none; border-radius: 8px;">Clear</a>
            <?php endif; ?>
        </form>

        <div class="stats-grid">
            <div class="stat-card active-card" onclick="showSection('users-section', this)">
                <h3>Total Customers</h3><p><?php echo $userCount; ?></p>
            </div>
            <div class="stat-card" onclick="showSection('vendors-section', this)">
                <h3>Total Vendors</h3><p><?php echo $vendorCount; ?></p>
            </div>
            <div class="stat-card" onclick="showSection('bookings-section', this)">
                <h3>Total Bookings</h3><p><?php echo $bookingCount; ?></p>
            </div>
            <div class="stat-card" onclick="showSection('revenue-section', this)">
                <h3>Confirmed Revenue</h3><p>KES <?php echo number_format($revenue); ?></p>
            </div>
        </div>

        <!-- USERS SECTION -->
        <div id="users-section" class="section-box active-section">
            <h3>Manage All Accounts</h3>
            <table>
                <tr><th>ID</th><th>Full Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr>
                <?php while($u = $allUsers->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['surname']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span style="text-transform: capitalize;"><?php echo htmlspecialchars($u['role']); ?></span></td>
                    <td class="<?php echo $u['account_status'] == 'active' ? 'status-active' : 'status-deactivated'; ?>">
                        <?php echo ucfirst($u['account_status']); ?>
                    </td>
                    <td>
                        <?php if($u['role'] !== 'admin'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <?php if($u['account_status'] == 'active'): ?>
                                <button type="submit" name="deactivate_user" class="btn-action btn-reject">Deactivate</button>
                            <?php else: ?>
                                <button type="submit" name="reactivate_user" class="btn-action btn-approve">Reactivate</button>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- VENDORS SECTION -->
        <div id="vendors-section" class="section-box">
            <h3>Vendor Approvals</h3>
            <table>
                <tr><th>ID</th><th>Vendor Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
                <?php while($v = $vendors->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $v['id']; ?></td>
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

        <!-- BOOKINGS SECTION -->
        <div id="bookings-section" class="section-box">
            <h3>Monitor All Bookings</h3>
            <table>
                <tr><th>ID</th><th>Customer</th><th>Tent</th><th>Date</th><th>Proof</th><th>Status</th><th>Delivery</th><th>Action</th></tr>
                <?php while($b = $bookings->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $b['id']; ?></td>
                    <td><?php echo htmlspecialchars($b['first_name'] . ' ' . $b['surname']); ?></td>
                    <td><?php echo htmlspecialchars($b['tent_type']); ?></td>
                    <td><?php echo htmlspecialchars($b['event_date']); ?></td>
                    <td>
                        <?php if($b['payment_proof']): ?>
                            <a href="../../uploads/<?php echo $b['payment_proof']; ?>" target="_blank" style="color: #F15A24; font-weight: bold;">View</a>
                        <?php else: ?>
                            <span style="color: #999;">None</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($b['status'] == 'confirmed'): ?>
                            <span class="status-paid">PAID</span>
                        <?php else: ?>
                            <span class="status-pending">PENDING</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                            <select name="delivery_status" style="padding: 5px; border-radius: 5px; border: 1px solid #ccc;" onchange="this.form.submit()">
                                <option value="pending" <?php if($b['delivery_status']=='pending') echo 'selected'; ?>>Pending</option>
                                <option value="out for delivery" <?php if($b['delivery_status']=='out for delivery') echo 'selected'; ?>>Out for Delivery</option>
                                <option value="delivered" <?php if($b['delivery_status']=='delivered') echo 'selected'; ?>>Delivered</option>
                            </select>
                            <input type="hidden" name="update_delivery" value="1">
                        </form>
                    </td>
                    <td>
                        <?php if($b['status'] == 'pending' && $b['payment_proof']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                <button type="submit" name="confirm_booking" class="btn-action btn-approve" onclick="return confirm('Confirm payment and start delivery?')">Approve</button>
                            </form>
                            <form method="POST" style="display:inline; margin-top: 5px;">
                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                <input type="text" name="rejection_reason" placeholder="Reason for rejection..." required style="padding: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.8rem; width: 120px;">
                                <button type="submit" name="reject_booking" class="btn-action btn-reject" onclick="return confirm('Reject this booking?')">Reject</button>
                            </form>
                        <?php elseif($b['status'] == 'confirmed'): ?>
                            <span style="color: green; font-weight: bold;">Approved</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- REVENUE / SERVICES SECTION -->
        <div id="revenue-section" class="section-box">
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

    <script>
        function showSection(sectionId, cardElement) {
            document.querySelectorAll('.section-box').forEach(box => box.classList.remove('active-section'));
            document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active-card'));
            document.getElementById(sectionId).classList.add('active-section');
            cardElement.classList.add('active-card');
        }
    </script>
</body>
</html>