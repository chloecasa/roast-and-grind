<?php
// item/edit.php - FR-9 & FR-14: Edit Product & Restock Form
require_once "../includes/config.php";
require_once "../includes/header.php";

// 1. Validate that an item ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$item_id = intval($_GET['id']);

// 2. Fetch current product and stock details
$sql = "SELECT i.*, s.quantity 
        FROM item i 
        INNER JOIN stock s USING (item_id) 
        WHERE i.item_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Product not found.");
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
                <form action="update.php" method="POST" enctype="multipart/form-data">
                    <!-- Hidden field to pass item_id -->
                    <input type="hidden" name="item_id" value="<?= $product['item_id']; ?>">
                    
                    <!-- Product Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name</label>
                        <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($product['item_name']); ?>" required>
                    </div>

                    <!-- Category & Origin -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="Coffee Beans" <?= $product['category'] === 'Coffee Beans' ? 'selected' : ''; ?>>Coffee Beans</option>
                                <option value="Drip Bags" <?= $product['category'] === 'Drip Bags' ? 'selected' : ''; ?>>Drip Bags</option>
                                <option value="Brewing Gear" <?= $product['category'] === 'Brewing Gear' ? 'selected' : ''; ?>>Brewing Gear</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Origin</label>
                            <input type="text" name="origin" class="form-control" value="<?= htmlspecialchars($product['origin']); ?>" required>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description & Tasting Notes</label>
                        <textarea name="description" rows="3" class="form-control" required><?= htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <!-- Prices & Stock Restocking -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Cost Price (₱)</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= $product['cost_price']; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Selling Price (₱)</label>
                            <input type="number" step="0.01" name="sell_price" class="form-control" value="<?= $product['sell_price']; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <!-- FR-14 Restocking Input -->
                            <label class="form-label fw-bold text-primary">Available Stock (Restock)</label>
                            <input type="number" name="quantity" class="form-control border-primary" min="0" value="<?= $product['quantity']; ?>" required>
                        </div>
                    </div>

                    <!-- Product Image -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Replace Product Image (Optional)</label>
                        <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png">
                        <small class="text-muted">Current image: <code><?= htmlspecialchars($product['img_path']); ?></code></small>
                    </div>

                   <!-- Action Buttons (Update & Delete) -->
<div class="d-flex justify-content-between align-items-center mt-4">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fa-solid fa-check me-2"></i>Update Product & Stock
    </button>
    
    <!-- Delete Button right inside the Edit page -->
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
