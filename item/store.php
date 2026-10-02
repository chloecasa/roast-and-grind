<?php
// item/store.php - Handles FR-8 with Multiple Gallery Images
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_name   = trim($_POST['item_name']);
    $category    = trim($_POST['category']);
    $origin      = trim($_POST['origin']);
    $description = trim($_POST['description']);
    $cost_price  = floatval($_POST['cost_price']);
    $sell_price  = floatval($_POST['sell_price']);
    $quantity    = intval($_POST['quantity']);

    $allowed_exts = ['jpg', 'jpeg', 'png'];
    $upload_dir   = __DIR__ . "/images/";

    // 1. Process Primary Image
    $primary_img = "default_coffee.jpg";
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed_exts)) {
            $new_name = time() . "_primary_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['product_image']['name']);
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $new_name)) {
                $primary_img = $new_name;
            }
        }
    }

    // 2. Database Insert Transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert Item
        $sql_item = "INSERT INTO item (item_name, description, origin, category, cost_price, sell_price, img_path) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_item = mysqli_prepare($conn, $sql_item);
        mysqli_stmt_bind_param($stmt_item, "ssssdds", $item_name, $description, $origin, $category, $cost_price, $sell_price, $primary_img);
        mysqli_stmt_execute($stmt_item);
        $item_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_item);

        // Insert Stock
        $sql_stock = "INSERT INTO stock (item_id, quantity) VALUES (?, ?)";
        $stmt_stock = mysqli_prepare($conn, $sql_stock);
        mysqli_stmt_bind_param($stmt_stock, "ii", $item_id, $quantity);
        mysqli_stmt_execute($stmt_stock);
        mysqli_stmt_close($stmt_stock);

        // 3. Process Multiple Gallery Images
        if (isset($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            $sql_gal = "INSERT INTO item_images (item_id, img_path) VALUES (?, ?)";
            $stmt_gal = mysqli_prepare($conn, $sql_gal);

            foreach ($_FILES['gallery_images']['name'] as $key => $name) {
                if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed_exts)) {
                        $gal_filename = time() . "_{$key}_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $name);
                        if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$key], $upload_dir . $gal_filename)) {
                            mysqli_stmt_bind_param($stmt_gal, "is", $item_id, $gal_filename);
                            mysqli_stmt_execute($stmt_gal);
                        }
                    }
                }
            }
            mysqli_stmt_close($stmt_gal);
        }

        mysqli_commit($conn);
        header("Location: ../shop.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("Error saving product: " . $e->getMessage());
    }
}
?>
