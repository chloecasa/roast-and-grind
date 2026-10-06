<?php
// includes/header.php - Shared Boutique Header
require_once __DIR__ . "/session.php";

// Count by NUMBER OF UNIQUE ITEMS
$cartCount = (isset($_SESSION['cart_products']) && is_array($_SESSION['cart_products'])) 
             ? count($_SESSION['cart_products']) 
             : 0;

// Automatically detect if we are on an Admin page (inside /item/ or /admin/)
$is_admin = (strpos($_SERVER['REQUEST_URI'], '/item/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false);
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
<!-- Automatically assigns store-wrapper to user pages so the -84% centering applies! -->
<body class="<?= $is_admin ? 'admin-page' : 'store-wrapper'; ?>" style="background-color: var(--bg-cream); min-height: 100vh;">

<!-- Top Boutique Navigation Bar -->
<header class="store-top-bar mb-4">
    <!-- Far Left Star -->
    <i class="fa-solid fa-star store-star"></i>

    <!-- Centered Nav Links -->
    <div class="store-center-nav">
        <a href="/roast-and-grind/item/index.php" class="store-nav-link store-nav-left">Manage Products</a>
        <a href="/roast-and-grind/index.php" class="store-brand">R&G</a>
 
        <div class="store-nav-right">
            <?php if ($is_admin): ?>
                <a href="/roast-and-grind/shop.php" class="store-nav-link">Storefront</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/roast-and-grind/user/logout.php" class="store-nav-link">Logout</a>
            <?php else: ?>
                <a href="/roast-and-grind/user/login.php" class="store-nav-link">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Side: Figma "See Cart Items" Button + Star -->
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

        <!-- Far Right Star -->
        <i class="fa-solid fa-star store-star"></i>
    </div>
</header>

<div class="container mb-5">
