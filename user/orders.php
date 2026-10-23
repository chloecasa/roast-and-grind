<?php
// user/orders.php - FR-6: Logged-in Customer Views Their Own Order History
require_once "../includes/session.php";
require_once "../includes/config.php";

// FR-3: Customer pages are for logged-in users only
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please log in to view your orders.';
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Secure parameterized query for this user's orders
$sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status,
               oi.item_name, oi.quantity
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.order_id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC, o.order_id DESC, oi.order_item_id ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$rows = mysqli_stmt_get_result($stmt);

// Group items under each order
$orders = [];
while ($r = mysqli_fetch_assoc($rows)) {
    $id = $r['order_id'];
    if (!isset($orders[$id])) {
        $orders[$id] = [
            'order_id'     => $id,
            'order_date'   => $r['order_date'],
            'total_amount' => $r['total_amount'],
            'status'       => $r['status'],
            'items'        => []
        ];
    }
    if ($r['item_name'] !== null) {
        $orders[$id]['items'][] = ['name' => $r['item_name'], 'qty' => $r['quantity']];
    }
}

// Custom boutique status badges
function orderStatusBadge($status)
{
    $classes = [
        'Pending'    => 'badge-pending',
        'Processing' => 'badge-processing',
        'Shipped'    => 'badge-shipped',
        'Delivered'  => 'badge-delivered',
        'Canceled'   => 'badge-canceled'
    ];
    $cls = $classes[$status] ?? 'badge-pending';
    return '<span class="order-badge-custom ' . $cls . '">' . htmlspecialchars($status) . '</span>';
}

require_once "../includes/header.php";
?>

<div class="orders-wrapper">
    <h1 class="orders-title">My Orders</h1>
    <p class="orders-subtitle">Track your past and ongoing specialty coffee orders</p>

    <?php require_once "../includes/alert.php"; ?>

    <div class="orders-card">
        <?php if (count($orders) === 0): ?>
            <!-- Empty Order History State -->
            <div class="orders-empty-box">
                <i class="fa-solid fa-mug-hot fa-3x mb-3" style="color: #FED7AA;"></i>
                <h3 class="orders-empty-title">No Orders Yet</h3>
                <p class="orders-empty-desc">You haven't placed any orders yet. Discover our fresh roast selection!</p>
                <a href="../shop.php" class="orders-shop-btn">Start Shopping</a>
            </div>
        <?php else: ?>
            <!-- Orders Table -->
            <div class="orders-table-box">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th class="text-start">Order ID</th>
                            <th class="text-start">Date Ordered</th>
                            <th class="text-start">Items</th>
                            <th class="text-center">Total</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="order-id-tag">#<?= $o['order_id']; ?></td>
                                <td><?= date('M j, Y • g:i A', strtotime($o['order_date'])); ?></td>
                                <td>
                                    <?php foreach ($o['items'] as $it): ?>
                                        <div class="order-item-line">
                                            <?= htmlspecialchars($it['name']); ?>
                                            <span class="order-item-qty">&times; <?= (int) $it['qty']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                                <td class="text-center order-total-price">
                                    ₱<?= number_format($o['total_amount'], 2); ?>
                                </td>
                                <td class="text-end">
                                    <?= orderStatusBadge($o['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
