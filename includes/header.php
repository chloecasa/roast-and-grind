<?php
// includes/header.php - Shared Boutique Header for Admin & Internal Pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    <link rel="stylesheet" href="/roast-and-grind/includes/style/style.css">
</head>
<body style="background-color: var(--bg-cream); min-height: 100vh;">

<!-- Top Boutique Navigation Bar (Matches the Shop Page) -->
<header class="store-top-bar mb-4">
    <!-- Far Left Star -->
    <i class="fa-solid fa-star store-star"></i>

    <!-- Centered Nav Cluster -->
    <div class="store-center-nav">
        <a href="/roast-and-grind/shop.php" class="store-nav-link">Storefront</a>
        <a href="/roast-and-grind/index.php" class="store-brand">R&G</a>
        <a href="/roast-and-grind/item/index.php" class="store-nav-link">Manage Products</a>
    </div>

    <!-- Far Right Star -->
    <i class="fa-solid fa-star store-star"></i>
</header>

<div class="container mb-5">
