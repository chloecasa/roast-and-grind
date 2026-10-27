<?php
// includes/left_menu.php - Dedicated Left Slide-Out Navigation Menu
$user_role = $_SESSION['user_role'] ?? '';
$is_logged_in = isset($_SESSION['user_id']);
?>

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
            <a href="/roast-and-grind/admin/users.php" class="left-menu-link">Manage Users</a> <!-- ADDED THIS -->
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
