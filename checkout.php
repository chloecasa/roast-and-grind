<?php
// checkout.php - FR-18: login gate, stock re-verification, order review + payment method
include('./includes/session.php');
include('./includes/config.php');

// FR-18: a guest must log in or register first. The cart stays in the session, and
// user/login.php / user/store.php send the customer back here afterwards.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please log in or register to place your order. Your cart has been kept.';
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: user/login.php');
    exit;
}

if (!isset($_SESSION['cart_products']) || count($_SESSION['cart_products']) == 0) {
    $_SESSION['message'] = 'Your cart is empty.';
    header('Location: view_cart.php');
    exit;
}

// FR-18: re-verify the available stock of EVERY cart item (live from the database)
$sql = "SELECT i.item_name, i.sell_price, s.quantity AS qty
        FROM item i INNER JOIN stock s USING (item_id)
        WHERE i.item_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $item_id);

$problems = [];
$lines    = [];
$total    = 0;

foreach ($_SESSION['cart_products'] as $cart_itm) {
    $item_id = (int) $cart_itm['item_id'];
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $name = htmlspecialchars($cart_itm['item_name']);

    if (!$row) {
        $problems[] = "$name is no longer available. Please remove it from your cart.";
    } elseif ($cart_itm['item_qty'] > $row['qty']) {
        $problems[] = "Not enough stock for $name: only {$row['qty']} left, but you have {$cart_itm['item_qty']} in your cart.";
    } else {
        $subtotal = $row['sell_price'] * $cart_itm['item_qty'];
        $total   += $subtotal;
        $lines[]  = ['name' => $row['item_name'], 'price' => $row['sell_price'], 'qty' => $cart_itm['item_qty'], 'subtotal' => $subtotal];
    }
}

// block the order if anything is short
if ($problems) {
    $_SESSION['message'] = implode('<br>', $problems) . '<br>Order not placed. Please update your cart.';
    header('Location: view_cart.php');
    exit;
}

include('./includes/header.php');
?>

<h1 align="center" class="mb-4">Checkout</h1>
<?php include('./includes/alert.php'); ?>

<div class="cart-view-table-back">
    <table class="table table-striped table-bordered align-middle">
        <thead>
            <tr><th>Item</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['name']); ?></td>
                    <td>₱<?= number_format($l['price'], 2); ?></td>
                    <td><?= $l['qty']; ?></td>
                    <td>₱<?= number_format($l['subtotal'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="text-end fw-bold">Amount Payable : ₱<?= number_format($total, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <form method="POST" action="place_order.php">
        <div class="mb-3" style="max-width:320px">
            <label class="form-label fw-bold">Payment method</label>
            <select name="payment_method" class="form-select" required>
                <option value="">Choose...</option>
                <option value="Cash on Delivery">Cash on Delivery</option>
                <option value="GCash">GCash</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
        </div>
        <a href="view_cart.php" class="btn btn-outline-dark">Back to Cart</a>
        <button type="submit" class="btn btn-warning float-end">Place Order</button>
    </form>
</div>

<?php include('./includes/footer.php'); ?>