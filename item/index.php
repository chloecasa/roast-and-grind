<?php
// item/index.php - Admin Product List: Entry point for FR-8, FR-9, FR-10, FR-13, FR-14
require_once "../includes/admin_check.php";
require_once "../includes/config.php";
require_once "../includes/csrf.php";

$sql = "SELECT i.item_id, i.item_name, i.category, i.origin, i.cost_price, i.sell_price, i.img_path,
               COALESCE(s.quantity, 0) AS quantity
        FROM item i
        LEFT JOIN stock s ON s.item_id = i.item_id
        ORDER BY i.item_id DESC";
$result    = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);


function stockBadge($qty)
{
    if ($qty <= 0) {
        return '<span class="badge text-bg-danger">Out of Stock</span>';
    }
    if ($qty <= 5) {
        return '<span class="badge" style="background-color:#fd7e14;color:#fff;">Low Stock</span>';
    }
    return '<span class="badge text-bg-success">In Stock</span>';
}

require_once "../includes/header.php";
?>

<div class="admin-items-wrapper">
    <h1 class="admin-items-title">Inventory Management</h1>
    <p class="admin-items-subtitle">Manage product catalog listings, pricing, and live inventory stock levels</p>

    <?php require_once "../includes/alert.php"; ?>

  
    <div class="admin-items-header-bar">
        <h3 class="admin-items-count">Products Catalog (<?= $itemCount; ?>)</h3>
        <div class="d-flex gap-3">
            <a href="../shop.php" class="dashboard-btn-outline" style="padding: 10px 24px; font-size: 1.15rem;">
                &larr; View Storefront
            </a>
            <a href="create.php" class="admin-add-product-btn">
                <i class="fa-solid fa-plus me-1"></i> Add New Product
            </a>
        </div>
    </div>

    
    <div class="admin-items-box">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="width:90px;" class="text-center">Image</th>
                    <th class="text-start">Product Details</th>
                    <th class="text-center">Cost Price</th>
                    <th class="text-center">Selling Price</th>
                    <th class="text-center">Stock Level</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($itemCount === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4" style="color: #78716C; font-family: 'Alinore'; font-size: 1.2rem;">
                            No products found in catalog. Click "Add New Product" to get started.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($row = mysqli_fetch_assoc($result)): 
                    $imgSrc = "images/" . $row['img_path'];
                    if (empty($row['img_path']) || !file_exists(__DIR__ . "/" . $imgSrc)) {
                        $imgSrc = "https://placehold.co/120x120/EDEAE1/743014?text=" . urlencode($row['item_name']);
                    }
                ?>
                    <tr>
                        
                        <td class="text-center">
                            <img src="<?= htmlspecialchars($imgSrc); ?>" alt="" width="65" height="65"
                                 style="object-fit:cover; border-radius:10px; border:1px solid rgba(116,48,20,0.15);">
                        </td>

                        
                        <td>
                            <div class="admin-item-title"><?= htmlspecialchars($row['item_name']); ?></div>
                            <span class="admin-item-meta">
                                <?= htmlspecialchars($row['category']); ?> &bull; <?= htmlspecialchars($row['origin']); ?>
                            </span>
                        </td>

                        
                        <td class="text-center" style="font-family: 'Alinore';">₱<?= number_format($row['cost_price'], 2); ?></td>

                        
                        <td class="text-center fw-bold" style="font-family: 'Alinore'; color: #4D6443;">₱<?= number_format($row['sell_price'], 2); ?></td>

                        
                        <td class="text-center" style="font-family: 'Alinore'; font-size: 1.2rem;">
                            <span class="fw-bold"><?= (int) $row['quantity']; ?></span>
                            <?= stockBadge((int) $row['quantity']); ?>
                        </td>

                        
                        <td class="text-end">
                            <a href="edit.php?id=<?= $row['item_id']; ?>" class="admin-action-btn" title="Edit / Restock">
                                <i class="fa-regular fa-pen-to-square"></i> Edit
                            </a>
                            <form action="delete.php" method="POST" class="d-inline">
                                <input type="hidden" name="item_id" value="<?= (int) $row['item_id']; ?>">
                                <?= csrf_field(); ?>
                                <button type="submit" class="admin-action-btn delete-btn" title="Delete Product"
                                        onclick="return confirm('Delete this product and its inventory record? This cannot be undone.');">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>


<?php require_once "../includes/footer.php"; ?>
<?php require_once "../includes/header.php";  ?>
