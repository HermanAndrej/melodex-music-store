<?php

return function($flight) {
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create new order",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderCreateRequest")
     *     ),
     *     @OA\Response(response=200, description="Order created", @OA\JsonContent(ref="#/components/schemas/Order")),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    $flight->route('POST /api/orders', function() use ($flight) {
        try {
            $orderService = $flight->get('orderService');
            if (!$orderService) {
                $flight->json(['error' => 'Order service not available'], 500);
                return;
            }

            // Get JSON input data
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $flight->json(['error' => 'Invalid JSON input'], 400);
                return;
            }

            // Get current user from JWT
            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            // Add user ID to order data
            $input['user_id'] = $user->id ?? $user['id'] ?? null;

            $order = $orderService->create($input);
            $flight->json($order);
        } catch (Exception $e) {
            error_log("Create order error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to create order', 'message' => $e->getMessage()], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get orders for current user",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    $flight->route('GET /api/orders', function() use ($flight) {
        try {
            $orderService = $flight->get('orderService');
            if (!$orderService) {
                $flight->json(['error' => 'Order service not available'], 500);
                return;
            }

            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            // Handle different user object formats
            $userId = $user->id ?? $user['id'] ?? $user->UserID ?? $user['UserID'] ?? null;
            
            if (!$userId) {
                $flight->json(['error' => 'User ID not found'], 401);
                return;
            }

            $orders = $orderService->getOrdersByUser($userId);
            $flight->json($orders);
        } catch (Exception $e) {
            error_log("Get orders error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch orders'], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get order details by ID",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    $flight->route('GET /api/orders/@id', function($id) use ($flight) {
        try {
            $orderService = $flight->get('orderService');
            if (!$orderService) {
                $flight->json(['error' => 'Order service not available'], 500);
                return;
            }

            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            $order = $orderService->getOrderDetails($id);
            if ($order) {
                // Optional: Check if the order belongs to the current user
                $userId = $user->id ?? $user['id'] ?? $user->UserID ?? $user['UserID'] ?? null;
                if ($userId && isset($order['user_id']) && $order['user_id'] != $userId) {
                    $flight->json(['error' => 'Access denied'], 403);
                    return;
                }
                
                $flight->json($order);
            } else {
                $flight->json(['error' => 'Order not found'], 404);
            }
        } catch (Exception $e) {
            error_log("Get order details error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch order details'], 500);
        }
    });
};