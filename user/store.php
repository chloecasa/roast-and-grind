<?php
// user/store.php - FR-1: handles the registration form (validate, hash password, save user)
include('../includes/session.php');
include('../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$email       = trim($_POST['email']);
$password    = trim($_POST['password']);
$confirmPass = trim($_POST['confirmPass']);

// validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = 'email invalid format';
    header('Location: register.php');
    exit;
}
if (strlen($password) < 6) {
    $_SESSION['message'] = 'password should be at least 6 characters';
    header('Location: register.php');
    exit;
}
if ($password !== $confirmPass) {
    $_SESSION['message'] = 'passwords do not match';
    header('Location: register.php');
    exit;
}

try {
    $password = password_hash($password, PASSWORD_BCRYPT); // hashed, never stored plain

    $sql   = "INSERT INTO users (email, password_hash) VALUES (?, ?)";
    $stmt1 = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt1, 'ss', $email, $password);
    mysqli_stmt_execute($stmt1);
    $new_id = mysqli_insert_id($conn);
} catch (mysqli_sql_exception $e) {
    // email is UNIQUE, so a duplicate email lands here
    $_SESSION['message'] = ($e->getCode() == 1062)
        ? 'that email is already registered. Try logging in.'
        : 'could not create the account. Please try again.';
    header('Location: register.php');
    exit;
}

// log the new customer in right away; the cart stays in the session - FR-18
session_regenerate_id(true);
$_SESSION['user_id']    = $new_id;
$_SESSION['user_email'] = $email;
$_SESSION['user_role']  = 'user';

$pages = ['checkout.php' => '../checkout.php', 'shop.php' => '../shop.php', 'index.php' => '../index.php'];
$page  = $_SESSION['redirect_after_login'] ?? 'index.php';
unset($_SESSION['redirect_after_login']);
header('Location: ' . ($pages[$page] ?? '../index.php'));
exit;