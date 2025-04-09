<?php

class OrderService extends BaseService {
    protected $validationRules = [
        'UserID' => [
            'required' => true,
            'type' => 'numeric'
        ],
        'OrderDate' => [
            'required' => true,
            'type' => 'date'
        ],
        'TotalAmount' => [
            'required' => true,
            'type' => 'numeric'
        ]
    ];

    private $productService;

    public function __construct($orderDao, $productService) {
        parent::__construct($orderDao);
        $this->productService = $productService;
    }

    public function create($data) {
        // Validate products and calculate total
        if (!isset($data['items']) || empty($data['items'])) {
            throw new Exception("Order must contain at least one item");
        }

        $totalAmount = 0;
        foreach ($data['items'] as $item) {
            $product = $this->productService->getById($item['ProductID']);
            if (!$product) {
                throw new Exception("Product not found: " . $item['ProductID']);
            }
            if ($product['Stock'] < $item['Quantity']) {
                throw new Exception("Insufficient stock for product: " . $product['Name']);
            }
            $totalAmount += $product['Price'] * $item['Quantity'];
        }

        $data['TotalAmount'] = $totalAmount;
        $data['OrderDate'] = date('Y-m-d');

        // Create order
        $order = parent::create($data);

        // Update stock levels
        foreach ($data['items'] as $item) {
            $this->productService->updateStock($item['ProductID'], -$item['Quantity']);
        }

        return $order;
    }

    public function getOrdersByUser($userId) {
        return $this->dao->getByUser($userId);
    }

    public function getOrderDetails($orderId) {
        return $this->dao->getOrderDetails($orderId);
    }
} 