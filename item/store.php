<?php
// item/store.php - FR-8: Add Product / MP5: server-side (PHP) validation, no HTML5 validation
// The checks live in includes/product_validation.php (shared with item/update.php).
require_once "../includes/admin_check.php";
require_once "../includes/config.php";
require_once "../includes/product_validation.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: create.php");
    exit;
}

// 1. Validate the text/number fields (name, category, origin, description, prices, stock)
[$errors, $clean] = validateProductFields($_POST);

// 2. Validate the primary image (required) and the gallery images (optional)
$primary = $_FILES['product_image'] ?? null;
if (!$primary || $primary['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = "A primary product image is required.";
} elseif (($msg = validateImageUpload($primary, 'Primary image')) !== null) {
    $errors[] = $msg;
}

$gallery = [];   // valid gallery files, ready to be saved
if (isset($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
    foreach ($_FILES['gallery_images']['name'] as $key => $name) {
        if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $file = [
            'name'     => $name,
            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$key],
            'error'    => $_FILES['gallery_images']['error'][$key],
            'size'     => $_FILES['gallery_images']['size'][$key]
        ];
        $msg = validateImageUpload($file, 'Gallery image "' . htmlspecialchars($name) . '"');
        if ($msg !== null) {
            $errors[] = $msg;
        } else {
            $gallery[$key] = $file;
        }
    }
}

// If validation fails, return to the form with the errors and the old input
if (count($errors) > 0) {
    $_SESSION['message']   = implode('<br>', $errors);
    $_SESSION['old_input'] = $_POST;
    header("Location: create.php");
    exit;
}

// 3. Save the files and the database rows together (all or nothing)
$upload_dir = __DIR__ . "/images/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$saved_files = [];   // files written in this request (deleted again if the transaction fails)

mysqli_begin_transaction($conn);

try {
    // Primary image
    $primary_img = productImageName('primary', $primary['name']);
    if (!move_uploaded_file($primary['tmp_name'], $upload_dir . $primary_img)) {
        throw new RuntimeException('The primary image could not be saved on the server. Please try again.');
    }
    $saved_files[] = $primary_img;

    // Product row
    $stmt_item = mysqli_prepare($conn,
        "INSERT INTO item (item_name, description, origin, category, cost_price, sell_price, img_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_item, "ssssdds",
        $clean['item_name'], $clean['description'], $clean['origin'], $clean['category'],
        $clean['cost_price'], $clean['sell_price'], $primary_img);
    mysqli_stmt_execute($stmt_item);
    $item_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_item);

    // Stock row (FR-8: initial available stock quantity)
    $stmt_stock = mysqli_prepare($conn, "INSERT INTO stock (item_id, quantity) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_stock, "ii", $item_id, $clean['quantity']);
    mysqli_stmt_execute($stmt_stock);
    mysqli_stmt_close($stmt_stock);

    // Gallery images (MP1: multiple photos)
    if ($gallery) {
        $stmt_gal = mysqli_prepare($conn, "INSERT INTO item_images (item_id, img_path) VALUES (?, ?)");
        foreach ($gallery as $key => $file) {
            $gal_filename = productImageName($key, $file['name']);
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $gal_filename)) {
                throw new RuntimeException('A gallery image could not be saved on the server. Please try again.');
            }
            $saved_files[] = $gal_filename;
            mysqli_stmt_bind_param($stmt_gal, "is", $item_id, $gal_filename);
            mysqli_stmt_execute($stmt_gal);
        }
        mysqli_stmt_close($stmt_gal);
    }

    mysqli_commit($conn);
    $_SESSION['success'] = "Product created successfully!";
    header("Location: index.php");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);

    foreach ($saved_files as $file) {
        $path = $upload_dir . basename($file);
        if (is_file($path)) unlink($path);
    }

    $_SESSION['message'] = ($e instanceof RuntimeException)
        ? $e->getMessage()
        : 'Could not save the product. Please try again.';
    $_SESSION['old_input'] = $_POST;
    header("Location: create.php");
    exit;
}