<?php
// includes/admin_check.php - FR-19: only users with the admin role may open order management pages.
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please log in as an administrator.';
    header('Location: ../user/login.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    $_SESSION['message'] = 'Access denied. Administrators only.';
    header('Location: ../shop.php');
    exit;
}

$order_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Canceled'];

function statusBadge($status)
{
    $colors = [
        'Pending'    => 'secondary',
        'Processing' => 'primary',
        'Shipped'    => 'info',
        'Delivered'  => 'success',
        'Canceled'   => 'danger'
    ];
    $color = $colors[$status] ?? 'dark';
    return '<span class="badge text-bg-' . $color . '">' . htmlspecialchars($status) . '</span>';
}