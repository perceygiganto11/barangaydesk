<?php

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/page_data.php';

$view = $_GET['view'] ?? 'dashboard';
$current = user();
$flashes = take_flash();

if (!$current && !in_array($view, ['login', 'register'], true)) {
    $view = 'login';
}

if (!$current) {
    include __DIR__ . '/pages/auth.php';
    exit;
}

$data = load_page_data($current, $view);
extract($data, EXTR_SKIP);

$pages = [
    'dashboard' => __DIR__ . '/pages/dashboard.php',
    'catalog' => __DIR__ . '/pages/catalog.php',
    'requests' => __DIR__ . '/pages/requests.php',
    'transactions' => __DIR__ . '/pages/transactions.php',
    'notifications' => __DIR__ . '/pages/notifications.php',
    'activity' => __DIR__ . '/pages/activity.php',
    'profile' => __DIR__ . '/pages/profile.php',
];

$page = $pages[$view] ?? $pages['dashboard'];
include __DIR__ . '/components/header.php';
include $page;
include __DIR__ . '/components/footer.php';
