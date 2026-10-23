<?php
// includes/header.php - Clean Header with Left Star, Menu Button, Centered Logo, and Cart
require_once __DIR__ . "/session.php";

$cartCount = (isset($_SESSION['cart_products']) && is_array($_SESSION['cart_products'])) 
             ? count($_SESSION['cart_products']) 
             : 0;

$user_role = $_SESSION['user_role'] ?? '';
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = (strpos($_SERVER['REQUEST_URI'], '/item/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/user/') !== false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roast & Grind | Specialty Coffee</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom Theme Stylesheet -->
    <link rel="stylesheet" href="/roast-and-grind/includes/style/style.css?v=<?= time(); ?>">
</head>
<body class="<?= $is_admin ? 'admin-page' : 'store-wrapper'; ?>" style="background-color: var(--bg-cream); min-height: 100vh; display: flex; flex-direction: column;">

<!-- Top Boutique Navigation Bar -->
<header class="store-top-bar mb-4">
    <!-- Left Group: Star + Menu Button with gap -->
    <div class="store-left-menu-group">
        <i class="fa-solid fa-star store-star"></i>
        <button type="button" class="menu-trigger-btn" data-bs-toggle="offcanvas" data-bs-target="#leftMenuDrawer" aria-controls="leftMenuDrawer">
            <i class="fa-solid fa-bars me-1"></i> MENU
        </button>
    </div>

    <!-- Center: Perfectly Centered R&G Logo -->
    <div class="store-center-nav">
        <a href="/roast-and-grind/index.php" class="store-brand">R&G</a>
    </div>

    <!-- Right Side: See Cart Items Button + Star -->
    <div class="store-right-cart-group">
        <a href="/roast-and-grind/view_cart.php" class="figma-cart-btn" title="View Cart">
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

<!-- =========================================================
     LEFT SLIDE-OUT NAVIGATION MENU DRAWER
========================================================= -->
<div class="offcanvas offcanvas-start left-menu-drawer" tabindex="-1" id="leftMenuDrawer" aria-labelledby="leftMenuTitle">
    <div class="left-menu-header">
        <h3 class="left-menu-title" id="leftMenuTitle">Navigation</h3>
        <button type="button" class="left-menu-close" data-bs-dismiss="offcanvas" aria-label="Close">[CLOSE]</button>
    </div>

    <div class="left-menu-body">
        <a href="/roast-and-grind/index.php" class="left-menu-link">Home</a>
        <a href="/roast-and-grind/shop.php" class="left-menu-link">Storefront</a>

        <?php if ($user_role === 'admin'): ?>
            <hr style="border-color: rgba(255,255,255,0.2); margin: 5px 0;">
            <a href="/roast-and-grind/admin/dashboard.php" class="left-menu-link">Dashboard</a>
            <a href="/roast-and-grind/item/index.php" class="left-menu-link">Manage Products</a>
            <a href="/roast-and-grind/admin/orders.php" class="left-menu-link">Manage Orders</a>
             <a href="/roast-and-grind/admin/users.php" class="left-menu-link">Manage Users</a> 
        <?php endif; ?>

        <?php if ($is_logged_in && $user_role !== 'admin'): ?>
            <hr style="border-color: rgba(255,255,255,0.2); margin: 5px 0;">
            <a href="/roast-and-grind/user/orders.php" class="left-menu-link">My Orders</a>
            <a href="/roast-and-grind/user/profile.php" class="left-menu-link">My Profile</a>
        <?php endif; ?>
    </div>

    <!-- Bottom Action Button: Login (Green) / Logout (Red) -->
    <div class="left-menu-footer">
        <?php if ($is_logged_in): ?>
            <a href="/roast-and-grind/user/logout.php" class="left-menu-auth-btn logout-btn">Log Out</a>
        <?php else: ?>
            <a href="/roast-and-grind/user/login.php" class="left-menu-auth-btn login-btn">Log In</a>
        <?php endif; ?>
    </div>
</div>

<div class="container mb-5" style="flex: 1 0 auto;">
    

<!-- Include Left Navigation Menu Drawer -->
<?php include_once __DIR__ . "/left_menu.php"; ?>
