<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("./restapi/connection.php");
include("./role_based_header.php");
?>

<div class="ltn__utilize-overlay"></div>

<!-- BREADCRUMB AREA START -->
<div class="ltn__breadcrumb-area text-left bg-overlay-white-30 bg-image" data-bs-bg="img/connection1.jpg">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__breadcrumb-inner">
                    <h1 class="page-title">Account</h1>
                    <div class="ltn__breadcrumb-list">
                        <ul>
                            <li><a href="index.php"><span class="ltn__secondary-color"><i class="fas fa-home"></i></span> Home</a></li>
                            <li>Register</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BREADCRUMB AREA END -->

<!-- REGISTER AREA START -->
<div class="ltn__login-area pb-110">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area text-center">
                    <h1 class="section-title">Register <br>Your Account</h1>
                    <p>Join Share|K and start sharing your knowledge today!</p>
                </div>
            </div>
            <div class="col-lg-6 offset-lg-3">
                <div class="account-login-inner">
                    <form id="registerForm" enctype="multipart/form-data" class="ltn__form-box contact-form-box">
                        <h3>Create Account</h3>
                        <input type="text" name="Username" placeholder="Username*" required>
                        <input type="email" name="Email" placeholder="Email*" required>
                        <input type="password" name="Password" placeholder="Password*" required>
                        <input type="password" name="ConfirmPassword" placeholder="Confirm Password*" required>
                        <input type="file" name="Profile_Picture" accept="image/*">
                        <label class="checkbox-inline">
                            <input type="checkbox" required>
                            I agree to the terms and privacy policy.
                        </label>
                        <div class="btn-wrapper">
                            <button class="theme-btn-1 btn reverse-color btn-block" type="submit">CREATE ACCOUNT</button>
                        </div>
                    </form>
                    <div class="go-to-btn mt-50">
                        <a href="login.php">ALREADY HAVE AN ACCOUNT?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- REGISTER AREA END -->

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('restapi/api.php?resource=users', {
            method: 'POST',
            body: formData
        });
        console.log(response);
        const result = await response.json();
        console.log(result);
        if (response.ok) {
            alert('Registration successful! User ID: ' + result.id);
            window.location.href = 'login.php';
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('An error occurred: ' + error.message);
    }
});
</script>

<?php include("./footer.php"); ?>
