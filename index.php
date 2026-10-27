<?php
// index.php - Infinite Curved Orbit Hero Landing Page
require_once "includes/session.php";

$cartCount = (isset($_SESSION['cart_products']) && is_array($_SESSION['cart_products'])) 
             ? count($_SESSION['cart_products']) 
             : 0;

$user_role = $_SESSION['user_role'] ?? '';
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roast & Grind | Specialty Coffee & Gear</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="includes/style/style.css?v=<?= time(); ?>">
</head>
<body class="hero-body">

<div class="hero-wrapper" id="heroWrapper">
    
    
    <header class="hero-top-bar">
        <div class="store-left-menu-group">
            <i class="fa-solid fa-star store-star"></i>
            <button type="button" class="menu-trigger-btn" data-bs-toggle="offcanvas" data-bs-target="#leftMenuDrawer" aria-controls="leftMenuDrawer">
                <i class="fa-solid fa-bars me-1"></i> MENU
            </button>

            <?php if (!$is_logged_in): ?>
            <a href="user/login.php" class="menu-trigger-btn login-trigger-btn">
            <i class="fa-solid fa-right-to-bracket me-1"></i> LOG IN
            </a>
            <?php endif; ?>
        </div>

        <a href="index.php" class="store-brand" style="position: absolute; left: 50%; transform: translateX(-50%); font-family: 'Aqila', serif; color: var(--text-brown);">R&G</a>

        <form class="search-box" role="search" action="shop.php" method="get">
            <input class="text-wrapper"
            type="text"
            name="search"
            placeholder="Search coffee..."
            aria-label="Search coffee"
            autocomplete="off"
            maxlength="100">
            <button class="search" type="submit" aria-label="Submit coffee search">
            <img src="assets/img/search.svg" alt="">
            </button>
        </form>
        
           
            <i class="fa-solid fa-star store-star"></i>
       
    </header>

  
    <h1 class="hero-title">
        WHERE THE PERFECT ROAST MEETS<br>YOUR DAILY GRIND
    </h1>

    
    <div class="hero-subtext">
        Ethically sourced single-origin beans and precision drip gear designed to elevate your morning ritual, one cup at a time.
    </div>

   
    <div class="infinite-stage" id="carouselStage">
        <a href="shop.php?category=Brewing+Gear" class="orbit-item" data-index="0" title="Explore Brewing Gear">
            <img src="assets/hero/moka_pot.png" alt="Brewing Gear" onerror="this.src='https://placehold.co/400x500/743014/FFF5EA?text=Brewing+Gear'">
            <span class="orbit-label">Brewing Gear</span>
        </a>
        <a href="shop.php?category=Drip+Bags" class="orbit-item" data-index="1" title="Explore Drip Bags">
            <img src="assets/hero/drip_bag.png" alt="Drip Bags" onerror="this.src='https://placehold.co/450x550/743014/FFF5EA?text=Drip+Bags'">
            <span class="orbit-label">Drip Bags</span>
        </a>
        <a href="shop.php?category=Coffee+Beans" class="orbit-item" data-index="2" title="Explore Coffee Beans">
            <img src="assets/hero/coffee_beans.png" alt="Coffee Beans" onerror="this.src='https://placehold.co/400x400/743014/FFF5EA?text=Coffee+Beans'">
            <span class="orbit-label">Coffee Beans</span>
        </a>
        <a href="shop.php?category=Brewing+Gear" class="orbit-item" data-index="3" title="Explore Brewing Gear">
            <img src="assets/hero/moka_pot.png" alt="Brewing Gear" onerror="this.src='https://placehold.co/400x500/743014/FFF5EA?text=Brewing+Gear'">
            <span class="orbit-label">Brewing Gear</span>
        </a>
        <a href="shop.php?category=Drip+Bags" class="orbit-item" data-index="4" title="Explore Drip Bags">
            <img src="assets/hero/drip_bag.png" alt="Drip Bags" onerror="this.src='https://placehold.co/450x550/743014/FFF5EA?text=Drip+Bags'">
            <span class="orbit-label">Drip Bags</span>
        </a>
        <a href="shop.php?category=Coffee+Beans" class="orbit-item" data-index="5" title="Explore Coffee Beans">
            <img src="assets/hero/coffee_beans.png" alt="Coffee Beans" onerror="this.src='https://placehold.co/400x400/743014/FFF5EA?text=Coffee+Beans'">
            <span class="orbit-label">Coffee Beans</span>
        </a>
    </div>

   
    <div class="hero-arch-container">
        <div class="hero-arch-shape">
            <svg class="hero-curved-svg" viewBox="0 0 800 500">
                <path id="archCurve" d="M 50,440 A 350,370 0 0,1 750,440" fill="none" />
                <text>
                    <textPath href="#archCurve" startOffset="50%" text-anchor="middle">
                        ROAST AND GRIND
                    </textPath>
                </text>
            </svg>
        </div>
    </div>

    <a href="shop.php" class="hero-cta-btn">
        Explore Shop
    </a>
</div>


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

    <div class="left-menu-footer">
        <?php if ($is_logged_in): ?>
            <a href="/roast-and-grind/user/logout.php" class="left-menu-auth-btn logout-btn">Log Out</a>
        <?php else: ?>
            <a href="/roast-and-grind/user/login.php" class="left-menu-auth-btn login-btn">Log In</a>
        <?php endif; ?>
    </div>
</div>


<script>
    const items = document.querySelectorAll('.orbit-item');
    const totalItems = items.length;
    let targetAngle = 0;
    let currentAngle = 0;
    let isHovered = false;

    window.addEventListener('wheel', (e) => {
        targetAngle -= e.deltaY * 0.0018;
    }, { passive: true });

    items.forEach(item => {
        item.addEventListener('mouseenter', () => isHovered = true);
        item.addEventListener('mouseleave', () => isHovered = false);
    });

    let touchStartX = 0;
    window.addEventListener('touchstart', (e) => touchStartX = e.touches[0].clientX);
    window.addEventListener('touchmove', (e) => {
        const delta = touchStartX - e.touches[0].clientX;
        targetAngle -= delta * 0.003;
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    function render() {
        if (!isHovered) {
            currentAngle += (targetAngle - currentAngle) * 0.08;
        }
        const angleStep = (2 * Math.PI) / totalItems;
        items.forEach((item, index) => {
            const angle = currentAngle + (index * angleStep);
            let relAngle = ((angle % (2 * Math.PI)) + 3 * Math.PI) % (2 * Math.PI) - Math.PI;
            const x = 50 + 38 * Math.sin(relAngle); 
            const y = 92 - 52 * Math.cos(relAngle); 
            const scale = Math.max(0.6, 0.72 + 0.38 * Math.cos(relAngle));
            const tilt = (relAngle * 180 / Math.PI) * 0.45;
            const isVisible = Math.cos(relAngle) > -0.2;
            const opacity = Math.max(0, Math.min(1, (Math.cos(relAngle) + 0.2) * 2));
            const zIndex = Math.round(scale * 100);

            if (isVisible) {
                item.style.display = 'block';
                item.style.left = `${x}vw`;
                item.style.top = `${y}vh`;
                item.style.transform = `translate(-50%, -50%) rotate(${tilt}deg) scale(${scale})`;
                item.style.opacity = opacity;
                item.style.zIndex = zIndex;
            } else {
                item.style.display = 'none';
            }
        });
        requestAnimationFrame(render);
    }
    render();
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
