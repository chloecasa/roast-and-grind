<?php
// includes/footer.php - Boutique Full-Width Footer
?>
</div> <!-- Closes .container opened in header.php (if present) -->

<!-- =========================================================
     BOUTIQUE STORE FOOTER (Exact Figma Layout)
========================================================= -->
<footer class="boutique-footer">
    <div class="footer-top-row">
        <!-- Top Left: Slogan -->
        <div class="footer-slogan">
            WHERE THE PERFECT ROAST MEETS<br>YOUR DAILY GRIND
        </div>

        <!-- Top Right: Navigation Links -->
        <div class="footer-nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/roast-and-grind/user/logout.php" class="footer-link">LOGOUT</a>
            <?php else: ?>
                <a href="/roast-and-grind/user/login.php" class="footer-link">LOG IN</a>
            <?php endif; ?>

            <a href="/roast-and-grind/shop.php#top" class="footer-link">SHOP</a>
        </div>
    </div>

    <!-- Center: Massive Brand Wordmark -->
    <div class="footer-huge-brand">
        ROAST & GRIND
    </div>

    <!-- Bottom Left: Copyright -->
    <div class="footer-bottom-line">
        &copy; <?= date('Y'); ?> Roast & Grind – Specialty Coffee & Gear
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
