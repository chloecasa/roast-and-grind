<?php
// cart_update.php - FR-17: add to cart, change quantity, remove item (guests and customers)
include('./includes/session.php'); // own session name for this app
include('./includes/config.php');

$errors  = [];
$success = [];

// only our own two pages are allowed as the page to go back to
$redirect = 'index.php';
if (isset($_POST['redirect'])) {
    $page = strtok($_POST['redirect'], '?');
    if (in_array($page, ['index.php', 'view_cart.php'])) {
        $redirect = $_POST['redirect'];
    }
}

// fetch a product together with its current stock
function getProduct($conn, $item_id)
{
    $sql = "SELECT i.item_id, i.item_name, i.sell_price, s.quantity AS qty
            FROM item i INNER JOIN stock s USING (item_id)
            WHERE i.item_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ---------- ADD ----------
if (isset($_POST['type']) && $_POST['type'] == 'add') {

    $item_id  = (int) ($_POST['item_id'] ?? 0);
    $item_qty = (int) ($_POST['item_qty'] ?? 0);
    $row      = getProduct($conn, $item_id);

    if ($item_qty < 1) {
        $errors[] = 'Quantity must be at least 1.';
    } elseif (!$row) {
        $errors[] = 'Product not found.';
    } elseif ($row['qty'] <= 0) {
        // FR-17: Out of Stock items cannot be added
        $errors[] = htmlspecialchars($row['item_name']) . ' is out of stock.';
    } else {
        // quantity already in the cart for this item
        $in_cart = isset($_SESSION['cart_products'][$item_id]) ? $_SESSION['cart_products'][$item_id]['item_qty'] : 0;

        if ($in_cart + $item_qty > $row['qty']) {
            // FR-17: cannot go over available stock
            $errors[] = 'Cannot add ' . $item_qty . ' x ' . htmlspecialchars($row['item_name'])
                . '. Only ' . $row['qty'] . ' in stock (' . $in_cart . ' already in your cart).';
        } else {
            $new_product = [
                'item_id'    => $row['item_id'],
                'item_name'  => $row['item_name'],
                'item_price' => $row['sell_price'],
                'item_qty'   => $in_cart + $item_qty
            ];
            $_SESSION['cart_products'][$item_id] = $new_product;
            $success[] = htmlspecialchars($row['item_name']) . ' added to cart.';
        }
    }
}

// ---------- REMOVE ----------
if (isset($_POST['remove_code']) && is_array($_POST['remove_code'])) {
    foreach ($_POST['remove_code'] as $key) {
        $key = (int) $key;
        if (isset($_SESSION['cart_products'][$key])) {
            unset($_SESSION['cart_products'][$key]);
            $success[] = 'Item removed from cart.';
        }
    }
}

// ---------- UPDATE QUANTITIES ----------
if (isset($_POST['product_qty']) && is_array($_POST['product_qty'])) {
    foreach ($_POST['product_qty'] as $key => $value) {
        $item_id = (int) $key;

        // skip items that were just removed
        if (!isset($_SESSION['cart_products'][$item_id])) {
            continue;
        }

        $name = htmlspecialchars($_SESSION['cart_products'][$item_id]['item_name']);
        $qty  = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($qty === false) {
            $errors[] = "Invalid quantity for $name. Use Remove to delete an item.";
            continue;
        }

        $row = getProduct($conn, $item_id);

        if (!$row || $row['qty'] <= 0) {
            unset($_SESSION['cart_products'][$item_id]);
            $errors[] = "$name is no longer available and was removed from your cart.";
        } elseif ($qty > $row['qty']) {
            $errors[] = "Only {$row['qty']} of $name in stock.";
        } else {
            $_SESSION['cart_products'][$item_id]['item_qty']   = $qty;
            $_SESSION['cart_products'][$item_id]['item_price'] = $row['sell_price'];
        }
    }
}

// empty cart -> remove the session variable
if (isset($_SESSION['cart_products']) && count($_SESSION['cart_products']) == 0) {
    unset($_SESSION['cart_products']);
}

// FR-17: display a message whenever a cart action is rejected
if (count($errors) > 0) {
    $_SESSION['message'] = implode('<br>', $errors);
}
if (count($success) > 0) {
    $_SESSION['success'] = implode('<br>', $success);
}

header("Location: $redirect");
exit;