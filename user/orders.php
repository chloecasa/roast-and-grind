<?php
// user/orders.php - FR-6: a logged-in customer views their own order history
include('../includes/session.php');
include('../includes/config.php');

// FR-3: customer pages are for logged-in users only
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please log in to view your orders.';
    header('Location: login.php');
    exit;
}
$user_id = (int) $_SESSION['user_id'];

// only this user's orders: the id always comes from the session, never from the URL or a form
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

// one entry per order, with its items collected underneath
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

function orderStatusBadge($status)
{
    $colors = [
        'Pending'    => 'secondary',
        'Processing' => 'primary',
        'Shipped'    => 'info',
        'Delivered'  => 'success',
        'Canceled'   => 'danger'
    ];
    $color = $colors[$status] ?? 'dark';
    return '<span class="badge text-bg-' . $color . '">' . htmlspecialchars($status) . '</span>';
}

include('../includes/header.php');
?>

<h1 align="center" class="mb-4">My Orders</h1>
<?php include('../includes/alert.php'); ?>

<div class="cart-view-table-back">
    <?php if (count($orders) === 0): ?>
        <div class="text-center py-4">
            <p class="text-muted mb-3">You haven't placed any orders yet.</p>
            <a href="../shop.php" class="btn btn-dark">Start Shopping</a>
        </div>
    <?php else: ?>
        <table class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date Ordered</th>
                    <th>Items</th>
                    <th>Order Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['order_id']; ?></td>
                        <td><?= date('F j, Y g:i A', strtotime($o['order_date'])); ?></td>
                        <td>
                            <?php foreach ($o['items'] as $it): ?>
                                <div><?= htmlspecialchars($it['name']); ?> <span class="text-muted">&times; <?= (int) $it['qty']; ?></span></div>
                            <?php endforeach; ?>
                        </td>
                        <td>₱<?= number_format($o['total_amount'], 2); ?></td>
                        <td><?= orderStatusBadge($o['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>