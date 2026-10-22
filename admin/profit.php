<?php
// admin/profit.php - FR-21: profit reporting (admin only)
include('../includes/admin_check.php');
include('../includes/config.php');

function peso($n)
{
    return ($n < 0 ? '-' : '') . '₱' . number_format(abs($n), 2);
}

// FR-21: profit per product = selling price - cost price (per unit, using the current prices)
$products = mysqli_query($conn,
    "SELECT item_name, category, cost_price, sell_price, (sell_price - cost_price) AS profit
     FROM item
     ORDER BY profit DESC, item_name ASC");

// FR-21: profit per order = sum of (selling price - cost price) x quantity for each order line.
// The price and cost saved on the order line are used, so later price changes don't rewrite old orders.
$orders = mysqli_query($conn,
    "SELECT o.order_id, o.order_date, o.status, o.total_amount,
            COALESCE(SUM((oi.unit_price - oi.unit_cost) * oi.quantity), 0) AS profit
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.order_id
     GROUP BY o.order_id, o.order_date, o.status, o.total_amount
     ORDER BY o.order_date DESC, o.order_id DESC");

$rows = [];
$total_profit = 0;
while ($o = mysqli_fetch_assoc($orders)) {
    $rows[] = $o;
    if ($o['status'] !== 'Canceled') {
        $total_profit += (float) $o['profit'];   // canceled orders are not counted
    }
}

include('../includes/header.php');
?>

<h1 align="center" class="mb-4">Profit Report</h1>
<?php include('../includes/alert.php'); ?>

<div class="cart-view-table-back">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body text-center">
            <div class="text-muted">Total Profit (all non-canceled orders)</div>
            <div class="fs-2 fw-bold <?= $total_profit < 0 ? 'text-danger' : ''; ?>"><?= peso($total_profit); ?></div>
        </div>
    </div>

    <h5 class="fw-bold">Profit per Product</h5>
    <table class="table table-striped table-bordered align-middle mb-4">
        <thead>
            <tr><th>Product</th><th>Category</th><th>Cost Price</th><th>Selling Price</th><th>Profit per Unit</th></tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($products) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No products yet.</td></tr>
            <?php endif; ?>
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
                <tr>
                    <td><?= htmlspecialchars($p['item_name']); ?></td>
                    <td><?= htmlspecialchars($p['category']); ?></td>
                    <td><?= peso($p['cost_price']); ?></td>
                    <td><?= peso($p['sell_price']); ?></td>
                    <td class="fw-bold <?= $p['profit'] < 0 ? 'text-danger' : ''; ?>"><?= peso($p['profit']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h5 class="fw-bold">Profit per Order</h5>
    <table class="table table-striped table-bordered align-middle">
        <thead>
            <tr><th>Order ID</th><th>Date Ordered</th><th>Status</th><th>Order Total</th><th>Profit</th></tr>
        </thead>
        <tbody>
            <?php if (count($rows) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No orders yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $o): ?>
                <?php $canceled = ($o['status'] === 'Canceled'); ?>
                <tr class="<?= $canceled ? 'text-muted' : ''; ?>">
                    <td><a href="order_view.php?id=<?= $o['order_id']; ?>">#<?= $o['order_id']; ?></a></td>
                    <td><?= date('F j, Y g:i A', strtotime($o['order_date'])); ?></td>
                    <td><?= statusBadge($o['status']); ?></td>
                    <td><?= peso($o['total_amount']); ?></td>
                    <td class="<?= (!$canceled && $o['profit'] < 0) ? 'text-danger fw-bold' : ''; ?>">
                        <?= peso($o['profit']); ?>
                        <?php if ($canceled): ?><small>(not counted)</small><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-outline-dark">Back to Dashboard</a>
</div>

<?php include('../includes/footer.php'); ?>