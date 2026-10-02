<?php
// view_cart.php - FR-17: view cart, change quantities, remove items, cart total
include('./includes/session.php'); // own session name for this app
include('./includes/header.php');
include('./includes/config.php');
?>

<h1 align="center" class="mb-4">Your Cart</h1>
<?php include('./includes/alert.php'); ?>

<?php
if (isset($_SESSION['cart_products']) && count($_SESSION['cart_products']) > 0) {

    // prepared once, executed for every cart row (same idea as checkout.php)
    $sql = "SELECT quantity FROM stock WHERE item_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $product_code);
?>
    <div class="cart-view-table-back">
        <form method="POST" action="cart_update.php">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0; // set initial total value

                    foreach ($_SESSION['cart_products'] as $cart_itm) {

                        // set variables to use in content below
                        $product_name  = $cart_itm['item_name'];
                        $product_qty   = $cart_itm['item_qty'];
                        $product_price = $cart_itm['item_price'];
                        $product_code  = $cart_itm['item_id'];
                        $subtotal      = $product_price * $product_qty; // Price x Qty

                        // current stock, used as the max of the quantity box
                        mysqli_stmt_execute($stmt);
                        $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                        $max_qty   = $stock_row ? $stock_row['quantity'] : 0;

                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($product_name) . '</td>';
                        echo '<td>₱' . number_format($product_price, 2) . '</td>';
                        echo '<td><input type="number" class="form-control" style="width:90px" min="1" max="' . $max_qty . '" name="product_qty[' . $product_code . ']" value="' . $product_qty . '" /></td>';
                        echo '<td>₱' . number_format($subtotal, 2) . '</td>';
                        echo '<td class="text-center"><input type="checkbox" class="form-check-input" name="remove_code[]" value="' . $product_code . '" /></td>';
                        echo '</tr>';

                        $total += $subtotal; // add subtotal to total var
                    }
                    ?>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Amount Payable : ₱<?php echo number_format($total, 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <input type="hidden" name="redirect" value="view_cart.php" />

           <a href="shop.php" class="btn btn-outline-dark">Add More Items</a>
            <button type="submit" class="btn btn-dark">Update Cart</button>
            <!-- checkout.php is FR-18 (next step) -->
            <a href="checkout.php" class="btn btn-warning float-end">Checkout</a>
        </form>
    </div>
<?php
} else {
    echo '<div class="text-center py-5">';
    echo '<i class="fa-solid fa-cart-shopping fa-3x text-muted mb-3"></i>';
    echo '<h5 class="text-muted">Your cart is empty.</h5>';
   echo '<a href="shop.php" class="btn btn-outline-dark mt-2">Continue Shopping</a>';
    echo '</div>';
}

include('./includes/footer.php');
?>
