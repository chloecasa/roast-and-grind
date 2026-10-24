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

$upload_dir = __DIR__ . "/images/";

// 3. Database Updates using Transaction
$new_gallery_files = [];   // files saved in this request (deleted again if the transaction fails)
$files_to_delete   = [];   // removed gallery files (deleted from disk only after a successful commit)
$skipped_files     = 0;

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

    // MP1: Remove selected gallery photos (only rows that belong to THIS product)
    if (!empty($_POST['remove_images']) && is_array($_POST['remove_images'])) {
        $stmt_find = mysqli_prepare($conn, "SELECT img_path FROM item_images WHERE image_id = ? AND item_id = ?");
        $stmt_del  = mysqli_prepare($conn, "DELETE FROM item_images WHERE image_id = ? AND item_id = ?");

        foreach ($_POST['remove_images'] as $raw_id) {
            $image_id = intval($raw_id);
            if ($image_id <= 0) continue;

            mysqli_stmt_bind_param($stmt_find, "ii", $image_id, $item_id);
            mysqli_stmt_execute($stmt_find);
            $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_find));
            if (!$found) continue;

            mysqli_stmt_bind_param($stmt_del, "ii", $image_id, $item_id);
            mysqli_stmt_execute($stmt_del);
            $files_to_delete[] = $found['img_path'];
        }
        mysqli_stmt_close($stmt_find);
        mysqli_stmt_close($stmt_del);
    }

    // MP1: Add new gallery photos
    if (isset($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
        $stmt_gal = mysqli_prepare($conn, "INSERT INTO item_images (item_id, img_path) VALUES (?, ?)");

        foreach ($_FILES['gallery_images']['name'] as $key => $name) {
            $err = $_FILES['gallery_images']['error'][$key];
            if ($err === UPLOAD_ERR_NO_FILE) continue;

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($err !== UPLOAD_ERR_OK || !in_array($ext, $allowed_exts)) {
                $skipped_files++;
                continue;
            }

            $gal_filename = time() . "_g{$key}_" . bin2hex(random_bytes(2)) . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $name);
            if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$key], $upload_dir . $gal_filename)) {
                $new_gallery_files[] = $gal_filename;
                mysqli_stmt_bind_param($stmt_gal, "is", $item_id, $gal_filename);
                mysqli_stmt_execute($stmt_gal);
            } else {
                $skipped_files++;
            }
        }
        mysqli_stmt_close($stmt_gal);
    }

    mysqli_commit($conn);

    // Delete removed photo files from disk, unless another record still uses the same file
    foreach ($files_to_delete as $file) {
        $stmt_use = mysqli_prepare($conn,
            "SELECT (SELECT COUNT(*) FROM item_images WHERE img_path = ?) +
                    (SELECT COUNT(*) FROM item WHERE img_path = ?) AS uses_count");
        mysqli_stmt_bind_param($stmt_use, "ss", $file, $file);
        mysqli_stmt_execute($stmt_use);
        $use = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_use));
        mysqli_stmt_close($stmt_use);

        $path = $upload_dir . basename($file);
        if ((int) $use['uses_count'] === 0 && is_file($path)) {
            unlink($path);
        }
    }

    $msg = "Product and stock updated successfully!";
    if ($skipped_files > 0) {
        $msg .= " ({$skipped_files} gallery file(s) skipped: only JPG and PNG uploads are accepted.)";
    }
    $_SESSION['success'] = $msg;
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    // Remove any gallery files saved during this failed request
    foreach ($new_gallery_files as $file) {
        $path = $upload_dir . basename($file);
        if (is_file($path)) unlink($path);
    }
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    header("Location: edit.php?id=" . $item_id);
    exit;
}
?>