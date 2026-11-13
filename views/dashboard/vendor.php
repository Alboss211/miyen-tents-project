<?php
session_start();
require_once '../../config/db_connect.php';

// SECURITY: Must be logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../views/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";

// Handle Upload
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $price = htmlspecialchars($_POST['price']);
    $desc = htmlspecialchars($_POST['description']);
    
    $stmt = $conn->prepare("INSERT INTO products (vendor_id, name, price, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $userId, $name, $price, $desc);
    if($stmt->execute()) {
        $message = "Product uploaded successfully!";
    } else {
        $message = "Error uploading product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Dashboard - Miyen Tents</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .dash-container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .upload-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .btn-upload { background: #F15A24; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dash-container">
        <h2>Vendor Dashboard</h2>
        <?php if($message): ?><p style="color: green; font-weight: bold;"><?php echo $message; ?></p><?php endif; ?>

        <div class="upload-box">
            <h3>Upload New Tent Offering</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" placeholder="e.g. Premium Wedding Tent" required>
                </div>
                <div class="form-group">
                    <label>Price (KES)</label>
                    <input type="number" name="price" placeholder="e.g. 20000" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Describe the tent, capacity, and features..." required></textarea>
                </div>
                <button type="submit" class="btn-upload">Upload Product</button>
            </form>
        </div>
    </div>
</body>
</html>