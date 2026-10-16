<?php
// admin/order_view.php - FR-19: order details + status update form (admin only)
include('../includes/admin_check.php');
include('../includes/config.php');

$order_id = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn,
    "SELECT o.order_id, o.order_date, o.total_amount, o.payment_method, o.status, u.email
     FROM orders o
     LEFT JOIN users u ON u.user_id = o.user_id
     WHERE o.order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    $_SESSION['message'] = 'Order not found.';
    header('Location: orders.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT item_name, unit_price, quantity FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);

$is_canceled = ($order['status'] === 'Canceled');

include('../includes/header.php');
?>

<h1 align="center" class="mb-4">Order #<?= $order['order_id']; ?></h1>
<?php include('../includes/alert.php'); ?>

<div class="cart-view-table-back">
    <p class="mb-1"><strong>Customer:</strong> <?= htmlspecialchars($order['email'] ?? 'Unknown customer'); ?></p>
    <!-- delivery address and contact number come from the customer profile (FR-5), built later -->
    <p class="mb-1"><strong>Delivery address:</strong> <span class="text-muted">Not provided</span></p>
    <p class="mb-1"><strong>Contact number:</strong> <span class="text-muted">Not provided</span></p>
    <p class="mb-1"><strong>Date ordered:</strong> <?= date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
    <p class="mb-3"><strong>Payment method:</strong> <?= htmlspecialchars($order['payment_method']); ?></p>

    <table class="table table-striped table-bordered align-middle">
        <thead>
            <tr><th>Item</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
        </thead>
        <tbody>
            <?php while ($it = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= htmlspecialchars($it['item_name']); ?></td>
                    <td>₱<?= number_format($it['unit_price'], 2); ?></td>
                    <td><?= $it['quantity']; ?></td>
                    <td>₱<?= number_format($it['unit_price'] * $it['quantity'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="4" class="text-end fw-bold">Order Total : ₱<?= number_format($order['total_amount'], 2); ?></td>
            </tr>
        </tbody>
    </table>

    <form method="POST" action="order_update.php"
          onsubmit="return this.status.value !== 'Canceled' || confirm('Cancel this order? The items will be returned to stock and this cannot be undone.');">
        <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">

        <div class="mb-3" style="max-width:320px">
            <label class="form-label fw-bold">Status: <?= statusBadge($order['status']); ?></label>
            <select name="status" class="form-select" <?= $is_canceled ? 'disabled' : ''; ?>>
                <?php foreach ($order_statuses as $s): ?>
                    <option value="<?= $s; ?>" <?= $s === $order['status'] ? 'selected' : ''; ?>><?= $s; ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($is_canceled): ?>
                <div class="form-text">A canceled order can't be changed.</div>
            <?php endif; ?>
        </div>

        <a href="orders.php" class="btn btn-outline-dark">Back to Orders</a>
        <?php if (!$is_canceled): ?>
            <button type="submit" class="btn btn-warning float-end">Update Status</button>
        <?php endif; ?>
    </form>
</div>

<?php include('../includes/footer.php'); ?>