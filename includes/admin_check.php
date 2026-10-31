<?php

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

// FR-19: an order only moves forward. Delivered and Canceled are final.
// (Canceled is allowed until the order is delivered; it returns the items to stock, FR-16.)
function allowedNextStatuses($current)
{
    $next = [
        'Pending'    => ['Processing', 'Shipped', 'Delivered', 'Canceled'],
        'Processing' => ['Shipped', 'Delivered', 'Canceled'],
        'Shipped'    => ['Delivered', 'Canceled'],
        'Delivered'  => [],
        'Canceled'   => []
    ];
    return $next[$current] ?? [];
}

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