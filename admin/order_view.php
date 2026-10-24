<?php
// admin/order_view.php - FR-19: Order Details + Status Update Form (Admin Only)
require_once "../includes/admin_check.php";
require_once "../includes/config.php";

$order_id = (int) ($_GET['id'] ?? 0);

// Read the order from the MySQL VIEW (one row per order line)
$stmt = mysqli_prepare($conn,
    "SELECT * FROM view_order_transaction_details WHERE order_id = ? ORDER BY order_item_id");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

if (!$rows) {
    $_SESSION['message'] = 'Order not found.';
    header('Location: orders.php');
    exit;
}

// First row carries the order + customer header; every row is an item line
$order = $rows[0];
$order['status'] = $order['order_status'];
$order['email']  = $order['customer_email'];

// Total is computed here in PHP, not stored in or calculated by the database
$grand_total = 0;
foreach ($rows as $line) {
    $grand_total += $line['unit_price'] * $line['quantity'];
}

$is_canceled = ($order['status'] === 'Canceled');

$customer_name = trim($order['customer_name'] ?? '');
$address_parts = array_filter([$order['address'] ?? '', $order['town'] ?? '', $order['postal_code'] ?? ''], 'strlen');
$full_address  = implode(', ', $address_parts);
$phone         = $order['phone'] ?? '';

require_once "../includes/header.php";
?>

<div class="order-view-wrapper">
    <h1 class="order-view-title">Order #<?= $order['order_id']; ?></h1>
    <p class="order-view-subtitle">Detailed customer summary, fulfillment tracking, and status management</p>

    <?php require_once "../includes/alert.php"; ?>

    <div class="order-view-card">
        <!-- Customer & Order Metadata Grid -->
        <div class="order-info-grid">
            <div class="order-info-group">
                <span class="order-info-label">Customer Name</span>
                <span class="order-info-val">
                    <?= htmlspecialchars($customer_name !== '' ? $customer_name : ($order['email'] ?? 'Unknown Customer')); ?>
                </span>
                <?php if ($customer_name !== ''): ?>
                    <span class="order-info-val-sub">&lt;<?= htmlspecialchars($order['email'] ?? ''); ?>&gt;</span>
                <?php endif; ?>
            </div>

            <div class="order-info-group">
                <span class="order-info-label">Contact Number</span>
                <span class="order-info-val">
                    <?= $phone !== '' ? htmlspecialchars($phone) : '<span style="color:#78716C;">Not provided</span>'; ?>
                </span>
            </div>

            <div class="order-info-group">
                <span class="order-info-label">Delivery Address</span>
                <span class="order-info-val" style="font-size: 1.1rem; line-height: 1.3;">
                    <?= $full_address !== '' ? htmlspecialchars($full_address) : '<span style="color:#78716C;">Not provided</span>'; ?>
                </span>
            </div>

            <div class="order-info-group">
                <span class="order-info-label">Date Ordered</span>
                <span class="order-info-val"><?= date('M j, Y • g:i A', strtotime($order['order_date'])); ?></span>
            </div>

            <div class="order-info-group">
                <span class="order-info-label">Payment Method</span>
                <span class="order-info-val"><?= htmlspecialchars($order['payment_method']); ?></span>
            </div>

            <div class="order-info-group">
                <span class="order-info-label">Current Status</span>
                <div><?= statusBadge($order['status']); ?></div>
            </div>
        </div>

        <!-- Itemized Order Table -->
        <div class="profit-table-box mb-0">
            <table class="profit-table">
                <thead>
                    <tr>
                        <th class="text-start">Item</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $it): ?>
                        <tr>
                            <td class="profit-item-title"><?= htmlspecialchars($it['item_name']); ?></td>
                            <td class="text-center">₱<?= number_format($it['unit_price'], 2); ?></td>
                            <td class="text-center"><?= $it['quantity']; ?></td>
                            <td class="text-end fw-bold">₱<?= number_format($it['unit_price'] * $it['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Total Line -->
            <div class="confirm-total-box">
                <span class="confirm-total-label">Order Total:</span>
                <span class="confirm-total-amount">₱<?= number_format($grand_total, 2); ?></span>
            </div>
        </div>

        <!-- Status Update Form Box -->
        <form method="POST" action="order_update.php"
              onsubmit="return this.status.value !== 'Canceled' || confirm('Cancel this order? The items will be returned to stock and this cannot be undone.');">
            <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">

            <div class="order-update-box">
                <div style="max-width: 320px; width: 100%;">
                    <label class="form-label" style="font-family: 'Alinore', sans-serif; font-size: 1.1rem; color: var(--text-brown); text-transform: uppercase; margin-bottom: 6px;">
                        Update Fulfillment Status
                    </label>
                    <select name="status" class="form-select order-select-input" <?= $is_canceled ? 'disabled' : ''; ?>>
                        <?php foreach ($order_statuses as $s): ?>
                            <option value="<?= $s; ?>" <?= $s === $order['status'] ? 'selected' : ''; ?>><?= $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($is_canceled): ?>
                        <div class="form-text text-danger" style="font-family: 'Alinore';">A canceled order cannot be modified.</div>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-3 align-items-center">
                    <a href="orders.php" class="dashboard-btn-outline" style="padding: 12px 28px; font-size: 1.2rem;">
                        &larr; Back
                    </a>
                    <?php if (!$is_canceled): ?>
                        <button type="submit" class="dashboard-btn-primary" style="padding: 12px 32px; font-size: 1.2rem;">
                            Update Status
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>