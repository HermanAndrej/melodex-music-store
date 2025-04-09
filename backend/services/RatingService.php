<?php

class RatingService extends BaseService {
    protected $validationRules = [
        'UserID' => [
            'required' => true,
            'type' => 'numeric'
        ],
        'ProductID' => [
            'required' => true,
            'type' => 'numeric'
        ],
        'RatingValue' => [
            'required' => true,
            'type' => 'numeric'
        ]
    ];

    private $productService;

    public function __construct($ratingDao, $productService) {
        parent::__construct($ratingDao);
        $this->productService = $productService;
    }

    public function create($data) {
        // Validate rating value
        if ($data['RatingValue'] < 1 || $data['RatingValue'] > 5) {
            throw new Exception("Rating must be between 1 and 5");
        }

        // Check if user has already rated this product
        $existingRating = $this->dao->getUserProductRating($data['UserID'], $data['ProductID']);
        if ($existingRating) {
            throw new Exception("User has already rated this product");
        }

        $rating = parent::create($data);
        
        // Update product's average rating
        $this->productService->updateRating($data['ProductID']);
        
        return $rating;
    }

    public function update($id, $data) {
        // Validate rating value
        if (isset($data['RatingValue']) && ($data['RatingValue'] < 1 || $data['RatingValue'] > 5)) {
            throw new Exception("Rating must be between 1 and 5");
        }

        $rating = parent::update($id, $data);
        
        // Update product's average rating
        $this->productService->updateRating($data['ProductID']);
        
        return $rating;
    }

    public function getProductRatings($productId) {
        return $this->dao->getByProduct($productId);
    }

    public function getUserRatings($userId) {
        return $this->dao->getByUser($userId);
    }
} 