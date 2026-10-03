<?php
// user/login.php - FR-2: login (guests are sent here by checkout.php, FR-18)
include('../includes/session.php');
include('../includes/config.php');

// page to return to after login (checkout.php sets it); only these are allowed
function afterLoginPage()
{
    $pages = ['checkout.php' => '../checkout.php', 'shop.php' => '../shop.php', 'index.php' => '../index.php'];
    $page  = $_SESSION['redirect_after_login'] ?? 'index.php';
    unset($_SESSION['redirect_after_login']);
    return $pages[$page] ?? '../index.php';
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . afterLoginPage());
    exit;
}

if (isset($_POST['submit'])) {

    $email  = trim($_POST['email']);
    $sql    = "SELECT user_id, email, password_hash, role FROM users WHERE email = ? LIMIT 1";
    $result = mysqli_execute_query($conn, $sql, [$email]);

    if ($result->num_rows === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify(trim($_POST['password']), $row['password_hash'])) {
            // new session id, but the session DATA (the cart) is kept - FR-18
            session_regenerate_id(true);
            $_SESSION['user_id']    = $row['user_id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_role']  = $row['role'];
            header('Location: ' . afterLoginPage());
            exit;
        }
    }
    $_SESSION['message'] = 'wrong email or password';
}

include('../includes/header.php');
?>

<h1 align="center" class="mb-4">Log In</h1>

<div class="row col-md-4 mx-auto">
    <?php include('../includes/alert.php'); ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-dark w-100" name="submit">Log In</button>

        <div class="text-center mt-3">
            <p>No account yet? <a href="register.php">Register</a></p>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>