<?php
// order_confirmation.php - FR-18: Order Receipt
require_once "includes/session.php";
require_once "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: user/login.php');
    exit;
}

$order_id = (int) ($_GET['id'] ?? 0);


$stmt = mysqli_prepare($conn,
    "SELECT * FROM view_order_transaction_details
     WHERE order_id = ? AND user_id = ?
     ORDER BY order_item_id");
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

if (!$rows) {
    $_SESSION['message'] = 'Order not found.';
    header('Location: shop.php');
    exit;
}

$order = $rows[0];
$order['status'] = $order['order_status'];


$grand_total = 0;
foreach ($rows as $line) {
    $grand_total += $line['unit_price'] * $line['quantity'];
}

function statusBadge($status)
{
    $classes = [
        'Pending'    => 'bg-secondary',
        'Processing' => 'bg-primary',
        'Shipped'    => 'bg-info',
        'Delivered'  => 'bg-success',
        'Canceled'   => 'bg-danger'
    ];
    $cls = $classes[$status] ?? 'bg-dark';
    return '<span class="badge ' . $cls . '" style="font-family: \'Alinore\'; font-size: 1rem; letter-spacing: 1px;">' . htmlspecialchars($status) . '</span>';
}

include('includes/header.php');
?>

<div class="confirm-wrapper">
    <div class="confirm-card">
        
        <div class="confirm-icon-box">
            <i class="fa-solid fa-circle-check confirm-icon"></i>
        </div>

        <h1 class="confirm-title">Order Confirmed</h1>
        <p class="confirm-subtitle">Thank you for your order! We are preparing your fresh coffee.</p>

        <?php include('includes/alert.php'); ?>

        
        <div class="confirm-meta-grid">
            <div>
                <span class="confirm-meta-label">Order Number</span>
                <span class="confirm-meta-val">#<?= $order['order_id']; ?></span>
            </div>
            <div>
                <span class="confirm-meta-label">Date Placed</span>
                <span class="confirm-meta-val"><?= date('M j, Y • g:i A', strtotime($order['order_date'])); ?></span>
            </div>
            <div>
                <span class="confirm-meta-label">Payment Method</span>
                <span class="confirm-meta-val"><?= htmlspecialchars($order['payment_method']); ?></span>
            </div>
            <div>
                <span class="confirm-meta-label">Fulfillment Status</span>
                <span class="badge bg-success" style="font-family: 'Alinore', sans-serif; font-size: 1rem; letter-spacing: 1px;">
                    <?= htmlspecialchars($order['status']); ?>
                </span>
            </div>
        </div>

       
        <div class="confirm-table-box">
            <table class="confirm-table">
                <thead>
                    <tr>
                        <th class="text-start">Item</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $it): ?>
                        <tr>
                            <td class="confirm-item-name"><?= htmlspecialchars($it['item_name']); ?></td>
                            <td class="text-center">₱<?= number_format($it['unit_price'], 2); ?></td>
                            <td class="text-center"><?= $it['quantity']; ?></td>
                            <td class="text-end fw-bold">₱<?= number_format($it['unit_price'] * $it['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

           
            <div class="confirm-total-box">
                <span class="confirm-total-label">Total Amount:</span>
                <span class="confirm-total-amount">₱<?= number_format($grand_total, 2); ?></span>
            </div>
        </div>

      
        <div class="confirm-actions">
            <a href="shop.php" class="confirm-btn-primary">
                Continue Shopping
            </a>
            <a href="user/orders.php" class="confirm-btn-outline">
                View My Orders
            </a>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
