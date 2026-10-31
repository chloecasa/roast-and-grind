<?php
require_once "../includes/admin_check.php";
require_once "../includes/config.php";

function peso($n)
{
    return ($n < 0 ? '-' : '') . '₱' . number_format(abs($n), 2);
}


$result   = mysqli_query($conn, "SELECT item_name, category, cost_price, sell_price FROM item");
$products = [];
while ($p = mysqli_fetch_assoc($result)) {
    $p['profit'] = $p['sell_price'] - $p['cost_price'];
    $products[]  = $p;
}

usort($products, function ($a, $b) {
    return [$b['profit'], $a['item_name']] <=> [$a['profit'], $b['item_name']];
});



$result = mysqli_query($conn,
    "SELECT o.order_id, o.order_date, o.status,
            oi.unit_price, oi.unit_cost, oi.quantity
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.order_id
     ORDER BY o.order_date DESC, o.order_id DESC, oi.order_item_id ASC");

$by_order = [];
while ($r = mysqli_fetch_assoc($result)) {
    $id = $r['order_id'];
    if (!isset($by_order[$id])) {
        $by_order[$id] = [
            'order_id'     => $id,
            'order_date'   => $r['order_date'],
            'status'       => $r['status'],
            'total'        => 0,
            'profit'       => 0
        ];
    }
    if ($r['quantity'] !== null) {
        $by_order[$id]['total'] += $r['unit_price'] * $r['quantity'];
        $by_order[$id]['profit']       += ($r['unit_price'] - $r['unit_cost']) * $r['quantity'];
    }
}

$rows = array_values($by_order);
$total_profit = 0;
foreach ($rows as $o) {
    if ($o['status'] !== 'Canceled') {
        $total_profit += (float) $o['profit'];
    }
}

require_once "../includes/header.php";
?>

<div class="profit-wrapper">
    <h1 class="profit-title">Profit Report</h1>
    <p class="profit-subtitle">Unit margins, historical order profitability, and net realized earnings</p>

    <?php require_once "../includes/alert.php"; ?>

  
    <div class="profit-kpi-card">
        <div class="profit-kpi-label">Total Realized Profit</div>
        <div class="profit-kpi-val <?= $total_profit < 0 ? 'negative' : ''; ?>">
            <?= peso($total_profit); ?>
        </div>
        <div class="profit-kpi-note">Calculated across all non-canceled customer orders</div>
    </div>

   
    <h3 class="profit-section-header">Profit Margin per Product (Current Catalog)</h3>
    <div class="profit-table-box">
        <table class="profit-table">
            <thead>
                <tr>
                    <th class="text-start">Product</th>
                    <th class="text-start">Category</th>
                    <th class="text-center">Cost Price</th>
                    <th class="text-center">Selling Price</th>
                    <th class="text-end">Margin / Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4" style="color: #78716C;">No products found in catalog.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="profit-item-title"><?= htmlspecialchars($p['item_name']); ?></td>
                        <td><?= htmlspecialchars($p['category']); ?></td>
                        <td class="text-center"><?= peso($p['cost_price']); ?></td>
                        <td class="text-center"><?= peso($p['sell_price']); ?></td>
                        <td class="text-end <?= $p['profit'] < 0 ? 'profit-val-negative' : 'profit-val-positive'; ?>">
                            <?= peso($p['profit']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

   
    <h3 class="profit-section-header">Profit Realized per Order</h3>
    <div class="profit-table-box">
        <table class="profit-table">
            <thead>
                <tr>
                    <th class="text-start">Order ID</th>
                    <th class="text-start">Date Ordered</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Order Total</th>
                    <th class="text-end">Realized Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4" style="color: #78716C;">No customer orders placed yet.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($rows as $o): 
                    $canceled = ($o['status'] === 'Canceled'); 
                ?>
                    <tr class="<?= $canceled ? 'row-canceled' : ''; ?>">
                        <td>
                            <a href="order_view.php?id=<?= $o['order_id']; ?>" class="profit-order-link">
                                #<?= $o['order_id']; ?>
                            </a>
                        </td>
                        <td><?= date('M j, Y • g:i A', strtotime($o['order_date'])); ?></td>
                        <td class="text-center">
                            <?= statusBadge($o['status']); ?>
                        </td>
                        <td class="text-center"><?= peso($o['total']); ?></td>
                        <td class="text-end <?= (!$canceled && $o['profit'] < 0) ? 'profit-val-negative' : 'profit-val-positive'; ?>">
                            <?= peso($o['profit']); ?>
                            <?php if ($canceled): ?>
                                <small style="display:block; color:#78716C; font-size:0.85rem;">(not counted)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <div>
        <a href="dashboard.php" class="dashboard-btn-outline">
            &larr; Back to Dashboard
        </a>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
