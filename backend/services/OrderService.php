<?php
require_once __DIR__ . '/../dao/OrderDao.php';

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

    public function __construct($productService) {
        parent::__construct(new OrderDao()); 
        $this->productService = $productService;
    }

    public function create($data) {
        // Validate products and calculate total
        if (!isset($data['items']) || empty($data['items'])) {
            throw new Exception("Order must contain at least one item");
        }

        $totalAmount = 0;
        foreach ($data['items'] as $item) {
            if (!isset($item['ProductID']) || !isset($item['Quantity'])) {
                throw new Exception("Each item must have ProductID and Quantity");
            }
            
            // Get product using the service's getById method
            $product = $this->productService->getById($item['ProductID']);
            
            if (!$product) {
                throw new Exception("Product not found: " . $item['ProductID']);
            }
            
            // Check if we have the required fields
            if (!isset($product['Stock']) || !isset($product['Price'])) {
                throw new Exception("Product data incomplete for ProductID: " . $item['ProductID']);
            }
            
            if ($product['Stock'] < $item['Quantity']) {
                $productName = isset($product['Name']) ? $product['Name'] : "Product " . $item['ProductID'];
                throw new Exception("Insufficient stock for product: " . $productName);
            }
            
            $totalAmount += $product['Price'] * $item['Quantity'];
        }

        // Prepare order data for database (exclude items array)
        $orderData = [
            'UserID' => $data['UserID'] ?? $data['user_id'] ?? null,
            'TotalAmount' => $totalAmount,
            'OrderDate' => date('Y-m-d')
        ];

        // Validate required fields
        if (!$orderData['UserID']) {
            throw new Exception("User ID is required");
        }

        // Create order using DAO's create method instead of parent::create
        // This ensures we get the full order object back
        $errors = $this->validate($orderData);
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }
        
        $order = $this->dao->create($orderData);

        // Update stock levels - only if the order was created successfully
        if ($order) {
            try {
                foreach ($data['items'] as $item) {
                    $this->updateProductStock($item['ProductID'], -$item['Quantity']);
                }
            } catch (Exception $e) {
                // Log the error but don't fail the order creation
                error_log("Stock update failed for order " . ($order['OrderID'] ?? 'unknown') . ": " . $e->getMessage());
                // You could optionally delete the order here if stock update is critical
                // $this->dao->delete($order['OrderID']);
                // throw $e;
            }
        }

        return $order;
    }

    private function updateProductStock($productId, $quantityChange) {
        try {
            // Try multiple approaches to update stock
            
            // Method 1: If ProductService has an updateStock method
            if (method_exists($this->productService, 'updateStock')) {
                try {
                    $this->productService->updateStock($productId, $quantityChange);
                    return; // Success, exit early
                } catch (Exception $e) {
                    error_log("Method 1 failed - updateStock: " . $e->getMessage());
                }
            }
            
            // Method 2: Update through service's update method
            try {
                $product = $this->productService->getById($productId);
                if ($product && isset($product['Stock'])) {
                    $newStock = max(0, $product['Stock'] + $quantityChange); // Ensure stock doesn't go negative
                    $this->productService->update($productId, ['Stock' => $newStock]);
                    return; // Success, exit early
                }
            } catch (Exception $e) {
                error_log("Method 2 failed - service update: " . $e->getMessage());
            }
            
            // Method 3: Direct DAO access as last resort
            try {
                // Access the DAO directly through the service
                if (isset($this->productService->dao)) {
                    $product = $this->productService->dao->getById($productId);
                    if ($product && isset($product['Stock'])) {
                        $newStock = max(0, $product['Stock'] + $quantityChange);
                        $this->productService->dao->update($productId, ['Stock' => $newStock]);
                        return; // Success
                    }
                }
            } catch (Exception $e) {
                error_log("Method 3 failed - direct DAO: " . $e->getMessage());
            }
            
            // If all methods failed, log it but don't throw exception
            error_log("All stock update methods failed for product $productId");
            
        } catch (Exception $e) {
            error_log("Unexpected error in updateProductStock: " . $e->getMessage());
        }
    }

    public function getOrdersByUser($userId) {
        return $this->dao->getByUserID($userId);
    }

    public function getOrderDetails($orderId) {
        return $this->dao->getOrderDetails($orderId);
    }
}