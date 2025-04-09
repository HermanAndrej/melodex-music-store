<?php

// Category routes
$app->route('GET /api/categories', function() use ($app, $categoryService) {
    $categories = $categoryService->getHierarchy();
    $app->json($categories);
});

$app->route('GET /api/categories/@id', function($id) use ($app, $categoryService) {
    $category = $categoryService->getById($id);
    if ($category) {
        $app->json($category);
    } else {
        $app->json(['error' => 'Category not found'], 404);
    }
}); 