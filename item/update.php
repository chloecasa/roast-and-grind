<?php
// item/update.php - MP5: Strict PHP Validation for Product Edits
require_once "../includes/admin_check.php";
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$item_id     = intval($_POST['item_id'] ?? 0);
$item_name   = trim($_POST['item_name'] ?? '');
$category    = trim($_POST['category'] ?? '');
$origin      = trim($_POST['origin'] ?? '');
$description = trim($_POST['description'] ?? '');
$cost_price  = trim($_POST['cost_price'] ?? '');
$sell_price  = trim($_POST['sell_price'] ?? '');
$quantity    = trim($_POST['quantity'] ?? '');

// 1. Strict Server-Side Validation (MP5)
$errors = [];

if (empty($item_name)) $errors[] = "Product name is required.";
if (empty($category)) $errors[] = "Please select a category.";
if (empty($origin)) $errors[] = "Origin is required.";
if (empty($description)) $errors[] = "Description is required.";

if (!is_numeric($cost_price) || floatval($cost_price) < 0) {
    $errors[] = "Cost price must be a valid positive number.";
}
if (!is_numeric($sell_price) || floatval($sell_price) <= 0) {
    $errors[] = "Selling price must be a valid number greater than zero.";
}
if (!is_numeric($quantity) || intval($quantity) < 0 || strpos($quantity, '.') !== false) {
    $errors[] = "Stock quantity must be a valid whole number (0 or higher).";
}

// If errors exist, redirect back with error messages and old input
if (count($errors) > 0) {
    $_SESSION['message'] = implode('<br>', $errors);
    $_SESSION['old_input'] = $_POST;
    header("Location: edit.php?id=" . $item_id);
    exit;
}

// Formatting after passing validation
$cost_price = floatval($cost_price);
$sell_price = floatval($sell_price);
$quantity   = intval($quantity);

// 2. Check if a new image was uploaded
$new_image_uploaded = false;
$new_filename = null;
$allowed_exts = ['jpg', 'jpeg', 'png'];

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $file_name = $_FILES['product_image']['name'];
    $file_tmp  = $_FILES['product_image']['tmp_name'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (in_array($file_ext, $allowed_exts)) {
        $new_filename = time() . "_primary_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file_name);
        $upload_dest  = __DIR__ . "/images/" . $new_filename;

        if (move_uploaded_file($file_tmp, $upload_dest)) {
            $new_image_uploaded = true;
        }
    }
}

// 3. Database Updates using Transaction
mysqli_begin_transaction($conn);

try {
    if ($new_image_uploaded) {
        $sql_item = "UPDATE item SET item_name=?, description=?, origin=?, category=?, cost_price=?, sell_price=?, img_path=? WHERE item_id=?";
        $stmt_item = mysqli_prepare($conn, $sql_item);
        mysqli_stmt_bind_param($stmt_item, "ssssddsi", $item_name, $description, $origin, $category, $cost_price, $sell_price, $new_filename, $item_id);
    } else {
        $sql_item = "UPDATE item SET item_name=?, description=?, origin=?, category=?, cost_price=?, sell_price=? WHERE item_id=?";
        $stmt_item = mysqli_prepare($conn, $sql_item);
        mysqli_stmt_bind_param($stmt_item, "ssssddi", $item_name, $description, $origin, $category, $cost_price, $sell_price, $item_id);
    }

    mysqli_stmt_execute($stmt_item);
    mysqli_stmt_close($stmt_item);

    // FR-14 Restocking
    $sql_stock = "UPDATE stock SET quantity = ? WHERE item_id = ?";
    $stmt_stock = mysqli_prepare($conn, $sql_stock);
    mysqli_stmt_bind_param($stmt_stock, "ii", $quantity, $item_id);
    mysqli_stmt_execute($stmt_stock);
    mysqli_stmt_close($stmt_stock);

    mysqli_commit($conn);
    $_SESSION['success'] = "Product and stock updated successfully!";
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    header("Location: edit.php?id=" . $item_id);
    exit;
}
?>
