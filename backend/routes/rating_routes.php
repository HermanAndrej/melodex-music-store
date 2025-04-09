<?php

// Rating routes (protected)
$app->route('POST /api/ratings', function() use ($app, $ratingService) {
    $data = $app->request()->data;
    $user = $app->get('user');
    $data['UserID'] = $user['UserID'];
    $rating = $ratingService->create($data);
    $app->json($rating);
})->addMiddleware($auth);

$app->route('GET /api/products/@id/ratings', function($id) use ($app, $ratingService) {
    $ratings = $ratingService->getProductRatings($id);
    $app->json($ratings);
}); 