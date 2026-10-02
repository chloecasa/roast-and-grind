<?php
// shop.php - Boutique Catalog Page
require_once "includes/session.php"; // Uses partner's session
require_once "includes/config.php";

// Count by NUMBER OF UNIQUE ITEMS (not total quantity)
$cartCount = (isset($_SESSION['cart_products']) && is_array($_SESSION['cart_products'])) 
             ? count($_SESSION['cart_products']) 
             : 0;

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Roast & Grind</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="includes/style/style.css">
</head>
<body class="store-wrapper">

   <!-- Top Navigation Bar (Figma Aligned) -->
    <header class="store-top-bar">
        <!-- Far Left Star -->
        <i class="fa-solid fa-star store-star"></i>

        <!-- Centered Nav Links -->
        <div class="store-center-nav">
            <a href="item/index.php" class="store-nav-link">Manage Products</a>
            <a href="index.php" class="store-brand">R&G</a>
            
        </div>

        <!-- Right Side: Figma "See Cart Items" Button + Right Star -->
        <div class="store-right-cart-group">
            <a href="view_cart.php" class="figma-cart-btn" title="View Cart">
                <!-- Green Pill (w-44 h-10 bg-stone-600 rounded-[20px]) -->
                <div class="figma-cart-pill">
                    <span>See cart items</span>
                    <!-- Orange/Red Cart Icon from Figma -->
                    <i class="fa-solid fa-cart-shopping figma-cart-icon"></i>
                </div>
                <!-- Overlapping Badge (size-8 bg-amber-900 rounded-full) -->
                <div class="figma-cart-circle">
                    <?= $cartCount; ?>
                </div>
            </a>

            <!-- Far Right Star -->
            <i class="fa-solid fa-star store-star"></i>
        </div>
    </header>
    <!-- Main Headline -->
    <h1 class="store-title">
        Explore our full shelf and<br>brew with intention
    </h1>

    <div class="filter-bar-container">
        <?php include("includes/alert.php"); ?>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="filter-bar-container">
        <form method="GET" action="shop.php" class="row g-3 align-items-center">
            <!-- Search Box -->
            <div class="col-lg-5 col-md-12">
                <input type="text" 
                       name="search" 
                       class="form-control shop-search-input" 
                       placeholder="Search coffee by name, origin, or notes..." 
                       value="<?= htmlspecialchars($search); ?>">
            </div>

            <!-- Categories Dropdown -->
            <div class="col-lg-3 col-md-4">
                <select name="category" class="form-select shop-category-select">
                    <option value="All" <?= ($category === 'All' || empty($category)) ? 'selected' : ''; ?>>All Categories</option>
                    <option value="Coffee Beans" <?= ($category === 'Coffee Beans') ? 'selected' : ''; ?>>Coffee Beans</option>
                    <option value="Drip Bags" <?= ($category === 'Drip Bags') ? 'selected' : ''; ?>>Drip Bags</option>
                    <option value="Brewing Gear" <?= ($category === 'Brewing Gear') ? 'selected' : ''; ?>>Brewing Gear</option>
                </select>
            </div>

            <!-- Filter Button -->
            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn shop-filter-btn">Filter</button>
            </div>

            <!-- Reset Button -->
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
                    <!-- Pure Image Card -->
                    <div class="shop-card-pure">
                        <img src="<?= $imgSrc; ?>" alt="<?= htmlspecialchars($item['item_name']); ?>" class="shop-pure-img">
                        <button type="button" 
                                class="shop-view-btn" 
                                data-bs-toggle="offcanvas" 
                                data-bs-target="#drawer-<?= $item['item_id']; ?>">
                            View Info
                        </button>
                    </div>

                    <!-- Offcanvas Drawer for this product -->
                    <div class="offcanvas offcanvas-end product-drawer" 
                         tabindex="-1" 
                         id="drawer-<?= $item['item_id']; ?>" 
                         aria-labelledby="drawerLabel-<?= $item['item_id']; ?>">
                        
                        <!-- 1. Multi-Image Swipeable Carousel with Left/Right Arrows -->
                        <?php
                        // Fetch all gallery photos for this product
                        $gallery_res = mysqli_query($conn, "SELECT img_path FROM item_images WHERE item_id = " . intval($item['item_id']));
                        $gallery_images = [];
                        
                        // First slide is always the primary image
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
                                            <!-- Direct image with cover styling (No padding div) -->
                                            <img src="<?= $src; ?>" alt="Product image <?= $idx + 1; ?>" class="drawer-carousel-img">
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Left & Right Arrows -->
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

                        <!-- ADD THIS DESCRIPTION BLOCK HERE: -->
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
                                    <span>in stock</span>
                                    <span class="ms-1 fw-bold"><?= $item['qty']; ?></span>
                                </div>
                            </div>

                           <!-- Form submitting EXACTLY what her cart_update.php expects -->
                            <form method="POST" action="cart_update.php" class="drawer-right-col">
                                <input type="hidden" name="type" value="add">
                                <input type="hidden" name="item_id" value="<?= $item['item_id']; ?>">
                                <input type="hidden" name="item_qty" id="input-qty-<?= $item['item_id']; ?>" value="1">
                                <input type="hidden" name="redirect" value="shop.php?<?= htmlspecialchars($_SERVER['QUERY_STRING']); ?>">

                                <!-- Quantity Stepper -->
                                <div class="drawer-stepper">
                                    <button type="button" class="stepper-btn" onclick="stepQty(<?= $item['item_id']; ?>, -1)">-</button>
                                    <span class="stepper-val" id="qty-<?= $item['item_id']; ?>">1</span>
                                    <button type="button" class="stepper-btn" onclick="stepQty(<?= $item['item_id']; ?>, 1, <?= $item['qty']; ?>)">+</button>
                                </div>

                                <!-- Add to Cart Button -->
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

                        <button type="button" class="drawer-close-link" data-bs-dismiss="offcanvas" aria-label="Close">
                            [close]
                        </button>
                    </div>

                    <!-- ADD THIS MODAL HERE: -->
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
                <!-- Centered Empty State with Balanced Button -->
            <div class="shop-empty-state">
                <h3 class="shop-empty-title">NO PRODUCTS FOUND MATCHING YOUR SEARCH.</h3>
                <a href="shop.php" class="shop-empty-btn">VIEW ALL PRODUCTS</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

<!-- Quantity Stepper Logic -->
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

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
