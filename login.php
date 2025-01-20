<?php
session_start();
include("./restapi/connection.php");

// Check the connection (use PDO-based connection in restapi/connection.php)
if (!isset($pdo)) {
    die("Database connection not established.");
}

$error = "";

// Initialize session variables for failed attempts and lockout time
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}

if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = null;
}

$lockout_duration = 15 * 60; // 15 minutes in seconds

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user is in lockout period
    if ($_SESSION['failed_attempts'] >= 3) {
        $time_since_lockout = time() - $_SESSION['lockout_time'];
        
        if ($time_since_lockout < $lockout_duration) {
            $remaining_time = ceil(($lockout_duration - $time_since_lockout) / 60); // Minutes remaining
            $error = "Too many failed login attempts. Please try again after $remaining_time minutes.";
        } else {
            // Reset lockout after the duration has passed
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['lockout_time'] = null;
        }
    }

    // Proceed with login if not locked out
    if ($_SESSION['failed_attempts'] < 3) {
        try {
            // Check if the email exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verify the password
                if (password_verify($password, $user['PasswordHash'])) {
                    // Reset failed attempts and lockout time on successful login
                    $_SESSION['failed_attempts'] = 0;
                    $_SESSION['lockout_time'] = null;

                    // Set session variables
                    $_SESSION['user_id'] = $user['Id'];
                    $_SESSION['username'] = $user['Username'];
                    $_SESSION['email'] = $user['Email'];
                    $_SESSION['role'] = $user['Role'];
                    $_SESSION['points'] = $user['Points'];

                    // Redirect based on role
                    if ($user['Role'] === 'Admin') {
                        header("Location: account.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    // Increment failed attempts
                    $_SESSION['failed_attempts']++;

                    // Set lockout time if attempts reach 3
                    if ($_SESSION['failed_attempts'] >= 3) {
                        $_SESSION['lockout_time'] = time();
                    }

                    $error = "Invalid email or password.";
                }
            } else {
                // Increment failed attempts
                $_SESSION['failed_attempts']++;

                // Set lockout time if attempts reach 3
                if ($_SESSION['failed_attempts'] >= 3) {
                    $_SESSION['lockout_time'] = time();
                }

                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}
?>

<?php include("./role_based_header.php"); ?>
<!-- LOGIN PAGE CONTENT -->
<div class="ltn__utilize-overlay"></div>

<!-- BREADCRUMB AREA START -->
<div class="ltn__breadcrumb-area text-left bg-overlay-white-30 bg-image" style="height: 400px;" data-bs-bg="img/connection1.jpg">
</div>
<!-- BREADCRUMB AREA END -->

<!-- LOGIN AREA START -->
<div class="ltn__login-area pb-65">
    <div class="container">
        <div class="row">
            <div class="section-title-area text-center">
                <h1 class="section-title">Sign In <br>To Your Account</h1>
                <p>Welcome Back to our platform!</p>
            </div>
        </div>
        <div class="row">
            <!-- Login Box -->
            <div class="col-lg-6">
                <div class="account-login-inner box-style">
                    <h3 class="text-center">Login</h3>
                    <form action="login.php" method="POST" class="ltn__form-box contact-form-box">
                        <input type="email" name="email" placeholder="Email*" required>
                        <input type="password" name="password" placeholder="Password*" required>
                        <div class="btn-wrapper mt-0">
                            <button class="theme-btn-1 btn btn-block" type="submit">Log IN</button>
                        </div>
                        <!-- Display error message -->
                        <?php if (!empty($error)): ?>
                            <p style="color: red; margin-top: 10px;"><?php echo $error; ?></p>
                        <?php endif; ?>
                        <div class="go-to-btn mt-20">
                            <a href="forgot_password.php"><small>FORGOTTEN YOUR PASSWORD?</small></a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Register Box -->
            <div class="col-lg-6">
                <div class="account-create box-style text-center">
                    <h3>Create Account</h3>
                    <p>Register so that you can start engaging with the Sharek community!</p>
                    <div class="btn-wrapper">
                        <a href="register.php" class="theme-btn-1 btn black-btn">CREATE ACCOUNT</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- LOGIN AREA END -->

<style>
    .box-style {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .box-style h3 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .box-style .btn {
        width: 100%;
        margin-top: 20px;
    }

    .box-style p {
        color: #555;
        margin-bottom: 20px;
    }

    .account-login-inner {
        padding: 20px;
    }

    .ltn__breadcrumb-area {
        background-size: cover;
        background-position: center;
    }
</style>
<footer>
<p>&copy; 2025 Share|K. All rights reserved.</p>
</footer>
<?php include("./footer.php"); ?>
