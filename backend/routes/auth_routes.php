<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../dao/UserDao.php';

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication endpoints"
 * )
 */
return function($flight) {
    $authService = new AuthService();
    $userService = new UserService(new UserDao());

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Name","Email","Password"},
     *             @OA\Property(property="Name", type="string"),
     *             @OA\Property(property="Email", type="string", format="email"),
     *             @OA\Property(property="Password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully"
     *     )
     * )
     */
    $flight->route('POST /api/auth/register', function() use ($authService, $flight) {
        try {
            // Get JSON input data
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $flight->json(['success' => false, 'message' => 'Invalid JSON input'], 400);
                return;
            }

            $result = $authService->register($input);
            $flight->json($result);
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $flight->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    });

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Email","Password"},
     *             @OA\Property(property="Email", type="string", format="email"),
     *             @OA\Property(property="Password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful"
     *     )
     * )
     */
    $flight->route('POST /api/auth/login', function() use ($authService, $flight) {
        try {
            // Get JSON input data
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $flight->json(['success' => false, 'message' => 'Invalid JSON input'], 400);
                return;
            }

            $result = $authService->login($input);
            $flight->json($result);
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $flight->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    });
};
