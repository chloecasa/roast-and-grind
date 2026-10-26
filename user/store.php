<?php
// user/store.php - MP5: PHP Validation / MP2: Upload Photo
include('../includes/session.php');
include('../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$email       = trim($_POST['email'] ?? '');
$password    = $_POST['password'] ?? '';
$confirmPass = $_POST['confirmPass'] ?? '';


$errors = [];

if (empty($email)) {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address format.';
}

if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long.';
}

if ($password !== $confirmPass) {
    $errors[] = 'Passwords do not match.';
}

if (count($errors) > 0) {
    $_SESSION['message'] = implode('<br>', $errors);
    header('Location: register.php');
    exit;
}

$photo_filename = 'default_avatar.png';
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $up  = $_FILES['profile_photo'];
    $ext = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));
    $photo_error = '';

    if ($up['error'] === UPLOAD_ERR_INI_SIZE || $up['error'] === UPLOAD_ERR_FORM_SIZE) {
        $photo_error = 'The profile photo is too large. Please choose a smaller image.';
    } elseif ($up['error'] !== UPLOAD_ERR_OK) {
        $photo_error = 'The profile photo could not be uploaded. Please try again.';
    } elseif (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        $photo_error = 'Profile photo must be a JPG or PNG image.';
    } else {
        $photo_dir = __DIR__ . '/images/';
        if (!is_dir($photo_dir)) {
            mkdir($photo_dir, 0755, true);   
        }
        $new_name = time() . "_" . preg_replace('/[^a-zA-Z0-9]/', '', basename($up['name'], ".$ext")) . ".$ext";
        if (move_uploaded_file($up['tmp_name'], $photo_dir . $new_name)) {
            $photo_filename = $new_name;
        } else {
            $photo_error = 'The profile photo could not be saved on the server. Please try again.';
        }
    }

    if ($photo_error !== '') {
        $_SESSION['message'] = $photo_error;
        header('Location: register.php');
        exit;
    }
}

try {
    $password_hashed = password_hash($password, PASSWORD_BCRYPT); 

    mysqli_begin_transaction($conn);

    $sql   = "INSERT INTO users (email, password_hash) VALUES (?, ?)";
    $stmt1 = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt1, 'ss', $email, $password_hashed);
    mysqli_stmt_execute($stmt1);
    $new_id = mysqli_insert_id($conn);


    $stmt2 = mysqli_prepare($conn, "INSERT INTO customers (user_id, profile_photo) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt2, 'is', $new_id, $photo_filename);
    mysqli_stmt_execute($stmt2);

    mysqli_commit($conn);
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conn);
    $_SESSION['message'] = ($e->getCode() == 1062)
        ? 'That email is already registered. Try logging in.'
        : 'Could not create the account. Please try again.';
    header('Location: register.php');
    exit;
}


session_regenerate_id(true);
$_SESSION['user_id']    = $new_id;
$_SESSION['user_email'] = $email;
$_SESSION['user_role']  = 'user';

$pages = ['checkout.php' => '../checkout.php', 'shop.php' => '../shop.php', 'index.php' => '../index.php'];
$page  = $_SESSION['redirect_after_login'] ?? 'index.php';
unset($_SESSION['redirect_after_login']);
header('Location: ' . ($pages[$page] ?? '../index.php'));
exit;