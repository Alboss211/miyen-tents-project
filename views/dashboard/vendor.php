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
    $desc = htmlspecialchars($_POST['description']);
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

    $stmt = $conn->prepare("INSERT INTO products (vendor_id, name, price, description, availability, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssss", $userId, $name, $price, $desc, $availability, $imagePath);
    
    if($stmt->execute()) { $message = "Tent uploaded successfully!"; } 
    else { $message = "Error uploading tent."; }
}

// Fetch Vendor's Products
$stmt = $conn->prepare("SELECT * FROM products WHERE vendor_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$products = $stmt->get_result();
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
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; }
        .btn-upload { background: #F15A24; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .product-card { background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .product-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
        .status-avail { color: green; font-weight: bold; font-size: 0.8rem; }
        .status-unavail { color: red; font-weight: bold; font-size: 0.8rem; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dash-container">
        <h2>Vendor Dashboard</h2>
        <?php if($message): ?><p style="color: green; font-weight: bold;"><?php echo $message; ?></p><?php endif; ?>

        <div class="upload-box">
            <h3>Upload New Tent Offering</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="upload_product" value="1">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" placeholder="e.g. Premium Wedding Tent" required>
                </div>
                <div class="form-group">
                    <label>Price (KES)</label>
                    <input type="number" name="price" placeholder="e.g. 20000" required>
                </div>
                <div class="form-group">
                    <label>Description (Camping Package details)</label>
                    <textarea name="description" rows="3" placeholder="Describe the tent, capacity, and features..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Availability</label>
                    <select name="availability">
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tent Photo</label>
                    <input type="file" name="tent_image" accept="image/*" required>
                </div>
                <button type="submit" class="btn-upload">Upload Product</button>
            </form>
        </div>

        <hr style="margin: 40px 0; border: 0; border-top: 1px solid #ddd;">
        <h3>Your Active Listings</h3>
        <div class="product-grid">
            <?php if($products->num_rows > 0): ?>
                <?php while($row = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php if($row['image_path']): ?>
                            <img src="../../uploads/<?php echo htmlspecialchars($row['image_path']); ?>" alt="Tent Image">
                        <?php else: ?>
                            <div style="width:100%; height:150px; background:#eee; border-radius:8px; display:flex; align-items:center; justify-content:center;">No Image</div>
                        <?php endif; ?>
                        <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                        <p style="color: #F15A24; font-weight: bold;">KES <?php echo htmlspecialchars($row['price']); ?></p>
                        <p style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="<?php echo $row['availability'] == 'available' ? 'status-avail' : 'status-unavail'; ?>">
                            Status: <?php echo ucfirst($row['availability']); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You haven't uploaded any tents yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>