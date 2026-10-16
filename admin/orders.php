<?php
// admin/orders.php - FR-19: admin views all customer orders
include('../includes/admin_check.php');
include('../includes/config.php');

// the customer's name is their email for now (names come with the customer profile, FR-5)
$sql = "SELECT o.order_id, u.email, o.order_date, o.total_amount, o.status
        FROM orders o
        LEFT JOIN users u ON u.user_id = o.user_id
        ORDER BY o.order_date DESC, o.order_id DESC";
$orders = mysqli_query($conn, $sql);

include('../includes/header.php');
?>

<h1 align="center" class="mb-4">Orders</h1>
<?php include('../includes/alert.php'); ?>

<div class="cart-view-table-back">
    <table class="table table-striped table-bordered align-middle">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date Ordered</th>
                <th>Order Total</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($orders) === 0): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No orders yet.</td></tr>
            <?php endif; ?>

            <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td>#<?= $o['order_id']; ?></td>
                    <td><?= htmlspecialchars($o['email'] ?? 'Unknown customer'); ?></td>
                    <td><?= date('F j, Y g:i A', strtotime($o['order_date'])); ?></td>
                    <td>₱<?= number_format($o['total_amount'], 2); ?></td>
                    <td><?= statusBadge($o['status']); ?></td>
                    <td class="text-center">
                        <a href="order_view.php?id=<?= $o['order_id']; ?>" class="btn btn-sm btn-dark">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>