<?php 
include '../includes/header.php'; 

// Get the category from the URL (e.g., gallery.php?category=graduation)
$category = isset($_GET['category']) ? strtolower($_GET['category']) : 'graduation';

// Set up dynamic content based on the category
$title = "";
$message = "";
$images = [];

if ($category == 'graduation') {
    $title = "Graduation Events";
    $message = "Celebrate your success with unforgettable graduation ceremonies. We provide premium tents and setups for your big day.";
    $images = ['graduation1.jpg', 'graduation2.jpg', 'graduation3.jpg'];
} elseif ($category == 'wedding') {
    $title = "Wedding Receptions";
    $message = "Make your special day magical. Our elegant wedding tents and setups create the perfect romantic atmosphere.";
    $images = ['wedding1.jpg', 'wedding2.jpg', 'wedding3.jpg'];
} elseif ($category == 'camping') {
    $title = "Camping Adventures";
    $message = "Escape into nature with our durable and comfortable camping tents. Perfect for group adventures and outdoor experiences.";
    $images = ['camping1.jpg', 'camping2.jpg', 'camping3.jpg'];
} elseif ($category == 'birthday') {
    $title = "Birthday Parties";
    $message = "Throw an amazing birthday party! We offer colorful and fun tent setups to make your celebration memorable.";
    $images = ['birthday1.jpg', 'birthday2.jpg', 'birthday3.jpg'];
}
?>

<!-- Gallery Section -->
<section style="padding: 60px 10%; background: #f9f9f9; min-height: 80vh;">
    
    <!-- Header Message -->
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #333; font-size: 3rem; margin-bottom: 15px;"><?php echo $title; ?></h1>
        <p style="color: #666; max-width: 800px; margin: 0 auto; font-size: 1.2rem; line-height: 1.6;">
            <?php echo $message; ?>
        </p>
    </div>

    <!-- Featured Image (Main Carousel) -->
    <div style="max-width: 900px; margin: 0 auto 30px auto; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15); height: 500px;">
        <!-- Using the first image as the main feature -->
        <img id="mainImage" src="../assets/images/gallery/<?php echo $images[0]; ?>" alt="<?php echo $title; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.5s;" onerror="this.src='../assets/images/hero-bg.jpg';">
    </div>

    <!-- Thumbnail Grid -->
    <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 50px; flex-wrap: wrap;">
        <?php foreach($images as $index => $img): ?>
            <div onclick="changeImage('../assets/images/gallery/<?php echo $img; ?>')" style="width: 150px; height: 100px; border-radius: 10px; overflow: hidden; cursor: pointer; border: 3px solid transparent; transition: 0.3s;" onmouseover="this.style.borderColor='#F15A24'" onmouseout="this.style.borderColor='transparent'">
                <img src="../assets/images/gallery/<?php echo $img; ?>" alt="Thumbnail" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='../assets/images/hero-bg.jpg';">
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Call to Action (Book Now) -->
    <div style="text-align: center; background: white; padding: 40px; border-radius: 15px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 15px; font-size: 1.5rem; color: #333;">Ready to book your event?</h3>
        <p style="color: #666; margin-bottom: 25px;">Secure your date now and let us handle the setup.</p>
        <a href="book-now.php?type=<?php echo $category; ?>" class="btn btn-orange" style="padding: 15px 60px; font-size: 1.2rem; border-radius: 30px;">Book Now</a>
    </div>

</section>

<!-- Simple JavaScript to change the main image when a thumbnail is clicked -->
<script>
function changeImage(src) {
    const mainImg = document.getElementById('mainImage');
    mainImg.style.opacity = 0;
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = 1;
    }, 300);
}
</script>

<?php include '../includes/footer.php'; ?>