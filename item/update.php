<?php
// item/update.php - FR-9: Update Product / FR-14: Restocking / MP5: server-side (PHP) validation
// The checks live in includes/product_validation.php (shared with item/store.php).
require_once "../includes/admin_check.php";
require_once "../includes/config.php";
require_once "../includes/product_validation.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// 1. Which product?
$item_id = (isset($_POST['item_id']) && ctype_digit((string) $_POST['item_id'])) ? (int) $_POST['item_id'] : 0;
if ($item_id <= 0) {
    $_SESSION['message'] = 'Invalid product ID.';
    header("Location: index.php");
    exit;
}

$stmt_find = mysqli_prepare($conn, "SELECT item_id FROM item WHERE item_id = ?");
mysqli_stmt_bind_param($stmt_find, "i", $item_id);
mysqli_stmt_execute($stmt_find);
$exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_find));
mysqli_stmt_close($stmt_find);

if (!$exists) {
    $_SESSION['message'] = 'Product not found. It may have been deleted.';
    header("Location: index.php");
    exit;
}

// 2. Validate the text/number fields (name, category, origin, description, prices, stock)
[$errors, $clean] = validateProductFields($_POST);

// 3. Validate the images (both optional on update)
$primary = $_FILES['product_image'] ?? null;
$has_new_primary = $primary && $primary['error'] !== UPLOAD_ERR_NO_FILE;
if ($has_new_primary && ($msg = validateImageUpload($primary, 'Primary image')) !== null) {
    $errors[] = $msg;
}

$gallery = [];   // valid new gallery files, ready to be saved
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

// If errors exist, go back to the edit form with the messages and the old input
if (count($errors) > 0) {
    $_SESSION['message']   = implode('<br>', $errors);
    $_SESSION['old_input'] = $_POST;
    header("Location: edit.php?id=" . $item_id);
    exit;
}

// 4. Save the files and the database changes together (all or nothing)
$upload_dir = __DIR__ . "/images/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$saved_files     = [];   // files written in this request (deleted again if the transaction fails)
$files_to_delete = [];   // removed gallery files (deleted from disk only after a successful commit)

mysqli_begin_transaction($conn);

try {
    // Product row (+ new primary image if one was uploaded)
    if ($has_new_primary) {
        $new_primary = productImageName('primary', $primary['name']);
        if (!move_uploaded_file($primary['tmp_name'], $upload_dir . $new_primary)) {
            throw new RuntimeException('The primary image could not be saved on the server. Please try again.');
        }
        $saved_files[] = $new_primary;

        $stmt_item = mysqli_prepare($conn,
            "UPDATE item SET item_name=?, description=?, origin=?, category=?, cost_price=?, sell_price=?, img_path=? WHERE item_id=?");
        mysqli_stmt_bind_param($stmt_item, "ssssddsi",
            $clean['item_name'], $clean['description'], $clean['origin'], $clean['category'],
            $clean['cost_price'], $clean['sell_price'], $new_primary, $item_id);
    } else {
        $stmt_item = mysqli_prepare($conn,
            "UPDATE item SET item_name=?, description=?, origin=?, category=?, cost_price=?, sell_price=? WHERE item_id=?");
        mysqli_stmt_bind_param($stmt_item, "ssssddi",
            $clean['item_name'], $clean['description'], $clean['origin'], $clean['category'],
            $clean['cost_price'], $clean['sell_price'], $item_id);
    }
    mysqli_stmt_execute($stmt_item);
    mysqli_stmt_close($stmt_item);

    // FR-14 Restocking
    $stmt_stock = mysqli_prepare($conn, "UPDATE stock SET quantity = ? WHERE item_id = ?");
    mysqli_stmt_bind_param($stmt_stock, "ii", $clean['quantity'], $item_id);
    mysqli_stmt_execute($stmt_stock);
    mysqli_stmt_close($stmt_stock);

    // MP1: Remove selected gallery photos (only rows that belong to THIS product)
    if (!empty($_POST['remove_images']) && is_array($_POST['remove_images'])) {
        $stmt_find_img = mysqli_prepare($conn, "SELECT img_path FROM item_images WHERE image_id = ? AND item_id = ?");
        $stmt_del      = mysqli_prepare($conn, "DELETE FROM item_images WHERE image_id = ? AND item_id = ?");

        foreach ($_POST['remove_images'] as $raw_id) {
            $image_id = intval($raw_id);
            if ($image_id <= 0) continue;

            mysqli_stmt_bind_param($stmt_find_img, "ii", $image_id, $item_id);
            mysqli_stmt_execute($stmt_find_img);
            $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_find_img));
            if (!$found) continue;

            mysqli_stmt_bind_param($stmt_del, "ii", $image_id, $item_id);
            mysqli_stmt_execute($stmt_del);
            $files_to_delete[] = $found['img_path'];
        }
        mysqli_stmt_close($stmt_find_img);
        mysqli_stmt_close($stmt_del);
    }

    // MP1: Add new gallery photos
    if ($gallery) {
        $stmt_gal = mysqli_prepare($conn, "INSERT INTO item_images (item_id, img_path) VALUES (?, ?)");
        foreach ($gallery as $key => $file) {
            $gal_filename = productImageName("g{$key}_" . bin2hex(random_bytes(2)), $file['name']);
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

    $_SESSION['success'] = "Product and stock updated successfully!";
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
        : 'Could not update the product. Please try again.';
    header("Location: edit.php?id=" . $item_id);
    exit;
}