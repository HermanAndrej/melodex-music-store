<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

return function($flight) {
    $flight->route('POST /auth/register', function() use ($flight) {
        $data = json_decode(file_get_contents('php://input'), true);
        $authService = $flight->get('authService');

        if (!$authService) {
            $flight->json(['error' => 'Auth service not available'], 500);
            return;
        }

        $result = $authService->register($data);
        $flight->json($result);
    });

    $flight->route('POST /auth/login', function() use ($flight) {
        $data = json_decode(file_get_contents('php://input'), true);
        $authService = $flight->get('authService');

        if (!$authService) {
            $flight->json(['error' => 'Auth service not available'], 500);
            return;
        }

        $result = $authService->login($data);
        $flight->json($result);
    });
};
