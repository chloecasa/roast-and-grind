<?php
// item/create.php - FR-8: Add Product Form
require_once "../includes/admin_check.php"; // FR-3: admin only, runs before any output
require_once "../includes/config.php";
require_once "../includes/product_validation.php";

// values typed before a failed save are put back in the form (see item/store.php)
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
function oldVal($old, $key)
{
    return htmlspecialchars($old[$key] ?? '');
}

require_once "../includes/header.php";
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-plus me-2"></i>Add New Coffee / Gear</h5>
                <a href="../index.php" class="btn btn-sm btn-outline-light">Back to Shop</a>
            </div>
            <div class="card-body p-4">
                <?php include('../includes/alert.php'); ?>

                <form action="store.php" method="POST" enctype="multipart/form-data">
                    
                   
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name</label>
                        <input type="text" name="item_name" class="form-control" placeholder="e.g., Sagada Dark Roast (250g)" maxlength="150" value="<?= oldVal($old, 'item_name'); ?>" required>
                    </div>

                  
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="" disabled <?= empty($old['category']) ? 'selected' : ''; ?>>Select category...</option>
                                <?php foreach (PRODUCT_CATEGORIES as $cat): ?>
                                    <option value="<?= $cat; ?>" <?= (($old['category'] ?? '') === $cat) ? 'selected' : ''; ?>><?= $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Origin</label>
                            <input type="text" name="origin" class="form-control" placeholder="e.g., Mountain Province, PH (or N/A for gear)" maxlength="100" value="<?= oldVal($old, 'origin'); ?>" required>
                        </div>
                    </div>

               
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description & Tasting Notes</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Describe flavor notes, roast profile, or equipment specs..." maxlength="2000" required><?= oldVal($old, 'description'); ?></textarea>
                    </div>

               
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Cost Price (₱)</label>
                            <input type="number" step="0.01" min="0" max="99999999.99" name="cost_price" class="form-control" placeholder="0.00" value="<?= oldVal($old, 'cost_price'); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Selling Price (₱)</label>
                            <input type="number" step="0.01" min="0.01" max="99999999.99" name="sell_price" class="form-control" placeholder="0.00" value="<?= oldVal($old, 'sell_price'); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Initial Stock Qty</label>
                            <input type="number" step="1" name="quantity" class="form-control" min="0" max="9999999" placeholder="0" value="<?= oldVal($old, 'quantity'); ?>" required>
                        </div>
                    </div>

              
                    <div class="mb-3">
                        <label class="form-label fw-bold">Primary Card Image (JPG/PNG)</label>
                        <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png" required>
                        <small class="text-muted">This is the main image displayed on the storefront card. JPG/PNG, up to 5 MB. (Please choose the image files again after an error.)</small>
                    </div>

              
                    <div class="mb-4">
                        <label class="form-label fw-bold">Additional Gallery Photos (Multiple allowed)</label>
                        <input type="file" name="gallery_images[]" class="form-control" accept=".jpg,.jpeg,.png" multiple>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple images for the right-side carousel.</small>
                    </div>

         
                    <div class="d-grid">
                        <button type="submit" class="btn btn-dark btn-lg">
                            <i class="fa-solid fa-save me-2"></i>Save Product
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>