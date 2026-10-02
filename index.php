<?php
// index.php - Infinite Curved Orbit Hero Landing Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roast & Grind | Specialty Coffee & Gear</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="includes/style/style.css">
</head>
<body class="hero-body">

<div class="hero-wrapper" id="heroWrapper">
    <!-- Top Navigation Bar -->
    <header class="hero-top-bar">
        <i class="fa-solid fa-star hero-star"></i>
        <a href="index.php" class="hero-brand">R&G</a>
      
        <i class="fa-solid fa-star hero-star"></i>
    </header>

    <!-- Main Slogan -->
    <h1 class="hero-title">
        WHERE THE PERFECT ROAST MEETS<br>YOUR DAILY GRIND
    </h1>

    <!-- Right-side Description -->
    <div class="hero-subtext">
        Ethically sourced single-origin beans and precision drip gear designed to elevate your morning ritual, one cup at a time.
    </div>

    <!-- The Infinite Curved Carousel Stage -->
    <div class="infinite-stage" id="carouselStage">
        <!-- 6 Items (2 Sets of the 3 Categories for Seamless Infinite Looping) -->
        
        <!-- Set 1 -->
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

        <!-- Set 2 (Duplicates for infinite continuity) -->
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

  <!-- The Brown Arch with Text Hugging the Top Crest -->
    <div class="hero-arch-container">
        <div class="hero-arch-shape">
            <svg class="hero-curved-svg" viewBox="0 0 800 500">
                <!-- Arc lifted to Y=70 at the crest so it hugs the top edge perfectly -->
                <path id="archCurve" d="M 50,440 A 350,370 0 0,1 750,440" fill="none" />
                <text>
                    <textPath href="#archCurve" startOffset="50%" text-anchor="middle">
                        ROAST AND GRIND
                    </textPath>
                </text>
            </svg>
        </div>
    </div>

    <!-- Explore Shop CTA Button -->
    <a href="shop.php" class="hero-cta-btn">
        Explore Shop
    </a>
</div>

<!-- =========================================================
     INFINITE ORBIT SCROLL ENGINE (Pure Vanilla JS)
========================================================= -->
<script>
    const items = document.querySelectorAll('.orbit-item');
    const totalItems = items.length; // 6 items
    let targetAngle = 0;
    let currentAngle = 0;
    let isHovered = false;

    // Listen for wheel events anywhere on the screen
    window.addEventListener('wheel', (e) => {
        // Scrolling down advances items to the left; scrolling up moves to the right
        targetAngle -= e.deltaY * 0.0018;
    }, { passive: true });

    // Pause momentum on hover
    items.forEach(item => {
        item.addEventListener('mouseenter', () => isHovered = true);
        item.addEventListener('mouseleave', () => isHovered = false);
    });

    // Touch Support for mobile trackpads / touchscreens
    let touchStartX = 0;
    window.addEventListener('touchstart', (e) => touchStartX = e.touches[0].clientX);
    window.addEventListener('touchmove', (e) => {
        const delta = touchStartX - e.touches[0].clientX;
        targetAngle -= delta * 0.003;
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    function render() {
        // Smooth physics interpolation (Lerp for silky inertia)
        if (!isHovered) {
            currentAngle += (targetAngle - currentAngle) * 0.08;
        }

        const angleStep = (2 * Math.PI) / totalItems; // 60 degrees apart

        items.forEach((item, index) => {
            const angle = currentAngle + (index * angleStep);
            
            // Normalize relative angle to range [-PI, PI]
            let relAngle = ((angle % (2 * Math.PI)) + 3 * Math.PI) % (2 * Math.PI) - Math.PI;

            // Geometry along the curved dome:
            // X position: ranges across the screen (50% is center)
            const x = 50 + 38 * Math.sin(relAngle); 
            
            // Y position: curved dome apex at center (42% viewport height)
            const y = 92 - 52 * Math.cos(relAngle); 

            // Scale: biggest at apex, smaller at horizons
            const scale = Math.max(0.6, 0.72 + 0.38 * Math.cos(relAngle));

            // Dynamic rotation tilt along the arc
            const tilt = (relAngle * 180 / Math.PI) * 0.45;

            // Calculate visibility (cull items that loop behind)
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

</body>
</html>
