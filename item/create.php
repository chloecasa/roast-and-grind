<?php
// item/create.php - FR-8: Add Product Form
require_once "../includes/config.php";
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
                <!-- multipart/form-data is required for image uploads -->
                <form action="store.php" method="POST" enctype="multipart/form-data">
                    
                    <!-- Product Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name</label>
                        <input type="text" name="item_name" class="form-control" placeholder="e.g., Sagada Dark Roast (250g)" required>
                    </div>

                    <!-- Category & Origin -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="" disabled selected>Select category...</option>
                                <option value="Coffee Beans">Coffee Beans</option>
                                <option value="Drip Bags">Drip Bags</option>
                                <option value="Brewing Gear">Brewing Gear</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Origin</label>
                            <input type="text" name="origin" class="form-control" placeholder="e.g., Mountain Province, PH (or N/A for gear)" required>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description & Tasting Notes</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Describe flavor notes, roast profile, or equipment specs..." required></textarea>
                    </div>

                    <!-- Prices & Stock -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Cost Price (₱)</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Selling Price (₱)</label>
                            <input type="number" step="0.01" name="sell_price" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Initial Stock Qty</label>
                            <input type="number" name="quantity" class="form-control" min="0" placeholder="0" required>
                        </div>
                    </div>

                  <!-- 1. Primary Card Image -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Primary Card Image (JPG/PNG)</label>
                        <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png" required>
                        <small class="text-muted">This is the main image displayed on the storefront card.</small>
                    </div>

                    <!-- 2. Multiple Gallery Images for Right Drawer -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Additional Gallery Photos (Multiple allowed)</label>
                        <input type="file" name="gallery_images[]" class="form-control" accept=".jpg,.jpeg,.png" multiple>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple images for the right-side carousel.</small>
                    </div>

                    <!-- Submit Button -->
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
