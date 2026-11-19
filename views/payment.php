<?php 
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=PleaseLoginToBook");
    exit();
}

// Get the total amount from the previous step
$totalAmount = isset($_POST['total_amount']) ? $_POST['total_amount'] : 0;
$eventDate = isset($_POST['event_date']) ? $_POST['event_date'] : '';
$location = isset($_POST['location']) ? $_POST['location'] : '';
$items = isset($_POST['items']) ? $_POST['items'] : [];
?>

<div class="booking-container" style="max-width: 800px;">
    
    <div class="progress-container">
        <div class="progress-line" style="width: 100%;"></div>
        <div class="progress-step active">
            <div class="step-circle"></div>
            <span>General Info</span>
        </div>
        <div class="progress-step active">
            <div class="step-circle"></div>
            <span>Payment Info</span>
        </div>
    </div>

    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center;">
        
        <h2 style="color: #F15A24; margin-bottom: 20px;">Pay via M-Pesa</h2>
        
        <div style="background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <h3 style="margin-bottom: 10px;">Payment Instructions</h3>
            <p style="font-size: 1.1rem; margin-bottom: 5px;">1. Go to M-Pesa Menu</p>
            <p style="font-size: 1.1rem; margin-bottom: 5px;">2. Select <strong>Lipa na M-Pesa</strong> > <strong>Pay Bill</strong></p>
            <p style="font-size: 1.1rem; margin-bottom: 5px;">3. Business Number: <strong>123456</strong></p>
            <p style="font-size: 1.1rem; margin-bottom: 5px;">4. Account Number: <strong>MIYEN TENTS</strong></p>
            <p style="font-size: 1.1rem; margin-bottom: 15px;">5. Amount: <strong>KES <?php echo number_format($totalAmount, 2); ?></strong></p>
        </div>

        <!-- The form that uploads the proof -->
        <form action="../src/process_booking.php" method="POST" enctype="multipart/form-data">
            
            <!-- Pass the booking data forward -->
            <input type="hidden" name="total_amount" value="<?php echo $totalAmount; ?>">
            <input type="hidden" name="event_date" value="<?php echo $eventDate; ?>">
            <input type="hidden" name="location" value="<?php echo $location; ?>">
            <?php foreach($items as $id => $qty): ?>
                <input type="hidden" name="items[<?php echo $id; ?>]" value="<?php echo $qty; ?>">
            <?php endforeach; ?>

            <div class="form-group" style="text-align: left; margin-bottom: 20px;">
                <label style="font-weight: bold; margin-bottom: 10px; display: block;">Upload M-Pesa Confirmation Screenshot / PDF</label>
                <input type="file" name="payment_proof" accept="image/*, .pdf" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>

            <button type="submit" class="btn-proceed" style="width: 100%; padding: 15px; font-size: 1.2rem;">Confirm Payment & Book</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>