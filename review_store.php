<?php
// review_store.php - MP4: Submit/Update Review & Regex Foul Word Filter
require_once "includes/session.php";
require_once "includes/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: shop.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$item_id = (int)$_POST['item_id'];
$raw_review = trim($_POST['review_text']);
$redirect = $_POST['redirect'] ?? 'shop.php';

if (empty($raw_review)) {
    $_SESSION['message'] = "Review cannot be empty.";
    header("Location: $redirect");
    exit;
}

// 1. Verify that the user ACTUALLY BOUGHT this item (MP4 Requirement)
$check_purchase_sql = "
    SELECT 1 FROM orders o 
    JOIN order_items oi ON o.order_id = oi.order_id 
    WHERE o.user_id = ? AND oi.item_id = ? AND o.status != 'Canceled' 
    LIMIT 1
";
$chk_stmt = mysqli_prepare($conn, $check_purchase_sql);
mysqli_stmt_bind_param($chk_stmt, "ii", $user_id, $item_id);
mysqli_stmt_execute($chk_stmt);
$has_purchased = mysqli_stmt_get_result($chk_stmt)->num_rows > 0;
mysqli_stmt_close($chk_stmt);

if (!$has_purchased) {
    $_SESSION['message'] = "You can only review products you have purchased.";
    header("Location: $redirect");
    exit;
}

// 2. REGEX FOUL WORD FILTER (MP4 Requirement)
// Array of words to filter out (You can add more if needed)
$bad_words = ['crap', 'damn', 'shit', 'fuck', 'bitch', 'asshole', 'stupid', 'idiot', 'scam'];

// Build the regex pattern dynamically: /\b(crap|damn|shit|...)\b/i
$pattern = '/\b(' . implode('|', $bad_words) . ')\b/i';

// Replace matches with asterisks
$clean_review = preg_replace($pattern, '****', $raw_review);

// 3. Insert or Update the Review
// Uses ON DUPLICATE KEY UPDATE to allow the user to UPDATE their review (MP4 Requirement)
$sql = "INSERT INTO reviews (item_id, user_id, review_text) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE review_text = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iiss", $item_id, $user_id, $clean_review, $clean_review);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "Your review has been saved!";
} else {
    $_SESSION['message'] = "An error occurred while saving your review.";
}
mysqli_stmt_close($stmt);

header("Location: $redirect");
exit;
