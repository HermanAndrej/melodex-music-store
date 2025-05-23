<?php

return function($flight) {
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories hierarchy",
     *     tags={"Category"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))
     *     )
     * )
     */
    $flight->route('GET /api/categories', function() use ($flight) {
        try {
            $categoryService = $flight->get('categoryService');
            if (!$categoryService) {
                $flight->json(['error' => 'Category service not available'], 500);
                return;
            }
            
            $categories = $categoryService->getHierarchy();
            $flight->json($categories);
        } catch (Exception $e) {
            error_log("Category hierarchy error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch categories'], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get category by ID",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    $flight->route('GET /api/categories/@id', function($id) use ($flight) {
        try {
            $categoryService = $flight->get('categoryService');
            if (!$categoryService) {
                $flight->json(['error' => 'Category service not available'], 500);
                return;
            }
            
            $category = $categoryService->getById($id);
            if ($category) {
                $flight->json($category);
            } else {
                $flight->json(['error' => 'Category not found'], 404);
            }
        } catch (Exception $e) {
            error_log("Category by ID error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch category'], 500);
        }
    });
};