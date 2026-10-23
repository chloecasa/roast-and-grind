<?php
// item/store.php - Handles FR-8 with Multiple Gallery Images (with server-side validation)
require_once "../includes/admin_check.php"; // FR-3: admin only, runs before any output
require_once "../includes/config.php";
require_once "../includes/product_validation.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: create.php");
    exit;
}

$upload_dir = __DIR__ . "/images/";

// if the upload was bigger than the server allows, PHP drops the whole form
if (empty($_POST) && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH'])) {
    $_SESSION['message'] = 'The upload was too large, so nothing was saved. Use images of 5 MB or less.';
    header("Location: create.php");
    exit;
}

// ---- 1. validate everything BEFORE saving anything ----
list($errors, $clean) = validateProductFields($_POST);

// FR-8: the primary product image (JPG/JPEG, PNG) is required
$primary = $_FILES['product_image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
if ($primary['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'Primary card image is required (JPG, JPEG or PNG).';
} elseif ($err = validateImageUpload($primary, 'The primary card image')) {
    $errors[] = $err;
}

// optional gallery photos: any file that was chosen must be a valid image
$gallery = [];
if (isset($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
    foreach ($_FILES['gallery_images']['name'] as $key => $name) {
        $file = [
            'name'     => $name,
            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$key],
            'error'    => $_FILES['gallery_images']['error'][$key],
            'size'     => $_FILES['gallery_images']['size'][$key]
        ];
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($err = validateImageUpload($file, 'Gallery photo ' . ($key + 1))) {
            $errors[] = $err;
        } else {
            $gallery[$key] = $file;
        }
    }
}

if ($errors) {
    $_SESSION['message']   = implode('<br>', $errors);
    $_SESSION['old_input'] = array_map('strval', array_filter($_POST, 'is_scalar'));
    header("Location: create.php");
    exit;
}

// ---- 2. save the image files, then the database rows together ----
$moved = [];   // files written so far, removed again if anything fails

try {
    $primary_img = productImageName('primary', $primary['name']);
    if (!move_uploaded_file($primary['tmp_name'], $upload_dir . $primary_img)) {
        throw new RuntimeException('Could not save the primary image.');
    }
    $moved[] = $upload_dir . $primary_img;

    $gallery_names = [];
    foreach ($gallery as $key => $file) {
        $gal_filename = productImageName($key, $file['name']);
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $gal_filename)) {
            throw new RuntimeException('Could not save a gallery photo.');
        }
        $moved[]         = $upload_dir . $gal_filename;
        $gallery_names[] = $gal_filename;
    }

    mysqli_begin_transaction($conn);

    $sql_item = "INSERT INTO item (item_name, description, origin, category, cost_price, sell_price, img_path)
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_item = mysqli_prepare($conn, $sql_item);
    mysqli_stmt_bind_param($stmt_item, "ssssdds", $clean['item_name'], $clean['description'], $clean['origin'],
        $clean['category'], $clean['cost_price'], $clean['sell_price'], $primary_img);
    mysqli_stmt_execute($stmt_item);
    $item_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_item);

    $stmt_stock = mysqli_prepare($conn, "INSERT INTO stock (item_id, quantity) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_stock, "ii", $item_id, $clean['quantity']);
    mysqli_stmt_execute($stmt_stock);
    mysqli_stmt_close($stmt_stock);

    if ($gallery_names) {
        $stmt_gal = mysqli_prepare($conn, "INSERT INTO item_images (item_id, img_path) VALUES (?, ?)");
        foreach ($gallery_names as $gal_filename) {
            mysqli_stmt_bind_param($stmt_gal, "is", $item_id, $gal_filename);
            mysqli_stmt_execute($stmt_gal);
        }
        mysqli_stmt_close($stmt_gal);
    }

    mysqli_commit($conn);

    $_SESSION['success'] = 'Product added.';
    header("Location: ../shop.php");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);
    foreach ($moved as $path) {
        @unlink($path);                      // no leftover image files for a product that wasn't saved
    }
    error_log('item/store.php: ' . $e->getMessage());

    $_SESSION['message']   = 'The product could not be saved, so nothing was added. Please try again.';
    $_SESSION['old_input'] = array_map('strval', array_filter($_POST, 'is_scalar'));
    header("Location: create.php");
    exit;
}