<?php
// item/delete.php - FR-10: Delete Product & Inventory Record
// Deleting is a POST-only action (never a plain link), admin only.
require_once "../includes/admin_check.php"; // FR-3: admin only, runs before any output
require_once "../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = 'Invalid request. Please use the Delete button on the product list.';
    header("Location: index.php");
    exit;
}

$item_id = (isset($_POST['item_id']) && ctype_digit((string) $_POST['item_id'])) ? (int) $_POST['item_id'] : 0;
if ($item_id <= 0) {
    $_SESSION['message'] = 'Invalid product ID.';
    header("Location: index.php");
    exit;
}

// Make sure the product exists (and remember its name for the confirmation message)
$stmt_find = mysqli_prepare($conn, "SELECT item_name FROM item WHERE item_id = ?");
mysqli_stmt_bind_param($stmt_find, "i", $item_id);
mysqli_stmt_execute($stmt_find);
$found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_find));
mysqli_stmt_close($stmt_find);

if (!$found) {
    $_SESSION['message'] = 'Product not found. It may have already been deleted.';
    header("Location: index.php");
    exit;
}

mysqli_begin_transaction($conn);

try {
    $sql_stock = "DELETE FROM stock WHERE item_id = ?";
    $stmt_stock = mysqli_prepare($conn, $sql_stock);
    mysqli_stmt_bind_param($stmt_stock, "i", $item_id);
    mysqli_stmt_execute($stmt_stock);
    mysqli_stmt_close($stmt_stock);

    // item_images and reviews are removed by their foreign keys (ON DELETE CASCADE);
    // order_items keeps its rows and just sets item_id to NULL, so order history stays intact.
    $sql_item = "DELETE FROM item WHERE item_id = ?";
    $stmt_item = mysqli_prepare($conn, $sql_item);
    mysqli_stmt_bind_param($stmt_item, "i", $item_id);
    mysqli_stmt_execute($stmt_item);
    mysqli_stmt_close($stmt_item);

    mysqli_commit($conn);

    $_SESSION['success'] = 'Product "' . htmlspecialchars($found['item_name']) . '" was deleted.';
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['message'] = 'Could not delete the product. Please try again.';
    header("Location: index.php");
    exit;
}