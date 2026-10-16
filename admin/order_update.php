<?php
// admin/order_update.php - FR-19: change an order's status
//                          FR-16: Canceled returns the items to stock, in ONE transaction
include('../includes/admin_check.php');
include('../includes/config.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$order_id   = (int) ($_POST['order_id'] ?? 0);
$new_status = $_POST['status'] ?? '';

if (!in_array($new_status, $order_statuses, true)) {
    $_SESSION['message'] = 'Invalid status.';
    header("Location: order_view.php?id=$order_id");
    exit;
}

mysqli_begin_transaction($conn);

try {
    // lock the order row so two admins can't cancel the same order (and restock it) twice
    $sel = mysqli_prepare($conn, "SELECT status FROM orders WHERE order_id = ? FOR UPDATE");
    mysqli_stmt_bind_param($sel, 'i', $order_id);
    mysqli_stmt_execute($sel);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($sel));

    if (!$order) {
        throw new RuntimeException('Order not found.');
    }
    if ($order['status'] === 'Canceled') {
        throw new RuntimeException('This order is already canceled and can no longer be changed.');
    }
    if ($order['status'] === $new_status) {
        throw new RuntimeException("The order is already $new_status.");
    }

    // FR-16: Canceled -> give every purchased quantity back to stock
    if ($new_status === 'Canceled') {
        $lines = mysqli_prepare($conn, "SELECT item_id, quantity FROM order_items WHERE order_id = ?");
        mysqli_stmt_bind_param($lines, 'i', $order_id);
        mysqli_stmt_execute($lines);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($lines), MYSQLI_ASSOC);

        $restock = mysqli_prepare($conn, "UPDATE stock SET quantity = quantity + ? WHERE item_id = ?");
        foreach ($rows as $r) {
            mysqli_stmt_bind_param($restock, 'ii', $r['quantity'], $r['item_id']);
            mysqli_stmt_execute($restock);
            // if the product was deleted since the order, there is no stock row to update - skip it
        }
    }

    $upd = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE order_id = ?");
    mysqli_stmt_bind_param($upd, 'si', $new_status, $order_id);
    mysqli_stmt_execute($upd);

    mysqli_commit($conn);

    $_SESSION['success'] = ($new_status === 'Canceled')
        ? "Order #$order_id canceled. The items were returned to stock."
        : "Order #$order_id is now $new_status.";
} catch (Throwable $e) {
    mysqli_rollback($conn);

    if ($e instanceof RuntimeException) {
        $_SESSION['message'] = $e->getMessage();
    } else {
        $_SESSION['message'] = 'Something went wrong, so nothing was changed. Please try again.';
    }
}

header("Location: order_view.php?id=$order_id");
exit;