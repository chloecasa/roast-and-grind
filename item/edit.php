<?php
// item/edit.php - MP5: No HTML5 Validation (Edit Product & Restock Form)
require_once "../includes/admin_check.php";
require_once "../includes/config.php";

// Fallback if PRODUCT_CATEGORIES isn't defined globally
if (!defined('PRODUCT_CATEGORIES')) {
    define('PRODUCT_CATEGORIES', ['Coffee Beans', 'Drip Bags', 'Brewing Gear']);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$item_id = intval($_GET['id']);

$sql = "SELECT i.*, COALESCE(s.quantity, 0) AS quantity 
        FROM item i 
        LEFT JOIN stock s USING (item_id) 
        WHERE i.item_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Product not found.");
}

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
if ((int) ($old['item_id'] ?? 0) !== $item_id) {
    $old = [];
}

function fieldVal($old, $key, $product)
{
    return htmlspecialchars((string) ($old[$key] ?? $product[$key]));
}

require_once "../includes/header.php";
?>

<div class="admin-form-wrapper">
    <h1 class="admin-form-title">Edit Product & Stock</h1>
    <p class="admin-form-subtitle">Update item details, adjust pricing, or perform inventory restocking</p>

    <div class="admin-form-card">
        <?php include('../includes/alert.php'); ?>

        <form action="update.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="item_id" value="<?= $product['item_id']; ?>">
            
            <div class="mb-3">
                <label class="admin-form-label">Product Name</label>
                <!-- MP5: Removed required and maxlength -->
                <input type="text" name="item_name" class="form-control admin-form-input" 
                       value="<?= fieldVal($old, 'item_name', $product); ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="admin-form-label">Category</label>
                    <!-- MP5: Removed required -->
                    <select name="category" class="form-select admin-form-select">
                        <option value="" disabled>Select category...</option>
                        <?php $currentCat = $old['category'] ?? $product['category']; ?>
                        <?php foreach (PRODUCT_CATEGORIES as $cat): ?>
                            <option value="<?= $cat; ?>" <?= $currentCat === $cat ? 'selected' : ''; ?>><?= $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="admin-form-label">Origin / Region</label>
                    <!-- MP5: Removed required and maxlength -->
                    <input type="text" name="origin" class="form-control admin-form-input" 
                           value="<?= fieldVal($old, 'origin', $product); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="admin-form-label">Description & Tasting Notes</label>
                <!-- MP5: Removed required and maxlength -->
                <textarea name="description" rows="3" class="form-control admin-form-textarea"><?= fieldVal($old, 'description', $product); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="admin-form-label">Cost Price (₱)</label>
                    <!-- MP5: Changed to type="text", removed min/max/step/required -->
                    <input type="text" name="cost_price" class="form-control admin-form-input" 
                           value="<?= fieldVal($old, 'cost_price', $product); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="admin-form-label">Selling Price (₱)</label>
                    <!-- MP5: Changed to type="text", removed min/max/step/required -->
                    <input type="text" name="sell_price" class="form-control admin-form-input" 
                           value="<?= fieldVal($old, 'sell_price', $product); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="admin-form-label" style="color: var(--arch-brown);">Available Stock (Restock)</label>
                    <!-- MP5: Changed to type="text", removed min/max/step/required -->
                    <input type="text" name="quantity" class="form-control admin-form-input restock-highlight" 
                           value="<?= fieldVal($old, 'quantity', $product); ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="admin-form-label">Replace Product Image (Optional)</label>
                <input type="file" name="product_image" class="form-control admin-form-input" accept=".jpg,.jpeg,.png">
                <small class="text-muted" style="font-family: 'Alinore';">Current image: <code><?= htmlspecialchars($product['img_path']); ?></code></small>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <button type="submit" class="admin-edit-submit-btn">
                    <i class="fa-solid fa-check me-2"></i> Update Product & Stock
                </button>
                
                <a href="delete.php?id=<?= $product['item_id']; ?>" 
                   class="admin-edit-delete-btn" 
                   onclick="return confirm('Are you sure you want to delete \'<?= addslashes(htmlspecialchars($product['item_name'])); ?>\'?');">
                    <i class="fa-solid fa-trash me-2"></i> Delete Product
                </a>
            </div>

        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
