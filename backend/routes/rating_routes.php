<?php

return function($flight) {
    /**
     * @OA\Post(
     *     path="/api/ratings",
     *     summary="Add rating for a product",
     *     tags={"Rating"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RatingCreateRequest")
     *     ),
     *     @OA\Response(response=200, description="Rating created", @OA\JsonContent(ref="#/components/schemas/Rating")),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    $flight->route('POST /api/ratings', function() use ($flight) {
        try {
            $ratingService = $flight->get('ratingService');
            if (!$ratingService) {
                $flight->json(['error' => 'Rating service not available'], 500);
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

            $input['user_id'] = $user->id ?? $user['id'] ?? $user->UserID ?? $user['UserID'] ?? null;

            $rating = $ratingService->create($input);
            $flight->json($rating);
        } catch (Exception $e) {
            error_log("Create rating error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to create rating'], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/products/{id}/ratings",
     *     summary="Get ratings for a product",
     *     tags={"Rating"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of ratings", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Rating")))
     * )
     */
    $flight->route('GET /api/products/@id/ratings', function($id) use ($flight) {
        try {
            $ratingService = $flight->get('ratingService');
            if (!$ratingService) {
                $flight->json(['error' => 'Rating service not available'], 500);
                return;
            }

            $ratings = $ratingService->getProductRatings($id);
            $flight->json($ratings);
        } catch (Exception $e) {
            error_log("Get product ratings error: " . $e->getMessage());
            $flight->json(['error' => 'Failed to fetch ratings'], 500);
        }
    });
};
