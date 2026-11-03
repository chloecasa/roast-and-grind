<?php

require_once "includes/session.php";
require_once "includes/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: shop.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$item_id = (isset($_POST['item_id']) && ctype_digit((string) $_POST['item_id'])) ? (int) $_POST['item_id'] : 0;


$redirect = 'shop.php';
if (isset($_POST['redirect'])) {
    $page = strtok($_POST['redirect'], '?');
    if (in_array($page, ['shop.php'])) {
        $redirect = $_POST['redirect'];
    }
}

if ($item_id <= 0) {
    $_SESSION['message'] = "Invalid product.";
    header("Location: $redirect");
    exit;
}

try {
    
    $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE user_id = ? AND item_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $item_id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success'] = "Your review has been deleted.";
    } else {
        $_SESSION['message'] = "No review of yours was found for this product.";
    }
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $_SESSION['message'] = "Could not delete your review. Please try again.";
}

header("Location: $redirect");
exit;