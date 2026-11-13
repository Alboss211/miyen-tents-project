<?php include '../includes/header.php'; ?>

<style>
    .confirm-container {
        max-width: 800px;
        margin: 80px auto;
        text-align: center;
    }
    .check-circle {
        width: 80px;
        height: 80px;
        background: #F15A24;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto -40px auto;
        position: relative;
        z-index: 10;
    }
    .check-circle svg {
        width: 40px;
        height: 40px;
        fill: none;
        stroke: white;
        stroke-width: 3;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .confirm-box {
        border: 2px dashed #ccc;
        border-radius: 20px;
        padding: 80px 40px 40px 40px;
        background: white;
    }
    .confirm-box h2 {
        font-size: 2rem;
        margin-bottom: 20px;
        color: #333;
    }
    .confirm-box p {
        color: #666;
        line-height: 1.6;
        margin-bottom: 30px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
    .btn-homepage {
        background: #000;
        color: white;
        padding: 15px 40px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: bold;
        display: inline-block;
    }
</style>

<div class="confirm-container">
    
    <div class="check-circle">
        <svg viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </div>
    
    <div class="confirm-box">
        <h2>Booking Confirmed</h2>
        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</p>
        <a href="index.php" class="btn-homepage">Go to Homepage</a>
    </div>

</div>

<?php include '../includes/footer.php'; ?>