<?php
// order_confirmation.php - FR-18: Order ID, date, items, quantities, total, payment method
include('./includes/session.php');
include('./includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: user/login.php');
    exit;
}

$order_id = (int) ($_GET['id'] ?? 0);

// a customer can only open their OWN order
$stmt = mysqli_prepare($conn,
    "SELECT order_id, order_date, total_amount, payment_method, status FROM orders WHERE order_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    $_SESSION['message'] = 'Order not found.';
    header('Location: shop.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT item_name, unit_price, quantity FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);

include('./includes/header.php');
?>

<h1 align="center" class="mb-2">Order Confirmed</h1>
<p align="center" class="text-muted mb-4">Thank you for your order!</p>
<?php include('./includes/alert.php'); ?>

<div class="cart-view-table-back">
    <p class="mb-1"><strong>Order ID:</strong> #<?= $order['order_id']; ?></p>
    <p class="mb-1"><strong>Date:</strong> <?= date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
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

    <a href="shop.php" class="btn btn-outline-dark">Continue Shopping</a>
</div>

<?php include('./includes/footer.php'); ?>