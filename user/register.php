<?php
// user/register.php - FR-1: registration FORM only (the form posts to store.php)
include('../includes/session.php');
include('../includes/header.php');
?>

<h1 align="center" class="mb-4">Register</h1>

<div class="row col-md-4 mx-auto">
    <?php include('../includes/alert.php'); ?>
    <form action="store.php" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password (min. 6 characters)</label>
            <input type="password" class="form-control" id="password" name="password" minlength="6" required>
        </div>

        <div class="mb-3">
            <label for="password2" class="form-label">Confirm password</label>
            <input type="password" class="form-control" id="password2" name="confirmPass" minlength="6" required>
        </div>

        <button type="submit" class="btn btn-dark w-100">Create Account</button>

        <div class="text-center mt-3">
            <p>Already registered? <a href="login.php">Log in</a></p>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>