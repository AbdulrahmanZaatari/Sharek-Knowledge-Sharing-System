<?php
session_start();
include("./role_based_header.php")
?>
<div class="ltn__utilize-overlay"></div>
<!-- HERO SECTION START -->
<div class="hero-section">
    <div class="hero-content">
        <div class="hero-text">
            <h5 class="hero-subtitle">Where Questions Find Answers</h5>
            <div class="hero-title">
                <img src="img/logobg.png" alt="Share|K Logo" class="logo-image">
            </div>
            <p class="hero-description">
                A platform to connect, collaborate, and share knowledge with the world.
            </p>
            <div class="hero-buttons">
                <a href="#features" class="btn primary-btn">Explore Now</a>
                <a href="#about" class="btn secondary-btn">Learn More</a>
            </div>
        </div>
    </div>
    <div class="hero-image">
        <img src="img/hpbg.jpg" alt="Hero Background">
    </div>
</div>
<!-- HERO SECTION END -->

<!-- ABOUT SECTION START -->
<section id="about" class="about-section section-bg">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-image">
                    <img src="img/home1.jpg" alt="About Share|K" class="img-fluid">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <h2 class="section-title">About Share|K</h2>
                    <p class="about-description">
                        Share|K is a community-driven platform designed to connect individuals from diverse backgrounds, empowering them to share their knowledge, ideas, and experiences. 
                        Whether you're here to learn, teach, or collaborate, Share|K provides the tools to help you thrive and grow.
                    </p>
                    <ul class="about-list">
                        <li><i class="fas fa-check-circle"></i> Easy-to-use platform for sharing knowledge.</li>
                        <li><i class="fas fa-check-circle"></i> A vibrant community of learners and contributors.</li>
                        <li><i class="fas fa-check-circle"></i> Earn rewards and recognition for your contributions.</li>
                    </ul>
                    <a href="login.php" class="btn primary-btn">Get Started</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ABOUT SECTION END -->

<!-- FEATURES SECTION START -->
<section id="features" class="features-section section-bg">
    <div class="container">
        <h2 class="section-title">How to start sharing?</h2>
        <p class="intro-description">
            Take the first step to join a community of curious minds ready to share and grow together.
        </p>
        <div class="features-grid">
            <div class="feature-item">
                <i class="fas fa-user-plus feature-icon"></i>
                <h4>Join the Platform</h4>
                <p>Create your account and join our vibrant community of learners.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-share-alt feature-icon"></i>
                <h4>Share Knowledge</h4>
                <p>Post your ideas, answer questions, and contribute to discussions.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-trophy feature-icon"></i>
                <h4>Earn Rewards</h4>
                <p>Participate in challenges, earn points, and gain recognition.</p>
            </div>
        </div>
    </div>
</section>
<!-- FEATURES SECTION END -->

<!-- TESTIMONIAL AREA START -->
<div class="testimonial-section section-bg">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area text-center">
                    <h6 class="section-subtitle">Testimonials</h6>
                    <h1 class="section-title">User Feedback<span>.</span></h1>
                </div>
            </div>
        </div>
        <div class="row testimonial-slider">
            <div class="testimonial-item">
                <div class="testimonial-img">
                    <img src="img/testimonial/Ali.jpg" alt="Ali Ghandour">
                </div>
                <div class="testimonial-info">
                    <p>I appreciate the community provided by Share|K, it's a great place to learn and grow.</p>
                    <h4>Ali Ghandour</h4>
                    <h6>User</h6>
                </div>
            </div>
            <div class="testimonial-item">
                <div class="testimonial-img">
                    <img src="img/testimonial/hadi.jpg" alt="Hadi Obeid">
                </div>
                <div class="testimonial-info">
                    <p>Scrolling through Share|K's content is always a great way to spend my time.</p>
                    <h4>Hadi Obeid</h4>
                    <h6>User</h6>
                </div>
            </div>
            <div class="testimonial-item">
                <div class="testimonial-img">
                    <img src="img/testimonial/MI.png" alt="Mohammad Ismael">
                </div>
                <div class="testimonial-info">
                    <p>The System is very user-friendly and easy to navigate.</p>
                    <h4>Mohammad Ismael</h4>
                    <h6>User</h6>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- TESTIMONIAL AREA END -->
<?php
include("./footer.php")
?>
