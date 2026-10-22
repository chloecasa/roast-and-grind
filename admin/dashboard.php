<?php
// admin/dashboard.php - FR-20: sales dashboard (admin only)
include('../includes/admin_check.php');
include('../includes/config.php');

function peso($n)
{
    return ($n < 0 ? '-' : '') . '₱' . number_format(abs($n), 2);
}

// orders per status (every status is listed, even when its count is 0)
$per_status = array_fill_keys($order_statuses, 0);
$res = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM orders GROUP BY status");
while ($r = mysqli_fetch_assoc($res)) {
    $per_status[$r['status']] = (int) $r['total'];
}
$total_orders = array_sum($per_status);

// FR-20: Canceled orders are left out of the sales revenue
$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(total_amount), 0) AS revenue FROM orders WHERE status <> 'Canceled'"));
$total_revenue = (float) $row['revenue'];

// FR-21: total profit across non-canceled orders (shown here too; the detail is in profit.php)
$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM((oi.unit_price - oi.unit_cost) * oi.quantity), 0) AS profit
     FROM order_items oi
     JOIN orders o ON o.order_id = oi.order_id
     WHERE o.status <> 'Canceled'"));
$total_profit = (float) $row['profit'];

// FR-20: products at Low Stock (5 or below) or Out of Stock (a product with no stock row counts as 0)
$low_stock = mysqli_query($conn,
    "SELECT i.item_id, i.item_name, i.category, COALESCE(s.quantity, 0) AS quantity
     FROM item i
     LEFT JOIN stock s ON s.item_id = i.item_id
     WHERE COALESCE(s.quantity, 0) <= 5
     ORDER BY quantity ASC, i.item_name ASC");

include('../includes/header.php');
?>

<h1 align="center" class="mb-4">Sales Dashboard</h1>
<?php include('../includes/alert.php'); ?>

<div class="cart-view-table-back">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted">Total Orders</div>
                    <div class="fs-2 fw-bold"><?= $total_orders; ?></div>
                    <small class="text-muted">all orders, including canceled</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted">Total Sales Revenue</div>
                    <div class="fs-2 fw-bold"><?= peso($total_revenue); ?></div>
                    <small class="text-muted">canceled orders excluded</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted">Total Profit</div>
                    <div class="fs-2 fw-bold <?= $total_profit < 0 ? 'text-danger' : ''; ?>"><?= peso($total_profit); ?></div>
                    <a href="profit.php" class="small">View profit report</a>
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold">Orders per Status</h5>
    <div class="row g-3 mb-4">
        <?php foreach ($per_status as $status => $count): ?>
            <div class="col">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <?= statusBadge($status); ?>
                        <div class="fs-3 fw-bold mt-2"><?= $count; ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h5 class="fw-bold">Low Stock and Out of Stock Products</h5>
    <table class="table table-striped table-bordered align-middle">
        <thead>
            <tr><th>Product</th><th>Category</th><th>Stock</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($low_stock) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">All products are well stocked.</td></tr>
            <?php endif; ?>
            <?php while ($p = mysqli_fetch_assoc($low_stock)): ?>
                <tr>
                    <td><?= htmlspecialchars($p['item_name']); ?></td>
                    <td><?= htmlspecialchars($p['category']); ?></td>
                    <td><?= (int) $p['quantity']; ?></td>
                    <td>
                        <?php if ((int) $p['quantity'] === 0): ?>
                            <span class="badge text-bg-danger">Out of Stock</span>
                        <?php else: ?>
                            <span class="badge" style="background-color:#fd7e14;color:#fff;">Low Stock</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="../item/edit.php?id=<?= $p['item_id']; ?>" class="btn btn-sm btn-outline-primary">Restock</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="orders.php" class="btn btn-outline-dark">Manage Orders</a>
    <a href="profit.php" class="btn btn-dark float-end">Profit Report</a>
</div>

<?php include('../includes/footer.php'); ?>