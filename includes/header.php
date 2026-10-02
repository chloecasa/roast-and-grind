<?php
// includes/header.php
include_once __DIR__ . '/session.php';
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
</head>
<body class="bg-light">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/roast-and-grind/index.php">
            <i class="fa-solid fa-mug-hot me-2 text-warning"></i>Roast & Grind
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/roast-and-grind/index.php">Storefront</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="/roast-and-grind/item/index.php">
                        <i class="fa-solid fa-boxes-stacked me-1"></i>Manage Products
                    </a>
                </li>

                <li class="nav-item">
                    <?php
                    // total quantity of items currently in the cart
                    $cart_count = 0;
                    if (isset($_SESSION['cart_products'])) {
                        foreach ($_SESSION['cart_products'] as $cart_itm) {
                            $cart_count += $cart_itm['item_qty'];
                        }
                    }
                    ?>
                    <a class="nav-link" href="/roast-and-grind/view_cart.php">
                        <i class="fa-solid fa-cart-shopping me-1"></i>Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="badge bg-warning text-dark"><?= $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mb-5">
