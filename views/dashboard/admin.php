<?php
session_start();
require_once '../../config/db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /views/index.php");
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
    
    // Confirm Booking & Start Delivery
    if (isset($_POST['confirm_booking'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed', delivery_status = 'out for delivery' WHERE id = ?");
        $stmt->bind_param("i", $_POST['booking_id']);
        $stmt->execute();
        $message = "Booking confirmed! Delivery is now out for delivery.";
    }

    // Reject Booking
    if (isset($_POST['reject_booking'])) {
        $reason = htmlspecialchars($_POST['rejection_reason']);
        $stmt = $conn->prepare("UPDATE bookings SET status = 'pending', rejection_reason = ?, delivery_status = 'pending' WHERE id = ?");
        $stmt->bind_param("si", $reason, $_POST['booking_id']);
        $stmt->execute();
        $message = "Booking rejected. Reason sent to customer.";
    }

    // Update Delivery Status manually (With Security Check)
    if (isset($_POST['update_delivery'])) {
        $checkStmt = $conn->prepare("SELECT status FROM bookings WHERE id = ?");
        $checkStmt->bind_param("i", $_POST['booking_id']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result()->fetch_assoc();
        
        if ($checkResult['status'] === 'confirmed') {
            $newStatus = $_POST['delivery_status'];
            $stmt = $conn->prepare("UPDATE bookings SET delivery_status = ? WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $_POST['booking_id']);
            $stmt->execute();
            $message = "Delivery status updated.";
        } else {
            $message = "Error: Cannot update delivery. Payment not confirmed yet.";
        }
    }

    if (isset($_POST['delete_service'])) {
        $stmt = $conn->prepare("DELETE FROM event_items WHERE id = ?");
        $stmt->bind_param("i", $_POST['product_id']);
        $stmt->execute();
        $message = "Service deleted.";
    }
}

// --- FETCH STATISTICS ---
$userCount = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];
$vendorCount = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='vendor'")->fetch_assoc()['total'];
$bookingCount = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];

// SAFE REVENUE QUERY: This will not crash even if 'attendees' column is missing
$revenue = 0;
$checkColumn = $conn->query("SHOW COLUMNS FROM bookings LIKE 'attendees'");
if ($checkColumn && $checkColumn->num_rows > 0) {
    // Column exists, do the math
    $revenueQuery = $conn->query("
        SELECT SUM(ei.price * b.attendees) as total 
        FROM bookings b 
        JOIN event_items ei ON b.tent_type = ei.name 
        WHERE b.status = 'confirmed'
    ");
} else {
    // Column does not exist, just sum the base price to keep the page working
    $revenueQuery = $conn->query("
        SELECT SUM(ei.price) as total 
        FROM bookings b 
        JOIN event_items ei ON b.tent_type = ei.name 
        WHERE b.status = 'confirmed'
    ");
}
$revenue = $revenueQuery->fetch_assoc()['total'] ?? 0;

// --- FETCH DATA WITH SEARCH ---
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchWildcard = "%" . $searchTerm . "%";
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE first_name LIKE ? OR surname LIKE ? OR email LIKE ? OR id = ?");
    $stmt->bind_param("sssi", $searchWildcard, $searchWildcard, $searchWildcard, $searchTerm);
    $stmt->execute();
    $allUsers = $stmt->get_result();
    
    $stmt = $conn->prepare("SELECT b.*, u.first_name, u.surname FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.id = ? OR u.first_name LIKE ? OR u.surname LIKE ?");
    $stmt->bind_param("iss", $searchTerm, $searchWildcard, $searchWildcard);
    $stmt->execute();
    $bookings = $stmt->get_result();
} else {
    $allUsers = $conn->query("SELECT * FROM users ORDER BY role, first_name");
    $bookings = $conn->query("SELECT b.*, u.first_name, u.surname FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC");
}

$vendors = $conn->query("SELECT * FROM users WHERE role='vendor'");
$services = $conn->query("SELECT ei.id, ei.name, ei.price, u.first_name as vendor_name FROM event_items ei JOIN users u ON ei.vendor_id = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Miyen Tents</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        /* Sidebar Layout CSS */
        .dashboard-wrapper { display: flex; min-height: calc(100vh - 80px); background: #f4f6f9; }
        
        .sidebar { width: 260px; background: white; border-right: 1px solid #e0e0e0; padding: 20px 0; box-shadow: 2px 0 10px rgba(0,0,0,0.03); flex-shrink: 0; }
        .sidebar h3 { padding: 0 20px; margin-bottom: 15px; color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .sidebar-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; cursor: pointer; border-left: 4px solid transparent; transition: 0.2s; color: #333; font-weight: 500; }
        .sidebar-item:hover { background: #f9f9f9; }
        .sidebar-item.active { background: #fff8f5; border-left-color: #F15A24; color: #F15A24; font-weight: bold; }
        
        .sidebar-badge { background: #F15A24; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .sidebar-item.active .sidebar-badge { background: #dc3545; }
        
        /* Main Content Layout */
        .main-content { flex-grow: 1; padding: 30px; overflow-x: auto; }
        
        .search-bar { margin-bottom: 25px; display: flex; gap: 10px; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .search-bar input { flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; outline: none; }
        .search-bar button { padding: 10px 25px; background: #F15A24; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.2s; }
        .search-bar button:hover { background: #d94d1a; }
        
        .section-box { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: none; }
        .section-box.active-section { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .section-box h3 { margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; color: #333; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; color: #444; }
        th { background: #fcfcfc; font-weight: bold; color: #555; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        tr:hover { background: #fafafa; }
        
        .status-paid { color: white; background: #28a745; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
        .status-pending { color: white; background: #dc3545; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
        .status-active { color: green; font-weight: bold; }
        .status-deactivated { color: red; font-weight: bold; }
        
        .btn-action { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 0.8rem; margin-right: 5px; transition: 0.2s; }
        .btn-approve { background: #28a745; color: white; }
        .btn-approve:hover { background: #218838; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-reject:hover { background: #c82333; }
        .btn-delete { background: #333; color: white; }
        .btn-delete:hover { background: #111; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-wrapper { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #e0e0e0; padding: 10px; }
            .sidebar-item { display: inline-flex; border-left: none; border-bottom: 4px solid transparent; padding: 10px; margin-right: 10px; }
            .sidebar-item.active { border-left-color: transparent; border-bottom-color: #F15A24; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-wrapper">
        
        <!-- LEFT SIDEBAR -->
        <div class="sidebar">
            <h3>Admin Menu</h3>
            
            <div class="sidebar-item active" onclick="showSection('users-section', this)">
                <span>All Users</span>
                <span class="sidebar-badge"><?php echo $userCount; ?></span>
            </div>
            
            <div class="sidebar-item" onclick="showSection('vendors-section', this)">
                <span>Vendors</span>
                <span class="sidebar-badge"><?php echo $vendorCount; ?></span>
            </div>
            
            <div class="sidebar-item" onclick="showSection('bookings-section', this)">
                <span>Bookings</span>
                <span class="sidebar-badge"><?php echo $bookingCount; ?></span>
            </div>
            
            <div class="sidebar-item" onclick="showSection('revenue-section', this)">
                <span>Revenue & Services</span>
                <span class="sidebar-badge" style="background: #28a745;">KES <?php echo number_format($revenue); ?></span>
            </div>
        </div>

        <!-- RIGHT MAIN CONTENT -->
        <div class="main-content">
            <?php if($message): ?>
                <p style="color: green; font-weight: bold; background: #d4edda; padding: 15px; border-radius: 8px; margin-bottom: 20px;"><?php echo $message; ?></p>
            <?php endif; ?>

            <!-- SEARCH BAR -->
            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search by User ID, Name, Email, or Booking ID..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit">Search</button>
                <?php if($searchTerm): ?>
                    <a href="admin.php" style="padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 8px; display: flex; align-items: center;">Clear</a>
                <?php endif; ?>
            </form>

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
                                <button type="button" onclick="openImageModal('/uploads/<?php echo $b['payment_proof']; ?>')" style="background: none; border: none; color: #F15A24; font-weight: bold; text-decoration: underline; cursor: pointer; padding: 0; font-size: inherit; font-family: inherit;">View</button>
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
                            <?php if($b['status'] == 'confirmed'): ?>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <select name="delivery_status" style="padding: 5px; border-radius: 5px; border: 1px solid #ccc; font-size: 0.8rem;" onchange="this.form.submit()">
                                        <option value="pending" <?php if($b['delivery_status']=='pending') echo 'selected'; ?>>Pending</option>
                                        <option value="out for delivery" <?php if($b['delivery_status']=='out for delivery') echo 'selected'; ?>>Out for Delivery</option>
                                        <option value="delivered" <?php if($b['delivery_status']=='delivered') echo 'selected'; ?>>Delivered</option>
                                    </select>
                                    <input type="hidden" name="update_delivery" value="1">
                                </form>
                            <?php else: ?>
                                <span style="color: #999; font-size: 0.8rem;">🔒 Pending Payment</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($b['status'] == 'pending' && $b['payment_proof']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <button type="submit" name="confirm_booking" class="btn-action btn-approve" onclick="return confirm('Confirm payment and start delivery?')">Approve</button>
                                </form>
                                <form method="POST" style="display:inline; margin-top: 5px;">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <input type="text" name="rejection_reason" placeholder="Reason..." required style="padding: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.8rem; width: 100px;">
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
    </div>

    <!-- IMAGE MODAL POPUP -->
    <div id="imageModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); align-items: center; justify-content: center;">
        <span onclick="closeImageModal()" style="position: absolute; top: 20px; right: 40px; color: white; font-size: 40px; font-weight: bold; cursor: pointer;">&times;</span>
        <img id="modalImage" src="" style="max-width: 90%; max-height: 90%; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); object-fit: contain;">
    </div>

    <script>
        // 1. Sidebar Tab switching logic
        function showSection(sectionId, sidebarItem) {
            document.querySelectorAll('.section-box').forEach(box => box.classList.remove('active-section'));
            document.querySelectorAll('.sidebar-item').forEach(item => item.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active-section');
            sidebarItem.classList.add('active');
        }

        // 2. Modal Image Logic
        function openImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modalImg.src = imageSrc;
            modal.style.display = 'flex';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) { closeImageModal(); }
        });
    </script>
</body>
</html>