<?php
// view_cart.php - FR-17
include('./includes/session.php');
include('./includes/header.php');
include('./includes/config.php');
?>

<div class="cart-page-wrapper">
    <h1 class="cart-page-title">Your Cart</h1>
    <p class="cart-page-subtitle">Review items, adjust quantities, or proceed to secure checkout</p>

    <?php include('./includes/alert.php'); ?>

    <div class="cart-page-card">
        <?php
        if (isset($_SESSION['cart_products']) && count($_SESSION['cart_products']) > 0) {
            $sql = "SELECT quantity FROM stock WHERE item_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $product_code);
        ?>
            <form method="POST" action="cart_update.php">
                <div class="cart-table-box">
                    <table class="cart-main-table">
                        <thead>
                            <tr>
                                <th class="text-start">Item</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;

                            foreach ($_SESSION['cart_products'] as $cart_itm) {
                                $product_name  = $cart_itm['item_name'];
                                $product_qty   = $cart_itm['item_qty'];
                                $product_price = $cart_itm['item_price'];
                                $product_code  = $cart_itm['item_id'];
                                $subtotal      = $product_price * $product_qty;

                                mysqli_stmt_execute($stmt);
                                $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                                $max_qty   = $stock_row ? $stock_row['quantity'] : 0;
                            ?>
                                <tr>
                                    <td class="cart-item-heading"><?= htmlspecialchars($product_name); ?></td>
                                    <td class="text-center">₱<?= number_format($product_price, 2); ?></td>
                                    <td class="text-center">
                                        <input type="number" class="form-control cart-qty-input mx-auto" 
                                               min="1" max="<?= $max_qty; ?>" 
                                               name="product_qty[<?= $product_code; ?>]" 
                                               value="<?= $product_qty; ?>" />
                                    </td>
                                    <td class="text-end fw-bold" style="color: #4D6443;">₱<?= number_format($subtotal, 2); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="cart-remove-checkbox" name="remove_code[]" value="<?= $product_code; ?>" />
                                    </td>
                                </tr>
                            <?php
                                $total += $subtotal;
                            }
                            ?>
                        </tbody>
                    </table>

                
                    <div class="d-flex justify-content-between align-items-center border-top border-2 border-secondary pt-3 mt-3">
                        <span class="cart-payable-row">Amount Payable:</span>
                        <span class="cart-payable-amount">₱<?= number_format($total, 2); ?></span>
                    </div>
                </div>

                <input type="hidden" name="redirect" value="view_cart.php" />

                <!-- Action Controls Bar -->
                <div class="cart-actions-bar">
                    <a href="shop.php" class="cart-btn-outline">&larr; Add More Items</a>
                    
                    <div class="d-flex gap-3 align-items-center">
                        <button type="submit" class="cart-btn-dark">Update Cart</button>
                        <a href="checkout.php" class="cart-btn-checkout">Checkout &rarr;</a>
                    </div>
                </div>
            </form>
        <?php
        } else {
            echo '<div class="text-center py-5">';
            echo '<i class="fa-solid fa-cart-shopping fa-3x mb-3" style="color: #743014; opacity: 0.5;"></i>';
            echo '<h3 style="font-family: \'Aqila\', serif; color: var(--text-brown);">Your Cart is Empty</h3>';
            echo '<p style="font-family: \'Alinore\', sans-serif; color: #78716C; font-size: 1.2rem;">Explore our single-origin coffees and drip accessories.</p>';
            echo '<a href="shop.php" class="cart-shop-btn mt-3 d-inline-block" style="background:#4D6443; color:#FFF; font-family:\'Alinore\'; font-size:1.3rem; padding:12px 36px; border-radius:30px; text-decoration:none;">Start Shopping</a>';
            echo '</div>';
        }

   
        ?>
    </div>
    
</div>
<?php
include('./includes/footer.php');
?>

