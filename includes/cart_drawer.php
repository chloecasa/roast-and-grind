<?php
// includes/cart_drawer.php - Slide-out Cart Drawer (Exact Figma Specs)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

// Fixes the 4 undefined variable warnings
$returnUrl = $returnUrl ?? 'shop.php?cart=open';
?>


<link rel="stylesheet" href="includes/style/style.css?v=<?= time(); ?>">


<!-- =======================================================
     OFFCANVAS HTML CONTAINER
======================================================== -->
<div class="offcanvas offcanvas-end" 
     tabindex="-1" 
     id="cartDrawer" 
     aria-labelledby="cartDrawerTitle">
    
    <!-- 1. Sticky Top Header -->
    <div class="cart-header-box">
        <h2 class="cart-header-title" id="cartDrawerTitle">YOUR CART</h2>
        <button type="button" class="cart-header-close" data-bs-dismiss="offcanvas" aria-label="Close">[CLOSE]</button>
    </div>

    <!-- 2. Scrollable Middle Area -->
    <div class="cart-body-scroll">
        <?php 
        $grandTotal = 0;
        if (!empty($_SESSION['cart_products']) && is_array($_SESSION['cart_products'])): 
            foreach ($_SESSION['cart_products'] as $p_code => $c_item): 
                $sub = $c_item['item_price'] * $c_item['item_qty'];
                $grandTotal += $sub;

                // STRICTLY GET THE PRIMARY IMAGE FROM THE 'item' TABLE
                $img_q = mysqli_query($conn, "SELECT img_path FROM item WHERE item_id = " . intval($p_code));
                $img_row = mysqli_fetch_assoc($img_q);
                
                $c_img = "https://placehold.co/264x231/EDEAE1/743014?text=" . urlencode($c_item['item_name']);
                if ($img_row && !empty($img_row['img_path'])) {
                    $test_file = "item/images/" . $img_row['img_path'];
                    if (file_exists($test_file)) {
                        $c_img = $test_file;
                    }
                }
        ?>
            <!-- Single Cart Product Row -->
            <div class="cart-row-item">
                <!-- Fixed 170x160 Primary Image Thumbnail -->
                <div class="cart-img-frame">
                    <img src="<?= $c_img; ?>" alt="<?= htmlspecialchars($c_item['item_name']); ?>">
                </div>

                <!-- Product Details Column -->
                <div class="cart-details-col">
                    <h4 class="cart-item-name"><?= htmlspecialchars($c_item['item_name']); ?></h4>
                    
                    <div class="cart-item-unit-price">
                        PRICE: ₱<?= number_format($c_item['item_price'], 2); ?>
                    </div>

                    <!-- Quantity Stepper Row -->
                    <div class="cart-qty-line">
                        <span class="cart-qty-text">QUANTITY</span>
                        
                        <div class="cart-stepper-box">
                            <form method="POST" action="cart_update.php" style="display:inline; margin:0;">
                                <input type="hidden" name="redirect" value="<?= htmlspecialchars($returnUrl); ?>">
                                <input type="hidden" name="product_qty[<?= $p_code; ?>]" value="<?= max(1, $c_item['item_qty'] - 1); ?>">
                                <button type="submit" class="cart-stepper-btn">-</button>
                            </form>

                            <span class="cart-stepper-count"><?= $c_item['item_qty']; ?></span>

                            <form method="POST" action="cart_update.php" style="display:inline; margin:0;">
                                <input type="hidden" name="redirect" value="<?= htmlspecialchars($returnUrl); ?>">
                                <input type="hidden" name="product_qty[<?= $p_code; ?>]" value="<?= $c_item['item_qty'] + 1; ?>">
                                <button type="submit" class="cart-stepper-btn">+</button>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Link and Subtotal -->
                    <div class="cart-bottom-actions">
                        <form method="POST" action="cart_update.php" style="display:inline; margin:0;">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($returnUrl); ?>">
                            <input type="hidden" name="remove_code[]" value="<?= $p_code; ?>">
                            <button type="submit" class="cart-delete-link">[DELETE]</button>
                        </form>

                        <span class="cart-subtotal-text">
                            TOTAL ₱<?= number_format($sub, 2); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php 
            endforeach; 
        else: 
        ?>
            <!-- Empty Cart Notice -->
            <div class="cart-empty-box">
                <i class="fa-solid fa-cart-shopping fa-3x mb-3" style="color: #FED7AA; opacity: 0.5;"></i>
                <h4 style="font-family: 'Aqila', serif; color: #FED7AA;">Your Cart is Empty</h4>
                <p style="font-family: 'Alinore', sans-serif;">Add some coffee to see it here.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- 3. Sticky Bottom Footer -->
    <div class="cart-footer-box">
        <div class="cart-amount-line">
            <span>AMOUNT PAYABLE:</span>
            <span>₱<?= number_format($grandTotal, 2); ?></span>
        </div>

        <?php if (!empty($_SESSION['cart_products'])): ?>
            <a href="checkout.php" class="cart-checkout-pill">CHECKOUT</a>
        <?php else: ?>
            <button class="cart-checkout-pill" disabled style="opacity: 0.5; cursor: not-allowed;">CHECKOUT</button>
        <?php endif; ?>
    </div>
</div>
