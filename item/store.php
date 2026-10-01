<?php
// item/store.php - Handles FR-8 product creation
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Collect and sanitize form inputs
    $item_name   = trim($_POST['item_name']);
    $category    = trim($_POST['category']);
    $origin      = trim($_POST['origin']);
    $description = trim($_POST['description']);
    $cost_price  = floatval($_POST['cost_price']);
    $sell_price  = floatval($_POST['sell_price']);
    $quantity    = intval($_POST['quantity']);

    // 2. Handle Image Upload
    $img_path = "default_coffee.jpg"; // Fallback default image name

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['product_image']['tmp_name'];
        $file_name = $_FILES['product_image']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_exts)) {
            // Generate a unique file name to avoid overwriting existing files
            $new_filename = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file_name);
            $upload_dest  = __DIR__ . "/images/" . $new_filename;

            if (move_uploaded_file($file_tmp, $upload_dest)) {
                $img_path = $new_filename;
            }
        }
    }

    // 3. Database Insertion using a Transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert into 'item' table
        $sql_item = "INSERT INTO item (item_name, description, origin, category, cost_price, sell_price, img_path) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_item = mysqli_prepare($conn, $sql_item);
        mysqli_stmt_bind_param($stmt_item, "ssssdds", $item_name, $description, $origin, $category, $cost_price, $sell_price, $img_path);
        mysqli_stmt_execute($stmt_item);

        // Get the generated item_id
        $item_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_item);

        // Insert initial quantity into 'stock' table
        $sql_stock = "INSERT INTO stock (item_id, quantity) VALUES (?, ?)";
        $stmt_stock = mysqli_prepare($conn, $sql_stock);
        mysqli_stmt_bind_param($stmt_stock, "ii", $item_id, $quantity);
        mysqli_stmt_execute($stmt_stock);
        mysqli_stmt_close($stmt_stock);

        // Commit both inserts
        mysqli_commit($conn);

        // Redirect to storefront to see the new product
        header("Location: ../index.php");
        exit;

    } catch (Exception $e) {
        // Rollback if anything fails
        mysqli_rollback($conn);
        die("Error saving product: " . $e->getMessage());
    }
} else {
    // If accessed directly without POST, send back to create form
    header("Location: create.php");
    exit;
}
?>
