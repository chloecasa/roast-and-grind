<?php
// admin/dashboard.php - FR-20: Sales Dashboard (Admin Only)
require_once "../includes/admin_check.php";
require_once "../includes/config.php";

function peso($n)
{
    return ($n < 0 ? '-' : '') . '₱' . number_format(abs($n), 2);
}

// Orders per status count
$per_status = array_fill_keys($order_statuses, 0);
$res = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM orders GROUP BY status");
while ($r = mysqli_fetch_assoc($res)) {
    $per_status[$r['status']] = (int) $r['total'];
}
$total_orders = array_sum($per_status);

// FR-20: Total revenue excluding Canceled orders
$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(total_amount), 0) AS revenue FROM orders WHERE status <> 'Canceled'"));
$total_revenue = (float) $row['revenue'];

// FR-21: Total profit across non-canceled orders
$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM((oi.unit_price - oi.unit_cost) * oi.quantity), 0) AS profit
     FROM order_items oi
     JOIN orders o ON o.order_id = oi.order_id
     WHERE o.status <> 'Canceled'"));
$total_profit = (float) $row['profit'];

// FR-20: Products at Low Stock (5 or below) or Out of Stock
$low_stock = mysqli_query($conn,
    "SELECT i.item_id, i.item_name, i.category, COALESCE(s.quantity, 0) AS quantity
     FROM item i
     LEFT JOIN stock s ON s.item_id = i.item_id
     WHERE COALESCE(s.quantity, 0) <= 5
     ORDER BY quantity ASC, i.item_name ASC");

require_once "../includes/header.php";
?>

<div class="dashboard-wrapper">
    <h1 class="dashboard-title">Sales Dashboard</h1>
    <p class="dashboard-subtitle">Real-time overview of orders, revenue, profit, and inventory alerts</p>

    <?php require_once "../includes/alert.php"; ?>

    <!-- 1. Top KPI Summary Cards -->
    <div class="dashboard-kpi-grid">
        <!-- Card 1: Total Orders -->
        <div class="dashboard-kpi-card">
            <div>
                <div class="kpi-label">Total Orders</div>
                <div class="kpi-number"><?= $total_orders; ?></div>
            </div>
            <div class="kpi-hint">All customer orders recorded</div>
        </div>

        <!-- Card 2: Sales Revenue -->
        <div class="dashboard-kpi-card">
            <div>
                <div class="kpi-label">Total Sales Revenue</div>
                <div class="kpi-number kpi-revenue"><?= peso($total_revenue); ?></div>
            </div>
            <div class="kpi-hint">Excludes canceled orders</div>
        </div>

        <!-- Card 3: Net Profit -->
        <div class="dashboard-kpi-card">
            <div>
                <div class="kpi-label">Total Estimated Profit</div>
                <div class="kpi-number kpi-profit <?= $total_profit < 0 ? 'text-danger' : ''; ?>">
                    <?= peso($total_profit); ?>
                </div>
            </div>
            <div>
                <a href="profit.php" class="kpi-link">View Detailed Profit Report &rarr;</a>
            </div>
        </div>
    </div>

    <!-- 2. Order Status Pipeline -->
    <h3 class="dashboard-section-header">Order Pipeline</h3>
    <div class="dashboard-status-grid">
        <?php foreach ($per_status as $status => $count): ?>
            <div class="dashboard-status-card">
                <div><?= statusBadge($status); ?></div>
                <div class="dashboard-status-count"><?= $count; ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 3. Low Stock / Out of Stock Alert Table -->
    <h3 class="dashboard-section-header">Inventory Watchlist (Stock &le; 5)</h3>
    <div class="dashboard-table-box">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th class="text-start">Product</th>
                    <th class="text-start">Category</th>
                    <th class="text-center">Current Stock</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($low_stock) === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4" style="font-family: 'Alinore'; color: #78716C; font-size: 1.2rem;">
                            All products are currently well-stocked.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($p = mysqli_fetch_assoc($low_stock)): ?>
                    <tr>
                        <td class="dashboard-item-name"><?= htmlspecialchars($p['item_name']); ?></td>
                        <td><?= htmlspecialchars($p['category']); ?></td>
                        <td class="text-center fw-bold fs-5" style="color: <?= ((int)$p['quantity'] === 0) ? '#FB7185' : '#D97706'; ?>;">
                            <?= (int) $p['quantity']; ?>
                        </td>
                        <td class="text-center">
                            <?php if ((int) $p['quantity'] === 0): ?>
                                <span class="order-badge-custom badge-canceled">Out of Stock</span>
                            <?php else: ?>
                                <span class="order-badge-custom badge-pending">Low Stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="../item/edit.php?id=<?= $p['item_id']; ?>" class="dashboard-restock-btn">
                                Restock &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- 4. Action Buttons Bar -->
    <div class="dashboard-actions-bar">
        <a href="orders.php" class="dashboard-btn-outline">
            &larr; Manage Orders
        </a>
        <a href="profit.php" class="dashboard-btn-primary">
            Profit Breakdown &rarr;
        </a>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
