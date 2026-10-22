<?php
// item/edit.php - FR-9 & FR-14: Edit Product & Restock Form
require_once "../includes/admin_check.php"; // FR-3: admin only, runs before any output
require_once "../includes/config.php";
require_once "../includes/product_validation.php";
require_once "../includes/header.php";


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$item_id = intval($_GET['id']);


// LEFT JOIN so a product without an inventory record can still be opened and restocked
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

// values typed before a failed save are put back in the form (see item/update.php)
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
if ((int) ($old['item_id'] ?? 0) !== $item_id) {
    $old = [];
}
function fieldVal($old, $key, $product)
{
    return htmlspecialchars((string) ($old[$key] ?? $product[$key]));
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Product & Restock</h5>
                <a href="../index.php" class="btn btn-sm btn-outline-light">Back to Shop</a>
            </div>
            <div class="card-body p-4">
                <?php include('../includes/alert.php'); ?>
                <form action="update.php" method="POST" enctype="multipart/form-data">
               
                    <input type="hidden" name="item_id" value="<?= $product['item_id']; ?>">
                    
                
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name</label>
                        <input type="text" name="item_name" class="form-control" maxlength="150" value="<?= fieldVal($old, 'item_name', $product); ?>" required>
                    </div>

                  
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select" required>
                                <?php $currentCat = $old['category'] ?? $product['category']; ?>
                                <?php foreach (PRODUCT_CATEGORIES as $cat): ?>
                                    <option value="<?= $cat; ?>" <?= $currentCat === $cat ? 'selected' : ''; ?>><?= $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Origin</label>
                            <input type="text" name="origin" class="form-control" maxlength="100" value="<?= fieldVal($old, 'origin', $product); ?>" required>
                        </div>
                    </div>

            
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description & Tasting Notes</label>
                        <textarea name="description" rows="3" class="form-control" maxlength="2000" required><?= fieldVal($old, 'description', $product); ?></textarea>
                    </div>

                   
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Cost Price (₱)</label>
                            <input type="number" step="0.01" min="0" max="99999999.99" name="cost_price" class="form-control" value="<?= fieldVal($old, 'cost_price', $product); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Selling Price (₱)</label>
                            <input type="number" step="0.01" min="0.01" max="99999999.99" name="sell_price" class="form-control" value="<?= fieldVal($old, 'sell_price', $product); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <!-- FR-14 Restocking Input -->
                            <label class="form-label fw-bold text-primary">Available Stock (Restock)</label>
                            <input type="number" step="1" name="quantity" class="form-control border-primary" min="0" max="9999999" value="<?= fieldVal($old, 'quantity', $product); ?>" required>
                        </div>
                    </div>

                 
                    <div class="mb-4">
                        <label class="form-label fw-bold">Replace Product Image (Optional)</label>
                        <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png">
                        <small class="text-muted">JPG/PNG, up to 5 MB. Current image: <code><?= htmlspecialchars($product['img_path']); ?></code></small>
                    </div>

                   <!-- Action Buttons (Update & Delete) -->
<div class="d-flex justify-content-between align-items-center mt-4">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fa-solid fa-check me-2"></i>Update Product & Stock
    </button>
    
    
    <a href="delete.php?id=<?= $product['item_id']; ?>" 
       class="btn btn-outline-danger btn-lg" 
       onclick="return confirm('Are you sure you want to delete \'<?= addslashes(htmlspecialchars($product['item_name'])); ?>\'?');">
        <i class="fa-solid fa-trash me-2"></i>Delete Product
    </a>
</div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>