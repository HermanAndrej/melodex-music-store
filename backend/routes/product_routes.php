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
            $flight->json($products);
        } catch (Exception $e) {
            error_log("Search products error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to search products'], 500);
        }
    });
};
