<?php include '../includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/booking.css">

<div class="booking-container">
    
    <!-- Progress Bar (Step 1) -->
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

    <!-- Form -->
    <form action="../src/process_booking.php" method="POST" class="booking-form">
        
        <div class="form-row">
            <div class="form-group">
                <label>Type of Tent</label>
                <select name="tent_type" required>
                    <option value="" disabled selected>Select Type of Tent</option>
                    <option value="standard">Standard Tent</option>
                    <option value="premium">Premium Tent</option>
                    <option value="vip">VIP Tent</option>
                </select>
            </div>

            <div class="form-group">
                <label>Number of Attendees</label>
                <select name="attendees" required>
                    <option value="" disabled selected>Select Number</option>
                    <option value="50">1 - 50 People</option>
                    <option value="100">51 - 100 People</option>
                    <option value="200">101 - 200 People</option>
                    <option value="500">200+ People</option>
                </select>
            </div>
        </div>

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

        <div class="booking-actions">
            <button type="button" class="btn-back" onclick="history.back()">Back</button>
            <button type="submit" class="btn-proceed">Proceed</button>
        </div>
    </form>

</div>

<?php include '../includes/footer.php'; ?>