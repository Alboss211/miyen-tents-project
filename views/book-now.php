<?php 
include '../includes/header.php'; 

// SECURITY: Require login before booking
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=PleaseLoginToBook");
    exit();
}

require_once '../config/db_connect.php';

// Fetch all available items from the database
$itemsResult = $conn->query("SELECT * FROM event_items WHERE availability = 'available'");
?>

<link rel="stylesheet" href="../assets/css/booking.css">

<div class="booking-container">
    
    <div class="progress-container">
        <div class="progress-line" style="width: 50%;"></div>
        <div class="progress-step active">
            <div class="step-circle"></div>
            <span>General Info</span>
        </div>
        <div class="progress-step">
            <div class="step-circle"></div>
            <span>Payment Info</span>
        </div>
    </div>

    <!-- The form now submits to payment.php -->
    <form action="payment.php" method="POST" class="booking-form">
        
        <h3 style="margin-bottom: 15px; color: #333;">Select Your Items</h3>
        <table class="item-table" style="width: 100%; border-collapse: collapse; margin-bottom: 30px; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <thead style="background: #f9f9f9;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Item</th>
                    <th style="padding: 15px; text-align: left;">Price (KES)</th>
                    <th style="padding: 15px; text-align: left;">Availability</th>
                    <th style="padding: 15px; text-align: left;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $itemsResult->fetch_assoc()): ?>
                <tr>
                    <td style="padding: 15px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td style="padding: 15px; border-bottom: 1px solid #eee;"><?php echo number_format($item['price'], 2); ?></td>
                    <td style="padding: 15px; border-bottom: 1px solid #eee;">
                        <span style="color: green; font-weight: bold;">Available</span>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #eee;">
                        <input type="number" name="items[<?php echo $item['id']; ?>]" min="0" value="0" 
                               data-price="<?php echo $item['price']; ?>" 
                               class="item-quantity" 
                               style="width: 80px; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="form-row">
            <div class="form-group">
                <label>Event Date</label>
                <input type="date" name="event_date" required>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="Enter Location" required>
            </div>
        </div>

        <!-- Total Display -->
        <div style="text-align: right; margin: 20px 0; font-size: 1.5rem; font-weight: bold; color: #F15A24;">
            Total: KES <span id="totalAmount">0.00</span>
        </div>

        <div class="booking-actions">
            <button type="button" class="btn-back" onclick="history.back()">Back</button>
            <button type="submit" class="btn-proceed">Proceed to Pay</button>
        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.item-quantity');
    const totalDisplay = document.getElementById('totalAmount');

    function calculateTotal() {
        let total = 0;
        quantityInputs.forEach(input => {
            const price = parseFloat(input.getAttribute('data-price'));
            const quantity = parseInt(input.value) || 0;
            total += price * quantity;
        });
        totalDisplay.textContent = total.toFixed(2);
        // Store total in a hidden input to send to payment.php
        document.getElementById('totalHidden').value = total.toFixed(2);
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
});
</script>

<?php include '../includes/footer.php'; ?>