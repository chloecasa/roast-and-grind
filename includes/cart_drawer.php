<?php
// includes/cart_drawer.php - cart panel on the right side of shop.php (FR-17)
// same idea as the cart panel in the professor's index.php: Qty box + item name + Remove checkbox + Update / Checkout
// needs: $conn (config.php) and $returnUrl (set in shop.php, brings the customer back here with the cart open)
?>
<!-- No backdrop and page scrolling stays on, so the customer can keep browsing while the cart is open -->
<div class="offcanvas offcanvas-end cart-drawer"
     tabindex="-1"
     id="cartDrawer"
     data-bs-backdrop="false"
     data-bs-scroll="true"
     aria-labelledby="cartDrawerLabel">

    <button type="button" class="drawer-close-link" data-bs-dismiss="offcanvas" aria-label="Close">[close]</button>

<?php
if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0) {

    // prepared once, executed for every cart row (same idea as view_cart.php)
    $sql = "SELECT quantity FROM stock WHERE item_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $product_code);

    echo '<div class="cart-view-table-front" id="view-cart">';
    echo '<h3 id="cartDrawerLabel">Your Shopping Cart</h3>';
    echo '<form method="POST" action="cart_update.php">';
    echo '<input type="hidden" name="redirect" value="' . htmlspecialchars($returnUrl) . '" />';
    echo '<table width="100%" cellpadding="6" cellspacing="0">';
    echo '<tbody>';
    $total = 0;
    $b = 0;
    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $product_name  = $cart_itm["item_name"];
        $product_qty   = $cart_itm["item_qty"];
        $product_price = $cart_itm["item_price"];
        $product_code  = $cart_itm["item_id"];
        $bg_color = ($b++ % 2 == 1) ? 'odd' : 'even';

        // current stock, used as the max of the quantity box
        mysqli_stmt_execute($stmt);
        $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $max_qty   = $stock_row ? $stock_row['quantity'] : 0;

        echo '<tr class="' . $bg_color . '">';
        echo "<td>Qty <input type='number' min='1' max='{$max_qty}' name='product_qty[$product_code]' value='{$product_qty}' /></td>";
        echo '<td>' . htmlspecialchars($product_name) . '</td>';
        echo '<td><input type="checkbox" name="remove_code[]" value="' . $product_code . '" /> Remove</td>';
        echo '</tr>';
        $subtotal = ($product_price * $product_qty);
        $total += $subtotal;
    }
    echo '<tr class="cart-total-row">';
    echo '<td colspan="3">Amount Payable : ₱' . number_format($total, 2) . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="3" class="cart-actions">';
    echo '<button type="submit">Update</button><a href="checkout.php" class="button">Checkout</a>';
    echo '</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</form>';
    echo '</div>';
} else {
    echo '<div class="cart-drawer-empty">';
    echo '<i class="fa-solid fa-cart-shopping"></i>';
    echo '<p>Your cart is empty.</p>';
    echo '</div>';
}
?>
</div>