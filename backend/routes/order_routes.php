<?php
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../services/ProductService.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

return function($flight) {
    // Create services
    $productDao = new ProductDao();
    $productService = new ProductService($productDao);
    $orderDao = new OrderDao();
    $orderService = new OrderService($orderDao, $productService);
    $userDao = new UserDao();

    error_log("Registering order routes...");

    /**
     * @OA\Get(
     *     path="/api/orders/all",
     *     summary="Get all orders (admin only)",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all orders",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Admin access required")
     * )
     */
    $flight->route('GET /api/orders/all', function() use ($orderService, $userDao, $flight) {
        try {
            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            // Get user ID from JWT
            $userId = $user->id ?? $user['id'] ?? $user->UserID ?? $user['UserID'] ?? null;
            if (!$userId) {
                $flight->json(['error' => 'User ID not found'], 401);
                return;
            }

            // Get user from database to check role
            $dbUser = $userDao->getById($userId);
            if (!$dbUser || $dbUser['Role'] !== 'admin') {
                $flight->json(['error' => 'Admin access required'], 403);
                return;
            }

            $orders = $orderService->getAll();
            $flight->json(['success' => true, 'data' => $orders]);
        } catch (Exception $e) {
            error_log("Get all orders error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch orders: ' . $e->getMessage()], 500);
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     summary="Delete an order (admin only)",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Admin access required"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    $flight->route('DELETE /api/orders/@id', function($id) use ($orderService, $userDao, $flight) {
        try {
            $user = $flight->get('user');
            if (!$user) {
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            // Get user ID from JWT
            $userId = $user->id ?? $user['id'] ?? $user->UserID ?? $user['UserID'] ?? null;
            if (!$userId) {
                $flight->json(['error' => 'User ID not found'], 401);
                return;
            }

            // Get user from database to check role
            $dbUser = $userDao->getById($userId);
            if (!$dbUser || $dbUser['Role'] !== 'admin') {
                $flight->json(['error' => 'Admin access required'], 403);
                return;
            }

            // Check if order exists
            $order = $orderService->getById($id);
            if (!$order) {
                $flight->json(['error' => 'Order not found'], 404);
                return;
            }

            // Delete the order
            $orderService->delete($id);
            $flight->json(['success' => true, 'message' => 'Order deleted successfully']);
        } catch (Exception $e) {
            error_log("Delete order error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to delete order: ' . $e->getMessage()], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/orders/user",
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
    $flight->route('GET /api/orders/user', function() use ($orderService, $flight) {
        try {
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

            $orders = $orderService->getByUserId($userId);
            $flight->json(['success' => true, 'data' => $orders]);
        } catch (Exception $e) {
            error_log("Get user orders error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch user orders'], 500);
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
    $flight->route('GET /api/orders/@id', function($id) use ($orderService, $flight) {
        try {
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
                
                $flight->json(['success' => true, 'data' => $order]);
            } else {
                $flight->json(['error' => 'Order not found'], 404);
            }
        } catch (Exception $e) {
            error_log("Get order details error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch order details'], 500);
        }
    });

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
    $flight->route('POST /api/orders', function() use ($orderService, $flight) {
        try {
            // Get JSON input data
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Invalid JSON input: " . json_last_error_msg());
                $flight->json(['error' => 'Invalid JSON input: ' . json_last_error_msg()], 400);
                return;
            }

            error_log("Received order data: " . json_encode($input));

            // Get current user from JWT
            $user = $flight->get('user');
            if (!$user) {
                error_log("User not authenticated");
                $flight->json(['error' => 'User not authenticated'], 401);
                return;
            }

            error_log("Authenticated user: " . json_encode($user));

            // Verify that the UserID in the request matches the authenticated user
            $userId = $user->id ?? $user['id'] ?? $user->UserID ?? $user['UserID'] ?? null;
            if (!$userId) {
                error_log("User ID not found in JWT");
                $flight->json(['error' => 'User ID not found in JWT'], 401);
                return;
            }

            if ($userId != $input['UserID']) {
                error_log("User ID mismatch. JWT: $userId, Request: " . $input['UserID']);
                $flight->json(['error' => 'Invalid user ID'], 403);
                return;
            }

            error_log("Creating order for user $userId");
            $order = $orderService->create($input);
            error_log("Order created successfully: " . json_encode($order));
            
            $flight->json(['success' => true, 'data' => $order]);
        } catch (Exception $e) {
            error_log("Create order error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $flight->json(['error' => 'Failed to create order', 'message' => $e->getMessage()], 500);
        }
    });
};