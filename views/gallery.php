<?php 
include '../includes/header.php'; 

// Get the category from the URL
$category = isset($_GET['category']) ? strtolower($_GET['category']) : 'all';

// If 'all', show the slideshow gallery
if ($category == 'all') {
    // Define images for the slideshow (mix of all categories)
    $slideshowImages = [
        '../assets/images/gallery/graduation1.jpg',
        '../assets/images/gallery/wedding1.jpg',
        '../assets/images/gallery/camping1.jpg',
        '../assets/images/gallery/birthday1.jpg',
        '../assets/images/gallery/graduation2.jpg',
        '../assets/images/gallery/wedding2.jpg',
        '../assets/images/gallery/camping2.jpg',
        '../assets/images/gallery/birthday2.jpg'
    ];
    ?>
    
    <section style="padding: 60px 10%; background: #f9f9f9; min-height: 80vh; text-align: center;">
        <h1 style="font-size: 3rem; margin-bottom: 20px; color: #333;">Our Event Galleries</h1>
        <p style="color: #666; font-size: 1.2rem; margin-bottom: 40px;">A glimpse of the unforgettable events we've helped create.</p>

        <!-- The Slideshow Container -->
        <div class="slideshow-container" style="max-width: 900px; margin: 0 auto 40px auto; position: relative; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            
            <?php foreach($slideshowImages as $index => $img): ?>
                <div class="mySlides fade" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                    <img src="<?php echo $img; ?>" style="width: 100%; height: 500px; object-fit: cover;" onerror="this.src='../assets/images/hero-bg.jpg';">
                </div>
            <?php endforeach; ?>

            <!-- Navigation Dots -->
            <div style="text-align: center; position: absolute; bottom: 15px; width: 100%;">
                <?php foreach($slideshowImages as $index => $img): ?>
                    <span class="dot" onclick="currentSlide(<?php echo $index + 1; ?>)" style="height: 12px; width: 12px; margin: 0 5px; background-color: #bbb; border-radius: 50%; display: inline-block; cursor: pointer; transition: background-color 0.3s;"></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Category Selection Buttons -->
        <h2 style="margin-bottom: 20px;">Explore by Category</h2>
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="gallery.php?category=graduation" class="btn btn-orange" style="padding: 12px 30px; border-radius: 30px;">🎓 Graduation</a>
            <a href="gallery.php?category=wedding" class="btn btn-orange" style="padding: 12px 30px; border-radius: 30px;">💍 Wedding</a>
            <a href="gallery.php?category=camping" class="btn btn-orange" style="padding: 12px 30px; border-radius: 30px;">⛺ Camping</a>
            <a href="gallery.php?category=birthday" class="btn btn-orange" style="padding: 12px 30px; border-radius: 30px;">🎂 Birthday</a>
        </div>
    </section>

    <!-- Slideshow JavaScript -->
    <script>
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            let i;
            let slides = document.getElementsByClassName("mySlides");
            let dots = document.getElementsByClassName("dot");
            
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            slideIndex++;
            if (slideIndex > slides.length) {slideIndex = 1}    
            
            for (i = 0; i < dots.length; i++) {
                dots[i].style.backgroundColor = "#bbb"; 
            }
            
            slides[slideIndex-1].style.display = "block";  
            dots[slideIndex-1].style.backgroundColor = "#F15A24"; 
            
            setTimeout(showSlides, 3000); // Change image every 3 seconds
        }

        function currentSlide(n) {
            slideIndex = n - 1;
        }
    </script>

    <?php
    include '../includes/footer.php';
    exit(); 
}

// --- IF A SPECIFIC CATEGORY IS SELECTED, SHOW THE DETAIL PAGE ---
$title = ""; $message = ""; $images = [];
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

<!-- Category Detail View -->
<section style="padding: 60px 10%; background: #f9f9f9; min-height: 80vh;">
    
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #333; font-size: 3rem; margin-bottom: 15px;"><?php echo $title; ?></h1>
        <p style="color: #666; max-width: 800px; margin: 0 auto; font-size: 1.2rem; line-height: 1.6;">
            <?php echo $message; ?>
        </p>
    </div>

    <!-- Featured Image -->
    <div style="max-width: 900px; margin: 0 auto 30px auto; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15); height: 500px;">
        <img id="mainImage" src="../assets/images/gallery/<?php echo $images[0]; ?>" alt="<?php echo $title; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.5s;" onerror="this.src='../assets/images/hero-bg.jpg';">
    </div>

    <!-- Thumbnails -->
    <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 50px; flex-wrap: wrap;">
        <?php foreach($images as $img): ?>
            <div onclick="changeImage('../assets/images/gallery/<?php echo $img; ?>')" style="width: 150px; height: 100px; border-radius: 10px; overflow: hidden; cursor: pointer; border: 3px solid transparent; transition: 0.3s;" onmouseover="this.style.borderColor='#F15A24'" onmouseout="this.style.borderColor='transparent'">
                <img src="../assets/images/gallery/<?php echo $img; ?>" alt="Thumbnail" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='../assets/images/hero-bg.jpg';">
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Call to Action -->
    <div style="text-align: center; background: white; padding: 40px; border-radius: 15px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 15px; font-size: 1.5rem; color: #333;">Ready to book your event?</h3>
        <p style="color: #666; margin-bottom: 25px;">Secure your date now and let us handle the setup.</p>
        
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="../views/gallery.php" class="btn btn-outline" style="padding: 15px 40px; font-size: 1.1rem; border-radius: 30px;">
                ← Back to Gallery
            </a>
            <a href="book-now.php?type=<?php echo $category; ?>" class="btn btn-orange" style="padding: 15px 40px; font-size: 1.1rem; border-radius: 30px;">
                Book Now
            </a>
        </div>
    </div>

</section>

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