<?php
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../dao/UserDao.php';

/**
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 */
return function($flight) {
    $userService = new UserService(new UserDao());

    /**
     * @OA\Get(
     *     path="/api/users/profile",
     *     tags={"Users"},
     *     summary="Get user profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully"
     *     )
     * )
     */
    $flight->route('GET /api/users/profile', function() use ($userService, $flight) {
        try {
            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['success' => false, 'message' => 'User not authenticated'], 401);
                return;
            }

            $userId = $user->id ?? $user['id'] ?? $user->UserID ?? $user['UserID'] ?? null;
            if (!$userId) {
                $flight->json(['success' => false, 'message' => 'User ID not found'], 401);
                return;
            }

            $result = $userService->getProfile($userId);
            $flight->json($result);
        } catch (Exception $e) {
            error_log("Get profile error: " . $e->getMessage());
            $flight->json(['success' => false, 'message' => 'Failed to get profile: ' . $e->getMessage()], 500);
        }
    });

    /**
     * @OA\Put(
     *     path="/api/users/profile",
     *     tags={"Users"},
     *     summary="Update user profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="Name", type="string"),
     *             @OA\Property(property="Phone", type="string"),
     *             @OA\Property(property="Address", type="string"),
     *             @OA\Property(property="City", type="string"),
     *             @OA\Property(property="Country", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     )
     * )
     */
    $flight->route('PUT /api/users/profile', function() use ($userService, $flight) {
        try {
            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['success' => false, 'message' => 'User not authenticated'], 401);
                return;
            }

            // Get JSON input data
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $flight->json(['success' => false, 'message' => 'Invalid JSON input'], 400);
                return;
            }

            // Add user ID to the data
            $input['UserID'] = $user->id ?? $user['id'] ?? $user->UserID ?? $user['UserID'] ?? null;
            
            if (!$input['UserID']) {
                $flight->json(['success' => false, 'message' => 'User ID not found'], 401);
                return;
            }

            $result = $userService->updateProfile($input);
            $flight->json($result);
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            $flight->json(['success' => false, 'message' => 'Failed to update profile: ' . $e->getMessage()], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/users/all",
     *     tags={"Users"},
     *     summary="Get all users (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all users"
     *     )
     * )
     */
    $flight->route('GET /api/users/all', function() use ($userService, $flight) {
        try {
            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['success' => false, 'message' => 'User not authenticated'], 401);
                return;
            }

            // Check if user is admin
            $isAdmin = isset($user->Role) ? $user->Role === 'admin' : 
                      (isset($user['Role']) ? $user['Role'] === 'admin' : false);
            if (!$isAdmin) {
                $flight->json(['success' => false, 'message' => 'Admin access required'], 403);
                return;
            }

            $result = $userService->getAllUsers();
            $flight->json($result);
        } catch (Exception $e) {
            error_log("Get all users error: " . $e->getMessage());
            $flight->json(['success' => false, 'message' => 'Failed to get users: ' . $e->getMessage()], 500);
        }
    });
};
