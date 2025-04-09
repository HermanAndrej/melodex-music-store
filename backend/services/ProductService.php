<?php

class ProductService extends BaseService {
    protected $validationRules = [
        'Name' => ['required' => true],
        'Price' => [
            'required' => true,
            'type' => 'numeric'
        ],
        'Stock' => [
            'required' => true,
            'type' => 'numeric'
        ],
        'CategoryID' => ['type' => 'numeric'],
        'Brand' => ['type' => 'string'],
        'Description' => ['type' => 'string'],
        'ImageURL' => ['type' => 'string']
    ];

    public function __construct($productDao) {
        parent::__construct($productDao);
    }

    public function create($data) {
        // Set default rating to 0
        $data['Rating'] = 0;
        return parent::create($data);
    }

    public function updateStock($productId, $quantity) {
        $product = $this->getById($productId);
        if (!$product) {
            throw new Exception("Product not found");
        }

        $newStock = $product['Stock'] + $quantity;
        if ($newStock < 0) {
            throw new Exception("Insufficient stock");
        }

        return $this->update($productId, ['Stock' => $newStock]);
    }

    public function getProductsByCategory($categoryId) {
        return $this->dao->getByCategory($categoryId);
    }

    public function searchProducts($query) {
        return $this->dao->search($query);
    }

    public function updateRating($productId) {
        $ratings = $this->dao->getProductRatings($productId);
        if (empty($ratings)) {
            return 0;
        }

        $totalRating = array_sum(array_column($ratings, 'RatingValue'));
        $averageRating = $totalRating / count($ratings);
        
        return $this->update($productId, ['Rating' => $averageRating]);
    }
} 