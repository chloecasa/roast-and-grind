<?php
// place_order.php - FR-18 (save order + items + payment method + stock deduction as ONE transaction)
//                   FR-15 (automatic stock decrement on successful order placement)
include('./includes/session.php');
include('./includes/config.php');

// make mysqli throw exceptions on any SQL error so the catch block can roll back
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

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

$allowed_payments = ['Cash on Delivery', 'GCash', 'Bank Transfer'];
$payment_method   = $_POST['payment_method'] ?? '';
if (!in_array($payment_method, $allowed_payments, true)) {
    $_SESSION['message'] = 'Please choose a payment method.';
    header('Location: checkout.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

mysqli_begin_transaction($conn);

try {
    // lock each product + stock row so two customers cannot buy the last item at the same time
    $sel = mysqli_prepare($conn,
        "SELECT i.item_name, i.cost_price, i.sell_price, s.quantity AS qty
         FROM item i INNER JOIN stock s USING (item_id)
         WHERE i.item_id = ? FOR UPDATE");
    mysqli_stmt_bind_param($sel, 'i', $item_id);

    $lines = [];
    $total = 0;

    foreach ($_SESSION['cart_products'] as $cart_itm) {
        $item_id = (int) $cart_itm['item_id'];
        $qty     = (int) $cart_itm['item_qty'];

        mysqli_stmt_execute($sel);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($sel));

        // FR-18: re-verify stock for every item, block the whole order if one is short
        if (!$row) {
            throw new RuntimeException(htmlspecialchars($cart_itm['item_name']) . ' is no longer available.');
        }
        if ($qty < 1 || $qty > $row['qty']) {
            throw new RuntimeException('Not enough stock for ' . htmlspecialchars($row['item_name'])
                . ': only ' . $row['qty'] . ' left.');
        }

        // price comes from the database, not from the session copy
        $lines[] = [
            'item_id' => $item_id,
            'name'    => $row['item_name'],
            'price'   => $row['sell_price'],
            'cost'    => $row['cost_price'],
            'qty'     => $qty
        ];
        $total += $row['sell_price'] * $qty;
    }

    // 1. the order
    $ins_order = mysqli_prepare($conn, "INSERT INTO orders (user_id, total_amount, payment_method) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($ins_order, 'ids', $user_id, $total, $payment_method);
    mysqli_stmt_execute($ins_order);
    $order_id = mysqli_insert_id($conn);

    // 2. its items + 3. FR-15 stock decrement for each one
    $ins_item = mysqli_prepare($conn,
        "INSERT INTO order_items (order_id, item_id, item_name, unit_price, unit_cost, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    $dec = mysqli_prepare($conn, "UPDATE stock SET quantity = quantity - ? WHERE item_id = ? AND quantity >= ?");

    foreach ($lines as $l) {
        mysqli_stmt_bind_param($ins_item, 'iisddi', $order_id, $l['item_id'], $l['name'], $l['price'], $l['cost'], $l['qty']);
        mysqli_stmt_execute($ins_item);

        mysqli_stmt_bind_param($dec, 'iii', $l['qty'], $l['item_id'], $l['qty']);
        mysqli_stmt_execute($dec);
        if (mysqli_stmt_affected_rows($dec) !== 1) {
            throw new RuntimeException('Not enough stock for ' . htmlspecialchars($l['name']) . '.');
        }
    }

    // all saved together, or (on any error above) none of it
    mysqli_commit($conn);

    unset($_SESSION['cart_products']);
    $_SESSION['success'] = 'Thank you! Your order has been placed.';
    header("Location: order_confirmation.php?id=$order_id");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);

    if ($e instanceof RuntimeException) {
        $_SESSION['message'] = $e->getMessage() . '<br>Order not placed. Please update your cart.';
    } else {
        $_SESSION['message'] = 'Something went wrong, so no order was placed and your stock was not changed. Please try again.';
    }
    header('Location: view_cart.php');
    exit;
}