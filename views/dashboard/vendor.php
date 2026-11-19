<?php
session_start();
require_once '../../config/db_connect.php';

// SECURITY: Must be logged in and be a vendor
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../../views/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";

// Handle Product Upload
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_product'])) {
    $name = htmlspecialchars($_POST['name']);
    $price = htmlspecialchars($_POST['price']);
    $availability = $_POST['availability'];
    
    // Handle Image Upload
    $imagePath = NULL;
    if (isset($_FILES['tent_image']) && $_FILES['tent_image']['error'] == 0) {
        $targetDir = "../../uploads/"; 
        if (!file_exists($targetDir)) { mkdir($targetDir, 0777, true); }
        
        $fileName = time() . "_" . basename($_FILES["tent_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowTypes = array('jpg', 'png', 'jpeg');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES["tent_image"]["tmp_name"], $targetFilePath)) {
                $imagePath = $fileName;
            }
        }
    }

    // We insert into event_items so that the booking system recognizes it
    $stmt = $conn->prepare("INSERT INTO event_items (vendor_id, name, price, availability, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $userId, $name, $price, $availability, $imagePath);
    
    if($stmt->execute()) { $message = "Item uploaded successfully!"; } 
    else { $message = "Error uploading item."; }
}

// Fetch Vendor's Uploaded Items
$stmt = $conn->prepare("SELECT * FROM event_items WHERE vendor_id = ? ORDER BY name");
$stmt->bind_param("i", $userId);
$stmt->execute();
$myItems = $stmt->get_result();

// Fetch Bookings for Vendor's Items
$bookingQuery = "
    SELECT b.id as booking_id, b.event_date, b.location, b.status, b.total_amount, 
           u.first_name, u.surname,
           ei.name as item_name, ei.price
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN event_items ei ON b.tent_type = ei.name
    WHERE ei.vendor_id = ?
    ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($bookingQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Dashboard - Miyen Tents</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .dash-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        
        .upload-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; }
        .btn-upload { background: #F15A24; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 40px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: bold; }
        .status-paid { color: white; background: #28a745; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; }
        .status-pending { color: white; background: #dc3545; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dash-container">
        
        <!-- Just the Title (No duplicate button) -->
        <h2>Vendor Dashboard</h2>
        <br>

        <?php if($message): ?><p style="color: green; font-weight: bold;"><?php echo $message; ?></p><?php endif; ?>

        <!-- Upload Form -->
        <div class="upload-box">
            <h3>Upload New Item</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="upload_product" value="1">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="name" placeholder="e.g. Plastic Chairs, Folding Tables, VIP Tent" required>
                </div>
                <div class="form-group">
                    <label>Price (KES)</label>
                    <input type="number" name="price" placeholder="e.g. 150" required>
                </div>
                <div class="form-group">
                    <label>Availability</label>
                    <select name="availability">
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Item Photo (Optional)</label>
                    <input type="file" name="tent_image" accept="image/*">
                </div>
                <button type="submit" class="btn-upload">Upload Item</button>
            </form>
        </div>

        <hr style="margin: 40px 0; border: 0; border-top: 1px solid #ddd;">

        <!-- My Uploaded Items -->
        <h3>Your Uploaded Items</h3>
        <table>
            <thead>
                <tr><th>Item Name</th><th>Price (KES)</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if($myItems->num_rows > 0): ?>
                    <?php while($row = $myItems->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo number_format($row['price'], 2); ?></td>
                        <td><span style="color: <?php echo $row['availability'] == 'available' ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo ucfirst($row['availability']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">You haven't uploaded any items yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Bookings for My Items -->
        <h3>Bookings for Your Items</h3>
        <table>
            <thead>
                <tr><th>Customer</th><th>Item Booked</th><th>Event Date</th><th>Location</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if($bookings->num_rows > 0): ?>
                    <?php while($b = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($b['first_name'] . ' ' . $b['surname']); ?></td>
                        <td><?php echo htmlspecialchars($b['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($b['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($b['location']); ?></td>
                        <td>
                            <?php if($b['status'] == 'confirmed'): ?>
                                <span class="status-paid">PAID</span>
                            <?php else: ?>
                                <span class="status-pending">PENDING</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No bookings for your items yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Script to automatically fix the header's logout button link -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                // If it finds the broken header link, fix it instantly
                if (link.href.includes('/views/src/process_logout.php') || link.innerText.trim() === 'Logout') {
                    link.href = '../../src/process_logout.php';
                }
            });
        });
    </script>

</body>
</html>