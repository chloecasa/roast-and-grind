<?php

require_once "includes/session.php";
require_once "includes/config.php";


$cartCount = (isset($_SESSION['cart_products']) && is_array($_SESSION['cart_products'])) 
             ? count($_SESSION['cart_products']) 
             : 0;

$returnQuery = $_GET;
$returnQuery['cart'] = 'open';
$returnUrl   = 'shop.php?' . http_build_query($returnQuery);
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT 
            i.item_id,
            i.item_name,
            i.description,
            i.origin,
            i.category,
            i.sell_price,
            i.img_path,
            s.quantity AS qty
        FROM item i
        INNER JOIN stock s USING (item_id)
        WHERE 1=1";

$params = [];
$types  = "";

if (!empty($search)) {
    $sql .= " AND (i.item_name LIKE ? OR i.description LIKE ? OR i.origin LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types   .= "sss";
}

if (!empty($category) && $category !== "All") {
    $sql .= " AND i.category = ?";
    $params[] = $category;
    $types   .= "s";
}

$sql .= " ORDER BY i.item_id ASC";
$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


function stockBadge($qty, $extraStyle = '')
{
    if ($qty <= 0) {
        return '<span class="badge text-bg-danger" style="' . $extraStyle . '">Out of Stock</span>';
    }
    if ($qty <= 5) {
        return '<span class="badge" style="background-color:#fd7e14;color:#fff;' . $extraStyle . '">Low Stock</span>';
    }
    return '<span class="badge text-bg-success" style="' . $extraStyle . '">In Stock</span>';
}


    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Roast & Grind</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="includes/style/style.css">
