<?php

// Order routes (protected)
$app->route('POST /api/orders', function() use ($app, $orderService) {
    $data = $app->request()->data;
    $order = $orderService->create($data);
    $app->json($order);
})->addMiddleware($auth);

$app->route('GET /api/orders', function() use ($app, $orderService) {
    $user = $app->get('user');
    $orders = $orderService->getOrdersByUser($user['UserID']);
    $app->json($orders);
})->addMiddleware($auth);

$app->route('GET /api/orders/@id', function($id) use ($app, $orderService) {
    $order = $orderService->getOrderDetails($id);
    if ($order) {
        $app->json($order);
    } else {
        $app->json(['error' => 'Order not found'], 404);
    }
})->addMiddleware($auth); 