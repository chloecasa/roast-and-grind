<?php
// item/create.php - MP5: No HTML5 Validation (Add Product Form)
require_once "../includes/admin_check.php"; 
require_once "../includes/config.php";

// Fallback if PRODUCT_CATEGORIES isn't defined globally
if (!defined('PRODUCT_CATEGORIES')) {
    define('PRODUCT_CATEGORIES', ['Coffee Beans', 'Drip Bags', 'Brewing Gear']);
}

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

function oldVal($old, $key)
{
    return htmlspecialchars($old[$key] ?? '');
}

require_once "../includes/header.php";
?>

<div class="admin-form-wrapper">
    <h1 class="admin-form-title">Add New Product</h1>
    <p class="admin-form-subtitle">Create a new coffee bean, drip bag package, or brewing accessory</p>

    <div class="admin-form-card">
        <?php include('../includes/alert.php'); ?>

        <form action="store.php" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="admin-form-label">Product Name</label>
                <!-- MP5: Removed required and maxlength -->
                <input type="text" name="item_name" class="form-control admin-form-input" 
                       placeholder="e.g., Sagada Dark Roast (250g)" 
                       value="<?= oldVal($old, 'item_name'); ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="admin-form-label">Category</label>
                    <!-- MP5: Removed required -->
                    <select name="category" class="form-select admin-form-select">
                        <option value="" disabled <?= empty($old['category']) ? 'selected' : ''; ?>>Select category...</option>
                        <?php foreach (PRODUCT_CATEGORIES as $cat): ?>
                            <option value="<?= $cat; ?>" <?= (($old['category'] ?? '') === $cat) ? 'selected' : ''; ?>><?= $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="admin-form-label">Origin / Region</label>
                    <!-- MP5: Removed required and maxlength -->
                    <input type="text" name="origin" class="form-control admin-form-input" 
                           placeholder="e.g., Mountain Province, PH (or N/A)" 
                           value="<?= oldVal($old, 'origin'); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="admin-form-label">Description & Tasting Notes</label>
                <!-- MP5: Removed required and maxlength -->
                <textarea name="description" rows="3" class="form-control admin-form-textarea" 
                          placeholder="Describe flavor notes, roast profile, or equipment specs..."><?= oldVal($old, 'description'); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="admin-form-label">Cost Price (₱)</label>
                    <!-- MP5: Changed to type="text", removed min/max/step/required -->
                    <input type="text" name="cost_price" class="form-control admin-form-input" 
                           placeholder="0.00" value="<?= oldVal($old, 'cost_price'); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="admin-form-label">Selling Price (₱)</label>
                    <!-- MP5: Changed to type="text", removed min/max/step/required -->
                    <input type="text" name="sell_price" class="form-control admin-form-input" 
                           placeholder="0.00" value="<?= oldVal($old, 'sell_price'); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="admin-form-label">Initial Stock Qty</label>
                    <!-- MP5: Changed to type="text", removed min/max/step/required -->
                    <input type="text" name="quantity" class="form-control admin-form-input" 
                           placeholder="0" value="<?= oldVal($old, 'quantity'); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="admin-form-label">Primary Card Image (JPG/PNG)</label>
                <!-- MP5: Removed required -->
                <input type="file" name="product_image" class="form-control admin-form-input" accept=".jpg,.jpeg,.png">
                <small class="text-muted" style="font-family: 'Alinore';">Main storefront image. Up to 5 MB.</small>
            </div>

            <div class="mb-4">
                <label class="admin-form-label">Additional Gallery Photos (Multiple allowed)</label>
                <input type="file" name="gallery_images[]" class="form-control admin-form-input" accept=".jpg,.jpeg,.png" multiple>
            </div>

            <button type="submit" class="admin-form-submit-btn">
                <i class="fa-solid fa-save me-2"></i> Save Product
            </button>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
