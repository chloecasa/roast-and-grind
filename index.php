<?php
// index.php - FR-7, FR-11, FR-12, FR-13: Storefront with Search & Category Filter
require_once "includes/config.php";
require_once "includes/header.php";

$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT 
            i.item_id,
            i.item_name,
            i.description,
            i.origin,
            i.category,
            i.sell_price,
            i.img_path,
            s.quantity AS qty
        FROM item i
        INNER JOIN stock s USING (item_id)
        WHERE 1=1";

$params = [];
$types  = "";

if (!empty($search)) {
    $sql .= " AND (i.item_name LIKE ? OR i.description LIKE ? OR i.origin LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types   .= "sss";
}

if (!empty($category) && $category !== "All") {
    $sql .= " AND i.category = ?";
    $params[] = $category;
    $types   .= "s";
}

$sql .= " ORDER BY i.item_id ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<?php include("includes/alert.php"); ?>

<div class="row mb-4">
    <div class="col-12 text-center">
        <h2 class="fw-bold">Artisan Coffee & Brewing Gear</h2>
        <p class="text-muted">Freshly roasted single-origin beans and premium drip equipment.</p>
    </div>
</div>

<!-- Search and Category Filter Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Search by coffee name, origin, or notes..." 
                           value="<?= htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="col-md-4">
                <select name="category" class="form-select">
                    <option value="All" <?= ($category === 'All' || empty($category)) ? 'selected' : ''; ?>>All Categories</option>
                    <option value="Coffee Beans" <?= ($category === 'Coffee Beans') ? 'selected' : ''; ?>>Coffee Beans</option>
                    <option value="Drip Bags" <?= ($category === 'Drip Bags') ? 'selected' : ''; ?>>Drip Bags</option>
                    <option value="Brewing Gear" <?= ($category === 'Brewing Gear') ? 'selected' : ''; ?>>Brewing Gear</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark flex-grow-1">Filter</button>
                <?php if (!empty($search) || (!empty($category) && $category !== "All")): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Reset Filters">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Product Grid -->
<div class="row">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($item = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <span class="badge bg-secondary"><?= htmlspecialchars($item['category']); ?></span>
                        <?php if ($item['origin'] !== 'N/A'): ?>
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($item['origin']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-dark mt-2"><?= htmlspecialchars($item['item_name']); ?></h5>
                        <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($item['description']); ?></p>

                        <div class="my-2">
                            <!-- FR-13 Stock Badges -->
                            <?php if ($item['qty'] > 5): ?>
                                <span class="badge bg-success">In Stock (<?= $item['qty']; ?>)</span>
                            <?php elseif ($item['qty'] > 0): ?>
                                <span class="badge bg-warning text-dark">Low Stock (<?= $item['qty']; ?> left)</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="fs-5 fw-bold text-dark">₱<?= number_format($item['sell_price'], 2); ?></span>

                            <?php if ($item['qty'] > 0): ?>
                                    <form method="POST" action="cart_update.php" class="d-flex gap-1">
                                    <input type="number" name="item_qty" class="form-control form-control-sm" style="width:65px"
                                           value="1" min="1" max="<?= $item['qty']; ?>" />
                                    <input type="hidden" name="item_id" value="<?= $item['item_id']; ?>" />
                                    <input type="hidden" name="type" value="add" />
                                    <input type="hidden" name="redirect" value="index.php?<?= htmlspecialchars($_SERVER['QUERY_STRING']); ?>" />
                                    <button class="btn btn-sm btn-dark" type="submit">
                                        <i class="fa-solid fa-cart-plus me-1"></i> Add
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Unavailable</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <i class="fa-solid fa-mug-saucer fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No products found matching your search.</h5>
            <a href="index.php" class="btn btn-outline-dark mt-2">View All Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
