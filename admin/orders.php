<?php
// admin/orders.php - FR-19: Admin Views All Customer Orders
require_once "../includes/admin_check.php";
require_once "../includes/config.php";

// Customer name comes from the customer profile (FR-5); email is fallback
$sql = "SELECT o.order_id, u.email, c.first_name, c.last_name, o.order_date, o.total_amount, o.status
        FROM orders o
        LEFT JOIN users u ON u.user_id = o.user_id
        LEFT JOIN customers c ON c.user_id = o.user_id
        ORDER BY o.order_date DESC, o.order_id DESC";
$orders = mysqli_query($conn, $sql);

require_once "../includes/header.php";
?>

<div class="admin-orders-wrapper">
    <h1 class="admin-orders-title">Customer Orders</h1>
    <p class="admin-orders-subtitle">Master ledger of all customer transactions, order totals, and fulfillment statuses</p>

    <?php require_once "../includes/alert.php"; ?>

    <!-- Orders Table Container -->
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
                <?php if (mysqli_num_rows($orders) === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4" style="color: #78716C; font-family: 'Alinore'; font-size: 1.2rem;">
                            No customer orders placed yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <!-- Order ID -->
                        <td>
                            <span class="order-id-tag">#<?= $o['order_id']; ?></span>
                        </td>

                        <!-- Customer Name & Email Fallback -->
                        <?php $name = trim(($o['first_name'] ?? '') . ' ' . ($o['last_name'] ?? '')); ?>
                        <td>
                            <div class="admin-customer-name">
                                <?= htmlspecialchars($name !== '' ? $name : ($o['email'] ?? 'Unknown Customer')); ?>
                            </div>
                            <?php if ($name !== ''): ?>
                                <span class="admin-customer-email"><?= htmlspecialchars($o['email'] ?? ''); ?></span>
                            <?php endif; ?>
                        </td>

                        <!-- Date Ordered -->
                        <td><?= date('M j, Y • g:i A', strtotime($o['order_date'])); ?></td>

                        <!-- Total Amount -->
                        <td class="text-center" style="font-family: 'Alinore', sans-serif; font-size: 1.3rem; font-weight: bold; color: #4D6443;">
                            ₱<?= number_format($o['total_amount'], 2); ?>
                        </td>

                        <!-- Harmonious Status Badge -->
                        <td class="text-center">
                            <?= statusBadge($o['status']); ?>
                        </td>

                        <!-- Action Link -->
                        <td class="text-end">
                            <a href="order_view.php?id=<?= $o['order_id']; ?>" class="admin-order-view-btn">
                                View Details &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Navigation Buttons -->
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
