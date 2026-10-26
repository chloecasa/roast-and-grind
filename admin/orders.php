<?php

require_once "../includes/admin_check.php";
require_once "../includes/config.php";


$sql = "SELECT order_id, order_date, order_status, customer_email, customer_name,
               unit_price, quantity
        FROM view_order_transaction_details
        ORDER BY order_date DESC, order_id DESC, order_item_id ASC";
$result = mysqli_query($conn, $sql);


$orders = [];
while ($r = mysqli_fetch_assoc($result)) {
    $id = $r['order_id'];
    if (!isset($orders[$id])) {
        $orders[$id] = [
            'order_id'   => $id,
            'order_date' => $r['order_date'],
            'status'     => $r['order_status'],
            'email'      => $r['customer_email'],
            'name'       => trim($r['customer_name'] ?? ''),
            'total'      => 0
        ];
    }
    $orders[$id]['total'] += $r['unit_price'] * $r['quantity'];
}

require_once "../includes/header.php";
?>

<div class="admin-orders-wrapper">
    <h1 class="admin-orders-title">Customer Orders</h1>
    <p class="admin-orders-subtitle">Master ledger of all customer transactions, order totals, and fulfillment statuses</p>

    <?php require_once "../includes/alert.php"; ?>

    
    <div class="admin-orders-box">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th class="text-start">Order ID</th>
                    <th class="text-start">Customer Details</th>
                    <th class="text-start">Date Ordered</th>
                    <th class="text-center">Order Total</th>
                    <th class="text-center">Fulfillment Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4" style="color: #78716C; font-family: 'Alinore'; font-size: 1.2rem;">
                            No customer orders placed yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($orders as $o): ?>
                    <tr>
               
                        <td>
                            <span class="order-id-tag">#<?= $o['order_id']; ?></span>
                        </td>

                     
                        <?php $name = $o['name']; ?>
                        <td>
                            <div class="admin-customer-name">
                                <?= htmlspecialchars($name !== '' ? $name : ($o['email'] ?? 'Unknown Customer')); ?>
                            </div>
                            <?php if ($name !== ''): ?>
                                <span class="admin-customer-email"><?= htmlspecialchars($o['email'] ?? ''); ?></span>
                            <?php endif; ?>
                        </td>

                   
                        <td><?= date('M j, Y • g:i A', strtotime($o['order_date'])); ?></td>

                     
                        <td class="text-center" style="font-family: 'Alinore', sans-serif; font-size: 1.3rem; font-weight: bold; color: #4D6443;">
                            ₱<?= number_format($o['total'], 2); ?>
                        </td>

           
                        <td class="text-center">
                            <?= statusBadge($o['status']); ?>
                        </td>

                
                        <td class="text-end">
                            <a href="order_view.php?id=<?= $o['order_id']; ?>" class="admin-order-view-btn">
                                View Details &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

   
    <div class="dashboard-actions-bar">
        <a href="dashboard.php" class="dashboard-btn-outline">
            &larr; Back to Dashboard
        </a>
        <a href="profit.php" class="dashboard-btn-primary">
            Profit Report &rarr;
        </a>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>