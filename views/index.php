<?php 
// We include the header here for the navigation bar
include '../includes/header.php'; 
?>

<!-- Hero Section -->
<header style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../assets/images/hero-bg.jpg') no-repeat center center/cover; height: 70vh; display: flex; align-items: center; padding-left: 10%;">
    <div style="color: white; max-width: 600px;">
        <h3 style="color: #F15A24; font-size: 1.5rem; margin-bottom: 10px;">Book Now</h3>
        <h1 style="font-size: 3.5rem; line-height: 1.2; margin-bottom: 20px;">Book your Event Tents<br>& Chairs</h1>
        <a href="book-now.php" class="btn btn-outline" style="color: white; border-color: white;">Book Now</a>
    </div>
</header>

<!-- Popular Categories -->
<section style="padding: 60px 10%; background: white;">
    <h2 style="margin-bottom: 30px; font-size: 2rem;">Popular</h2>
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        
        <!-- Graduation -->
        <div style="text-align: center; width: 150px;">
            <a href="book-now.php?type=graduation" style="text-decoration: none; color: inherit;">
                <div style="width: 120px; height: 120px; border-radius: 15px; overflow: hidden; margin: 0 auto 10px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <img src="../assets/images/categories/graduation.jpg" alt="Graduation" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <p style="font-weight: 600;">Graduation</p>
            </a>
        </div>

        <!-- Camping -->
        <div style="text-align: center; width: 150px;">
            <a href="book-now.php?type=camping" style="text-decoration: none; color: inherit;">
                <div style="width: 120px; height: 120px; border-radius: 15px; overflow: hidden; margin: 0 auto 10px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <img src="../assets/images/categories/camping.jpg" alt="Camping" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <p style="font-weight: 600;">Camping</p>
            </a>
        </div>

        <!-- Wedding -->
        <div style="text-align: center; width: 150px;">
            <a href="book-now.php?type=wedding" style="text-decoration: none; color: inherit;">
                <div style="width: 120px; height: 120px; border-radius: 15px; overflow: hidden; margin: 0 auto 10px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <img src="../assets/images/categories/wedding.jpg" alt="Wedding" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <p style="font-weight: 600;">Wedding</p>
            </a>
        </div>

        <!-- Birthday -->
        <div style="text-align: center; width: 150px;">
            <a href="book-now.php?type=birthday" style="text-decoration: none; color: inherit;">
                <div style="width: 120px; height: 120px; border-radius: 15px; overflow: hidden; margin: 0 auto 10px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <img src="../assets/images/categories/birthday.jpg" alt="Birthday" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <p style="font-weight: 600;">Birthday</p>
            </a>
        </div>

    </div>
</section>

<!-- Vendor Section -->
<section style="padding: 60px 10%; background: #f9f9f9;">
    <h3 style="color: #F15A24; font-size: 1.2rem; margin-bottom: 10px;">Vendor</h3>
    <h1 style="font-size: 2.5rem; margin-bottom: 20px; max-width: 700px;">Do you want to become Vendor with Miyen?</h1>
    <p style="color: #666; max-width: 600px; margin-bottom: 30px; line-height: 1.6;">
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.
    </p>
    
    <div style="display: flex; gap: 30px; margin-bottom: 40px;">
        <div style="text-align: center;">
            <div style="width: 80px; height: 80px; background: #ddd; border-radius: 50%; margin-bottom: 10px;"></div>
            <p style="font-weight: 600;">Earn More<br>Money</p>
        </div>
        <div style="text-align: center;">
            <div style="width: 80px; height: 80px; background: #ddd; border-radius: 50%; margin-bottom: 10px;"></div>
            <p style="font-weight: 600;">Earn More<br>Money</p>
        </div>
        <div style="text-align: center;">
            <div style="width: 80px; height: 80px; background: #ddd; border-radius: 50%; margin-bottom: 10px;"></div>
            <p style="font-weight: 600;">Earn More<br>Money</p>
        </div>
    </div>

    <a href="register.php" class="btn btn-outline">Signup as a Vendor</a>
</section>

<?php 
// Include footer
include '../includes/footer.php'; 
?>