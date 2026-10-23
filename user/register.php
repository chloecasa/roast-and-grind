<?php
// user/register.php - MP5: No HTML5 Validation / MP2: Profile Photo Upload
require_once "../includes/session.php";

if (isset($_SESSION['user_id'])) {
    header('Location: ../shop.php');
    exit;
}
require_once "../includes/header.php";
?>

<div class="auth-wrapper">
    <h1 class="auth-title">Create Account</h1>
    <p class="auth-subtitle">Join Roast & Grind to place orders and track deliveries</p>

    <div class="auth-card">
        <?php require_once "../includes/alert.php"; ?>

        <!-- ENCTYPE added for file uploads -->
        <form action="store.php" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="email" class="auth-label">Email Address</label>
                <!-- MP5: type="text" instead of email, NO 'required' attribute -->
                <input type="text" class="form-control auth-input" id="email" name="email" 
                       placeholder="e.g. name@example.com">
            </div>

            <div class="mb-3">
                <label for="password" class="auth-label">Password (Min. 6 Characters)</label>
                <!-- MP5: NO 'required', NO 'minlength' -->
                <input type="password" class="form-control auth-input" id="password" name="password" 
                       placeholder="••••••••">
            </div>

            <div class="mb-3">
                <label for="password2" class="auth-label">Confirm Password</label>
                <!-- MP5: NO 'required', NO 'minlength' -->
                <input type="password" class="form-control auth-input" id="password2" name="confirmPass" 
                       placeholder="••••••••">
            </div>

            <!-- MP2: Profile Photo Upload -->
            <div class="mb-4">
                <label for="profile_photo" class="auth-label">Profile Photo (Optional)</label>
                <input type="file" class="form-control auth-input" id="profile_photo" name="profile_photo" accept=".jpg,.jpeg,.png" style="padding: 10px 20px !important;">
            </div>

            <button type="submit" class="auth-submit-btn">
                Create Account
            </button>

            <p class="auth-switch-text">
                Already registered? <a href="login.php" class="auth-switch-link">Log in here</a>
            </p>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