</head>
<body class="store-wrapper">

    <header class="store-top-bar">
        <div class="store-left-menu-group">
            <i class="fa-solid fa-star store-star"></i>
            <button type="button" class="menu-trigger-btn" data-bs-toggle="offcanvas" data-bs-target="#leftMenuDrawer" aria-controls="leftMenuDrawer">
                <i class="fa-solid fa-bars me-1"></i> MENU
            </button>
        </div>

        <div class="store-center-nav">
            <a href="index.php" class="store-brand">R&G</a>
        </div>

        <div class="store-right-cart-group">
            <a href="view_cart.php" class="figma-cart-btn" title="View Cart"
               data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" aria-controls="cartDrawer">
                <div class="figma-cart-pill">
                    <span>See cart items</span>
                    <i class="fa-solid fa-cart-shopping figma-cart-icon"></i>
                </div>
                <div class="figma-cart-circle">
                    <?= $cartCount; ?>
                </div>
            </a>
            <i class="fa-solid fa-star store-star"></i>
        </div>
    </header>
   
    <h1 class="store-title">
        Explore our full shelf and<br>brew with intention
    </h1>

    <div class="filter-bar-container">
        <?php include("includes/alert.php"); ?>
    </div>

   
    <div class="filter-bar-container">
        <form method="GET" action="shop.php" class="row g-3 align-items-center">
         
            <div class="col-lg-5 col-md-12">
                <input type="text" 
                       name="search" 
                       class="form-control shop-search-input" 
                       placeholder="Search coffee by name, origin, or notes..." 
                       value="<?= htmlspecialchars($search); ?>">
            </div>

           
            <div class="col-lg-3 col-md-4">
                <select name="category" class="form-select shop-category-select">
                    <option value="All" <?= ($category === 'All' || empty($category)) ? 'selected' : ''; ?>>All Categories</option>
                    <option value="Coffee Beans" <?= ($category === 'Coffee Beans') ? 'selected' : ''; ?>>Coffee Beans</option>
                    <option value="Drip Bags" <?= ($category === 'Drip Bags') ? 'selected' : ''; ?>>Drip Bags</option>
                    <option value="Brewing Gear" <?= ($category === 'Brewing Gear') ? 'selected' : ''; ?>>Brewing Gear</option>
                </select>
            </div>

         
            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn shop-filter-btn">Filter</button>
            </div>

           
            <div class="col-lg-2 col-md-4">
                <a href="shop.php" class="shop-reset-btn">Reset</a>
            </div>
        </form>
    </div>

       <div class="shop-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="shop-grid-container">
                <?php while ($item = mysqli_fetch_assoc($result)): 
                    $imgSrc = "item/images/" . $item['img_path'];
                    if (!file_exists($imgSrc) || empty($item['img_path'])) {
                        $imgSrc = "https://placehold.co/547x649/EDEAE1/743014?text=" . urlencode($item['item_name']);
                    }

                    $fullDesc   = trim($item['description']);
                    $charLimit  = 110;
                    $isLong     = mb_strlen($fullDesc) > $charLimit;
                    $shortDesc  = $isLong ? mb_substr($fullDesc, 0, $charLimit) . '...' : $fullDesc;
                ?>
               
                    <div class="shop-card-pure">
                        <img src="<?= $imgSrc; ?>" alt="<?= htmlspecialchars($item['item_name']); ?>" class="shop-pure-img">
                        <?= stockBadge((int) $item['qty'], 'position:absolute;top:12px;left:12px;z-index:2;font-size:.9rem;'); ?>
                        <button type="button" 
                                class="shop-view-btn" 
                                data-bs-toggle="offcanvas" 
                                data-bs-target="#drawer-<?= $item['item_id']; ?>">
                            View Info
                        </button>
                    </div>

                    <!-- Offcanvas Drawer -->
                    <div class="offcanvas offcanvas-end product-drawer" 
                         tabindex="-1" 
                         id="drawer-<?= $item['item_id']; ?>" 
                         aria-labelledby="drawerLabel-<?= $item['item_id']; ?>">
                        
                       
                        <?php
            
                        $gallery_res = mysqli_query($conn, "SELECT img_path FROM item_images WHERE item_id = " . intval($item['item_id']));
                        $gallery_images = [];
                        
               
                        $gallery_images[] = $imgSrc;
                        
                        while ($g_row = mysqli_fetch_assoc($gallery_res)) {
                            $g_path = "item/images/" . $g_row['img_path'];
                            if (file_exists($g_path)) {
                                $gallery_images[] = $g_path;
                            }
                        }
                        ?>

                    <div class="drawer-img-box position-relative">
                            <div id="carousel-<?= $item['item_id']; ?>" 
                                 class="carousel slide h-100 w-100" 
                                 data-bs-touch="true" 
                                 data-bs-interval="false">
                                
                                <div class="carousel-inner h-100 w-100">
                                    <?php foreach ($gallery_images as $idx => $src): ?>
                                        <div class="carousel-item h-100 w-100 <?= $idx === 0 ? 'active' : ''; ?>">
                  
                                            <img src="<?= $src; ?>" alt="Product image <?= $idx + 1; ?>" class="drawer-carousel-img">
                                        </div>
                                    <?php endforeach; ?>
                                </div>

          
                                <?php if (count($gallery_images) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $item['item_id']; ?>" data-bs-slide="prev">
                                        <i class="fa-solid fa-chevron-left drawer-nav-arrow"></i>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $item['item_id']; ?>" data-bs-slide="next">
                                        <i class="fa-solid fa-chevron-right drawer-nav-arrow"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h2 class="drawer-title" id="drawerLabel-<?= $item['item_id']; ?>">
                            <?= htmlspecialchars($item['item_name']); ?>
                        </h2>

                       <div class="drawer-info-row">
                            <span class="drawer-category"><?= htmlspecialchars($item['category']); ?></span>
                            <span class="drawer-from">from</span>
                            <span class="drawer-location"><?= htmlspecialchars($item['origin']); ?></span>
                        </div>

                  
                        <div class="drawer-desc-box">
                            <span><?= htmlspecialchars($shortDesc); ?></span>
                            <?php if ($isLong): ?>
                                <button type="button" 
                                        class="desc-read-more-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#descModal-<?= $item['item_id']; ?>">
                                    ...read more
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="drawer-action-container">
                            <div class="drawer-left-col">
                                <div class="drawer-price-line">
                                    <span class="drawer-price-label">price:</span>
                                    <span class="drawer-price-value">₱<?= number_format($item['sell_price'], 2); ?></span>
                                </div>
                                <div class="drawer-stock-line">
                                    <?= stockBadge((int) $item['qty']); ?>
                                    <span class="ms-2 fw-bold"><?= (int) $item['qty']; ?></span>
                                    <span class="ms-1">available</span>
                                </div>
                            </div>

                      
                            <form method="POST" action="cart_update.php" class="drawer-right-col">
                                <input type="hidden" name="type" value="add">
                                <input type="hidden" name="item_id" value="<?= $item['item_id']; ?>">
                                <input type="hidden" name="item_qty" id="input-qty-<?= $item['item_id']; ?>" value="1">
                                <input type="hidden" name="redirect" value="<?= htmlspecialchars($returnUrl); ?>">

                       
                                <div class="drawer-stepper">
                                    <button type="button" class="stepper-btn" onclick="stepQty(<?= $item['item_id']; ?>, -1)">-</button>
                                    <span class="stepper-val" id="qty-<?= $item['item_id']; ?>">1</span>
                                    <button type="button" class="stepper-btn" onclick="stepQty(<?= $item['item_id']; ?>, 1, <?= $item['qty']; ?>)">+</button>
                                </div>

                             
                                <?php if ($item['qty'] > 0): ?>
                                    <button type="submit" class="drawer-add-btn">
                                        add to cart
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="drawer-add-btn disabled" disabled>
                                        out of stock
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                        <!-- Reviews Section -->
                        <?php
                        
                        $rev_q = mysqli_query($conn, "SELECT r.*, c.first_name, c.last_name FROM reviews r JOIN customers c ON r.user_id = c.user_id WHERE r.item_id = " . intval($item['item_id']) . " ORDER BY r.created_at DESC");
                        
                        $has_bought = false;
                        $my_review = "";
                        
                        if (isset($_SESSION['user_id'])) {
                            $uid = (int)$_SESSION['user_id'];
                            $iid = (int)$item['item_id'];
                            
                            
                            $chk_p = mysqli_query($conn, "SELECT 1 FROM orders o JOIN order_items oi ON o.order_id = oi.order_id WHERE o.user_id = $uid AND oi.item_id = $iid AND o.status = 'Delivered' LIMIT 1");
                            if (mysqli_num_rows($chk_p) > 0) $has_bought = true;
                            
                            
                            $chk_r = mysqli_query($conn, "SELECT review_text FROM reviews WHERE user_id = $uid AND item_id = $iid");
                            if ($r_row = mysqli_fetch_assoc($chk_r)) {
                                $my_review = $r_row['review_text'];
                            }
                        }
                        ?>
                        
                        <div class="mt-4 pt-4 border-top border-secondary">
                            <h4 style="font-family: 'Aqila', serif; color: #FED7AA; font-size: 1.5rem; margin-bottom: 16px;">Customer Reviews</h4>
                            
                            
                            <?php if ($has_bought): ?>
                                <form action="review_store.php" method="POST" class="mb-4 p-3 rounded" style="background: rgba(0,0,0,0.15);">
                                    <input type="hidden" name="item_id" value="<?= $item['item_id']; ?>">
                                    <input type="hidden" name="redirect" value="shop.php?cart=open">
                                    <label class="form-label" style="font-family: 'Alinore'; color: #FDBA74; font-size: 1.1rem; text-transform: uppercase;">
                                        <?= $my_review !== '' ? 'Update Your Review' : 'Write a Review'; ?>
                                    </label>
                                    <textarea name="review_text" rows="2" class="form-control mb-2" style="background: #FAF7F2; border-radius: 12px; font-family: 'Alinore'; font-size: 1.1rem; border:none; resize:none;" placeholder="Tell us what you think..." required><?= htmlspecialchars($my_review); ?></textarea>
                                    <button type="submit" style="background: transparent; color: #FED7AA; border: 1px solid #FED7AA; border-radius: 20px; padding: 6px 16px; font-family: 'Alinore'; font-size: 1rem; text-transform: uppercase; transition: all 0.2s ease;">
                                        <?= $my_review !== '' ? 'Save Changes' : 'Submit Review'; ?>
                                    </button>
                                </form>
                            <?php elseif (isset($_SESSION['user_id'])): ?>
                                <p style="font-family: 'Alinore'; color: #78716C; font-size: 1.1rem;">You can review this item once an order containing it has been delivered.</p>
                            <?php else: ?>
                                <p style="font-family: 'Alinore'; color: #78716C; font-size: 1.1rem;">Please <a href="user/login.php" style="color: #FED7AA;">log in</a> to leave a review.</p>
                            <?php endif; ?>

                            <
                            <div>
                                <?php if (mysqli_num_rows($rev_q) > 0): ?>
                                    <?php while($rev = mysqli_fetch_assoc($rev_q)): ?>
                                        <div class="mb-3">
                                            <div style="font-family: 'Alinore'; font-size: 1.1rem; color: #FDBA74; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">
                                                <?= htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']); ?>
                                            </div>
                                            <div style="font-family: 'Alinore'; font-size: 1.15rem; color: #E6D8CC; line-height: 1.4; background: rgba(0,0,0,0.1); padding: 10px 14px; border-radius: 8px;">
                                                <?= nl2br(htmlspecialchars($rev['review_text'])); ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p style="font-family: 'Alinore'; color: #78716C; font-size: 1.1rem;">No reviews yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <button type="button" class="drawer-close-link" data-bs-dismiss="offcanvas" aria-label="Close">
                            [close]
                        </button>
                    </div>

           
                    <div class="modal fade" id="descModal-<?= $item['item_id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content desc-modal-content">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-secondary">
                                    <h4 class="mb-0" style="font-family: 'Aqila', serif; color: #FED7AA; font-size: 1.8rem;">
                                        <?= htmlspecialchars($item['item_name']); ?>
                                    </h4>
                                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                                        [CLOSE]
                                    </button>
                                </div>
                                <div class="modal-body p-0">
                                    <p style="font-family: 'Alinore', sans-serif; font-size: 1.2rem; line-height: 1.6; color: #E6D8CC; text-align: justify;">
                                        <?= nl2br(htmlspecialchars($fullDesc)); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

               

                <?php endwhile; ?>
            <?php else: ?>
             
            <div class="shop-empty-state">
                <h3 class="shop-empty-title">NO PRODUCTS FOUND MATCHING YOUR SEARCH.</h3>
                <a href="shop.php" class="shop-empty-btn">VIEW ALL PRODUCTS</a>
            </div>
            <?php endif; ?>
        </div>
    </div>


<?php include("includes/footer.php"); ?>
<?php 
include("includes/left_menu.php");
include("includes/cart_drawer.php"); ?>


<script>
function stepQty(id, delta, maxStock = 999) {
    const el = document.getElementById('qty-' + id);
    const hiddenInput = document.getElementById('input-qty-' + id);
    
    let val = parseInt(el.innerText) + delta;
    if (val < 1) val = 1;
    if (val > maxStock) val = maxStock;

    el.innerText = val;
    if (hiddenInput) {
        hiddenInput.value = val;
    }
}
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
(function () {
    const el = document.getElementById('cartDrawer');
    if (!el) return;
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(el);

    
    el.addEventListener('show.bs.offcanvas',   () => { document.body.classList.add('cart-open');    sessionStorage.setItem('cartOpen', '1'); });
    el.addEventListener('hidden.bs.offcanvas', () => { document.body.classList.remove('cart-open'); sessionStorage.removeItem('cartOpen'); });

    
    const params = new URLSearchParams(window.location.search);
    if (params.get('cart') === 'open' || sessionStorage.getItem('cartOpen') === '1') {
        drawer.show();
    }
    if (params.has('cart')) {
        params.delete('cart');
        const qs = params.toString();
        history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : ''));
    }
})();
</script>
</body>
</html>