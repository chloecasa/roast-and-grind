<?php
// item/index.php - admin product list: the entry point to FR-8 add, FR-9 edit, FR-10 delete,
//                  FR-13 stock badges and FR-14 restocking (via the edit page)
require_once "../includes/admin_check.php"; // FR-3: admin only, runs before any output
require_once "../includes/config.php";

$sql = "SELECT i.item_id, i.item_name, i.category, i.origin, i.cost_price, i.sell_price, i.img_path,
               COALESCE(s.quantity, 0) AS quantity
        FROM item i
        LEFT JOIN stock s ON s.item_id = i.item_id
        ORDER BY i.item_id DESC";
$result    = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);

// FR-13: stock status badge (green / orange when 5 or below / red at 0)
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

<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-mug-hot me-2"></i>Products (<?= $itemCount; ?>)</h5>
                <div>
                    <a href="../shop.php" class="btn btn-sm btn-outline-light">Back to Shop</a>
                    <a href="create.php" class="btn btn-sm btn-light fw-bold"><i class="fa-solid fa-plus me-1"></i>Add Product</a>
                </div>
            </div>

            <div class="card-body p-4">
                <?php include('../includes/alert.php'); ?>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:80px;">Image</th>
                                <th>Product</th>
                                <th>Cost</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th style="width:110px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($itemCount === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No products yet. Add your first one.</td></tr>
                            <?php endif; ?>

                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                $imgSrc = "images/" . $row['img_path'];
                                if (empty($row['img_path']) || !file_exists(__DIR__ . "/" . $imgSrc)) {
                                    $imgSrc = "https://placehold.co/120x120/EDEAE1/743014?text=" . urlencode($row['item_name']);
                                }
                                ?>
                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars($imgSrc); ?>" alt="" width="60" height="60"
                                             style="object-fit:cover;border-radius:6px;">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['item_name']); ?></div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($row['category']); ?> &middot; <?= htmlspecialchars($row['origin']); ?>
                                        </small>
                                    </td>
                                    <td>₱<?= number_format($row['cost_price'], 2); ?></td>
                                    <td>₱<?= number_format($row['sell_price'], 2); ?></td>
                                    <td>
                                        <?= (int) $row['quantity']; ?>
                                        <?= stockBadge((int) $row['quantity']); ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit.php?id=<?= $row['item_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit / restock">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $row['item_id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                                           onclick="return confirm('Delete this product and its inventory record? This cannot be undone.');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>