<?php
// item/update.php - Handles FR-9 product update and FR-14 restocking (with server-side validation)
require_once "../includes/admin_check.php"; // FR-3: admin only, runs before any output
require_once "../includes/config.php";
require_once "../includes/product_validation.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

// if the upload was bigger than the server allows, PHP drops the whole form
if (empty($_POST) && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH'])) {
    $_SESSION['message'] = 'The upload was too large, so nothing was changed. Use an image of 5 MB or less.';
    header("Location: index.php");
    exit;
}

$item_id = filter_var($_POST['item_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

// the product must exist
$exists = false;
if ($item_id !== false) {
    $chk = mysqli_prepare($conn, "SELECT item_id FROM item WHERE item_id = ?");
    mysqli_stmt_bind_param($chk, "i", $item_id);
    mysqli_stmt_execute($chk);
    $exists = (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
}
if (!$exists) {
    $_SESSION['message'] = 'Product not found, so nothing was changed.';
    header("Location: index.php");
    exit;
}

$upload_dir = __DIR__ . "/images/";

// ---- 1. validate everything BEFORE saving anything ----
list($errors, $clean) = validateProductFields($_POST);

// FR-9: the image is optional here; if one is chosen it must be a valid JPG/JPEG/PNG
$image = $_FILES['product_image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
$has_new_image = ($image['error'] !== UPLOAD_ERR_NO_FILE);
if ($has_new_image && ($err = validateImageUpload($image, 'The new product image'))) {
    $errors[] = $err;
}

if ($errors) {
    $_SESSION['message']   = implode('<br>', $errors);
    $_SESSION['old_input'] = array_map('strval', array_filter($_POST, 'is_scalar'));
    header("Location: edit.php?id=" . $item_id);
    exit;
}

// ---- 2. save the new image file (if any), then the database rows together ----
$new_path = null;

try {
    if ($has_new_image) {
        $new_filename = productImageName('upd', $image['name']);
        $new_path     = $upload_dir . $new_filename;
        if (!move_uploaded_file($image['tmp_name'], $new_path)) {
            $new_path = null;
            throw new RuntimeException('Could not save the new image.');
        }
    }

    mysqli_begin_transaction($conn);

    if ($has_new_image) {
        $stmt_item = mysqli_prepare($conn,
            "UPDATE item SET item_name = ?, description = ?, origin = ?, category = ?, cost_price = ?, sell_price = ?, img_path = ?
             WHERE item_id = ?");
        mysqli_stmt_bind_param($stmt_item, "ssssddsi", $clean['item_name'], $clean['description'], $clean['origin'],
            $clean['category'], $clean['cost_price'], $clean['sell_price'], $new_filename, $item_id);
    } else {
        $stmt_item = mysqli_prepare($conn,
            "UPDATE item SET item_name = ?, description = ?, origin = ?, category = ?, cost_price = ?, sell_price = ?
             WHERE item_id = ?");
        mysqli_stmt_bind_param($stmt_item, "ssssddi", $clean['item_name'], $clean['description'], $clean['origin'],
            $clean['category'], $clean['cost_price'], $clean['sell_price'], $item_id);
    }
    mysqli_stmt_execute($stmt_item);
    mysqli_stmt_close($stmt_item);

    // FR-14 restocking; if the product has no inventory record yet, this creates it
    $stmt_stock = mysqli_prepare($conn,
        "INSERT INTO stock (item_id, quantity) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)");
    mysqli_stmt_bind_param($stmt_stock, "ii", $item_id, $clean['quantity']);
    mysqli_stmt_execute($stmt_stock);
    mysqli_stmt_close($stmt_stock);

    mysqli_commit($conn);

    // back to the admin product list, which shows the "Product updated." message
    $_SESSION['success'] = 'Product updated.';
    header("Location: index.php");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);
    if ($new_path) {
        @unlink($new_path);                  // don't keep an image for a change that wasn't saved
    }
    error_log('item/update.php: ' . $e->getMessage());

    $_SESSION['message']   = 'The product could not be updated, so nothing was changed. Please try again.';
    $_SESSION['old_input'] = array_map('strval', array_filter($_POST, 'is_scalar'));
    header("Location: edit.php?id=" . $item_id);
    exit;
}