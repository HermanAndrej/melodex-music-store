<?php

// User routes
$app->route('POST /api/users/register', function() use ($app, $userService) {
    $data = $app->request()->data;
    $user = $userService->create($data);
    $app->json($user);
});

$app->route('POST /api/users/login', function() use ($app, $userService) {
    $data = $app->request()->data;
    $user = $userService->authenticate($data['email'], $data['password']);
    
    if ($user) {
        $token = JWT::encode(['userId' => $user['UserID']], JWT_SECRET);
        $app->json(['token' => $token, 'user' => $user]);
    } else {
        $app->json(['error' => 'Invalid credentials'], 401);
    }
}); 