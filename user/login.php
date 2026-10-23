<?php
// user/login.php - FR-2: Login, MP2: Block Inactive Users, MP5: No HTML5 Validation
require_once "../includes/session.php";
require_once "../includes/config.php";

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
    
    // Fetch user including the status column
    $sql    = "SELECT user_id, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1";
    $result = mysqli_execute_query($conn, $sql, [$email]);

    if ($result->num_rows === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // MP2 Check: Is the account deactivated?
        if ($row['status'] === 'inactive') {
            $_SESSION['message'] = 'Your account has been deactivated. Please contact support.';
        } 
        // Normal password check
        elseif (password_verify(trim($_POST['password']), $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $row['user_id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_role']  = $row['role'];
            header('Location: ' . afterLoginPage());
            exit;
        } else {
            $_SESSION['message'] = 'Wrong email or password.';
        }
    } else {
        if (empty($email) || empty($_POST['password'])) {
            $_SESSION['message'] = 'Please enter both email and password.';
        } else {
            $_SESSION['message'] = 'Wrong email or password.';
        }
    }
} // <--- THIS is the curly brace that was missing!

require_once "../includes/header.php";
?>

<div class="auth-wrapper">
    <h1 class="auth-title">Welcome Back</h1>
    <p class="auth-subtitle">Sign in to access your coffee orders and saved details</p>

    <div class="auth-card">
        <?php require_once "../includes/alert.php"; ?>

        <!-- Form submitting to itself -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            
            <div class="mb-3">
                <label for="email" class="auth-label">Email Address</label>
                <!-- MP5: Removed required and type="email" -->
                <input type="text" class="form-control auth-input" id="email" name="email" 
                       placeholder="e.g. name@example.com">
            </div>

            <div class="mb-4">
                <label for="password" class="auth-label">Password</label>
                <!-- MP5: Removed required -->
                <input type="password" class="form-control auth-input" id="password" name="password" 
                       placeholder="••••••••">
            </div>

            <button type="submit" class="auth-submit-btn" name="submit">
                Log In
            </button>

            <p class="auth-switch-text">
                No account yet? <a href="register.php" class="auth-switch-link">Register here</a>
            </p>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
