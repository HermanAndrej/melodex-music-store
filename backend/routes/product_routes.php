<?php

// Product routes
$app->route('GET /api/products', function() use ($app, $productService) {
    $products = $productService->getAll();
    $app->json($products);
});

$app->route('GET /api/products/@id', function($id) use ($app, $productService) {
    $product = $productService->getById($id);
    if ($product) {
        $app->json($product);
    } else {
        $app->json(['error' => 'Product not found'], 404);
    }
});

$app->route('GET /api/products/category/@id', function($id) use ($app, $productService) {
    $products = $productService->getProductsByCategory($id);
    $app->json($products);
});

$app->route('GET /api/products/search', function() use ($app, $productService) {
    $query = $app->request()->query['q'];
    $products = $productService->searchProducts($query);
    $app->json($products);
}); 