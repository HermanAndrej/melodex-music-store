<?php

return function($flight) {
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Product"},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     )
     * )
     */
    $flight->route('GET /api/products', function() use ($flight) {
        try {
            $productService = $flight->get('productService');
            if (!$productService) {
                $flight->json(['error' => 'Product service not available'], 500);
                return;
            }

            $products = $productService->getAll();
            $flight->json($products);
        } catch (Exception $e) {
            error_log("Get products error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch products'], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product by ID",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    $flight->route('GET /api/products/@id', function($id) use ($flight) {
        try {
            $productService = $flight->get('productService');
            if (!$productService) {
                $flight->json(['error' => 'Product service not available'], 500);
                return;
            }

            $product = $productService->getById($id);
            if ($product) {
                $flight->json($product);
            } else {
                $flight->json(['error' => 'Product not found'], 404);
            }
        } catch (Exception $e) {
            error_log("Get product by ID error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch product'], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/products/category/{id}",
     *     summary="Get products by category ID",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     )
     * )
     */
    $flight->route('GET /api/products/category/@id', function($id) use ($flight) {
        try {
            $productService = $flight->get('productService');
            if (!$productService) {
                $flight->json(['error' => 'Product service not available'], 500);
                return;
            }

            $products = $productService->getProductsByCategory($id);
            $flight->json($products);
        } catch (Exception $e) {
            error_log("Get products by category error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch products by category'], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/products/search",
     *     summary="Search products",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     )
     * )
     */
    $flight->route('GET /api/products/search', function() use ($flight) {
        try {
            $query = $flight->request()->query->q ?? '';
            if (empty($query)) {
                $flight->json(['error' => 'Missing search query'], 400);
                return;
            }

            $productService = $flight->get('productService');
            if (!$productService) {
                $flight->json(['error' => 'Product service not available'], 500);
                return;
            }

            $products = $productService->searchProducts($query);
            $flight->json($products ?: []);
        } catch (Exception $e) {
            error_log("Search products error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to search products'], 500);
        }
    });

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Product"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     )
     * )
     */
    $flight->route('POST /api/products', function() use ($flight) {
        try {
            $productService = $flight->get('productService');
            if (!$productService) {
                $flight->json(['error' => 'Product service not available'], 500);
                return;
            }

            // Get JSON data from request body
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!$data) {
                $flight->json(['error' => 'Invalid JSON data'], 400);
                return;
            }

            $product = $productService->create($data);
            $flight->json($product, 201);
        } catch (Exception $e) {
            error_log("Create product error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully"
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    $flight->route('DELETE /api/products/@id', function($id) use ($flight) {
        try {
            $productService = $flight->get('productService');
            if (!$productService) {
                $flight->json(['error' => 'Product service not available'], 500);
                return;
            }

            $result = $productService->delete($id);
            if ($result) {
                $flight->json(['message' => 'Product deleted successfully']);
            } else {
                $flight->json(['error' => 'Product not found'], 404);
            }
        } catch (Exception $e) {
            error_log("Delete product error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to delete product'], 500);
        }
    });
};
