<?php
// item/update.php - Handles FR-9 product update and FR-14 restocking
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Collect and sanitize form inputs
    $item_id     = intval($_POST['item_id']);
    $item_name   = trim($_POST['item_name']);
    $category    = trim($_POST['category']);
    $origin      = trim($_POST['origin']);
    $description = trim($_POST['description']);
    $cost_price  = floatval($_POST['cost_price']);
    $sell_price  = floatval($_POST['sell_price']);
    $quantity    = intval($_POST['quantity']);

    // 2. Check if a new image was uploaded
    $new_image_uploaded = false;
    $new_filename = null;

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['product_image']['tmp_name'];
        $file_name = $_FILES['product_image']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file_name);
            $upload_dest  = __DIR__ . "/images/" . $new_filename;

            if (move_uploaded_file($file_tmp, $upload_dest)) {
                $new_image_uploaded = true;
            }
        }
    }

    // 3. Database Updates using Transaction
    mysqli_begin_transaction($conn);

    try {
        // Update 'item' table (with or without image replacement)
        if ($new_image_uploaded) {
            $sql_item = "UPDATE item SET 
                            item_name = ?, 
                            description = ?, 
                            origin = ?, 
                            category = ?, 
                            cost_price = ?, 
                            sell_price = ?, 
                            img_path = ? 
                         WHERE item_id = ?";
            $stmt_item = mysqli_prepare($conn, $sql_item);
            mysqli_stmt_bind_param($stmt_item, "ssssddsi", $item_name, $description, $origin, $category, $cost_price, $sell_price, $new_filename, $item_id);
        } else {
            $sql_item = "UPDATE item SET 
                            item_name = ?, 
                            description = ?, 
                            origin = ?, 
                            category = ?, 
                            cost_price = ?, 
                            sell_price = ? 
                         WHERE item_id = ?";
            $stmt_item = mysqli_prepare($conn, $sql_item);
            mysqli_stmt_bind_param($stmt_item, "ssssddi", $item_name, $description, $origin, $category, $cost_price, $sell_price, $item_id);
        }

        mysqli_stmt_execute($stmt_item);
        mysqli_stmt_close($stmt_item);

        // Update 'stock' table (FR-14 Restocking)
        $sql_stock = "UPDATE stock SET quantity = ? WHERE item_id = ?";
        $stmt_stock = mysqli_prepare($conn, $sql_stock);
        mysqli_stmt_bind_param($stmt_stock, "ii", $quantity, $item_id);
        mysqli_stmt_execute($stmt_stock);
        mysqli_stmt_close($stmt_stock);

        // Commit all changes
        mysqli_commit($conn);

        // Redirect back to storefront to verify
        header("Location: ../index.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("Error updating product: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>
