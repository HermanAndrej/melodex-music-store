<?php

return function($flight) {
    /**
     * @OA\Tag(
     *     name="users",
     *     description="User profile management"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"users"},
     *     summary="Get user profile by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    $flight->route('GET /api/users/@id', function($id) use ($flight) {
        try {
            $userService = $flight->get('userService');
            if (!$userService) {
                $flight->json(['error' => 'User service not available'], 500);
                return;
            }

            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            $result = $userService->getById($id);
            if ($result) {
                $flight->json($result);
            } else {
                $flight->json(['error' => 'User not found'], 404);
            }
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch user'], 500);
        }
    });

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"users"},
     *     summary="Update user profile by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data to update",
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated user profile",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    $flight->route('PUT /api/users/@id', function($id) use ($flight) {
        try {
            $userService = $flight->get('userService');
            if (!$userService) {
                $flight->json(['error' => 'User service not available'], 500);
                return;
            }

            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $flight->json(['error' => 'Invalid JSON input'], 400);
                return;
            }

            $updated = $userService->update($id, $input);
            if ($updated) {
                $flight->json($updated);
            } else {
                $flight->json(['error' => 'User not found'], 404);
            }
        } catch (Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to update user'], 500);
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"users"},
     *     summary="Delete user account by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean"))
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    $flight->route('DELETE /api/users/@id', function($id) use ($flight) {
        try {
            $userService = $flight->get('userService');
            if (!$userService) {
                $flight->json(['error' => 'User service not available'], 500);
                return;
            }

            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            $result = $userService->delete($id);
            if ($result) {
                $flight->json(['success' => true]);
            } else {
                $flight->json(['error' => 'User not found'], 404);
            }
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to delete user'], 500);
        }
    });
};
