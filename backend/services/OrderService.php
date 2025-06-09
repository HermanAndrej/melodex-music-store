<?php
require_once 'BaseService.php';

class OrderService extends BaseService {
    protected $validationRules = [
        'UserID' => [
            'required' => true,
            'type' => 'numeric'
        ],
        'TotalAmount' => [
            'required' => true,
            'type' => 'numeric'
        ],
        'OrderDate' => [
            'required' => true,
            'type' => 'date'
        ]
    ];

    private $productService;

    public function __construct($dao, $productService) {
        parent::__construct($dao);
        $this->productService = $productService;
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function create($data) {
        try {
            error_log("OrderService::create - Input data: " . json_encode($data));

            // Validate products and calculate total
            if (!isset($data['items']) || empty($data['items'])) {
                error_log("OrderService::create - No items in order");
                throw new Exception("Order must contain at least one item");
            }

            $totalAmount = 0;
            $processedItems = [];
            
            foreach ($data['items'] as $item) {
                error_log("Processing item: " . json_encode($item));

                if (!isset($item['ProductID']) || !isset($item['Quantity'])) {
                    error_log("OrderService::create - Missing ProductID or Quantity in item");
                    throw new Exception("Each item must have ProductID and Quantity");
                }
                
                $product = $this->productService->getById($item['ProductID']);
                if (!$product) {
                    error_log("OrderService::create - Product not found: " . $item['ProductID']);
                    throw new Exception("Product not found: " . $item['ProductID']);
                }

                error_log("Found product: " . json_encode($product));

                // Check if product has stock field
                if (!isset($product['Stock'])) {
                    error_log("OrderService::create - Product has no stock information: " . $item['ProductID']);
                    throw new Exception("Product stock information not available");
                }

                // Validate stock
                if ($product['Stock'] < $item['Quantity']) {
                    error_log("OrderService::create - Insufficient stock for product: " . $product['Name'] . ". Available: " . $product['Stock'] . ", Requested: " . $item['Quantity']);
                    throw new Exception("Insufficient stock for product: " . $product['Name'] . ". Available: " . $product['Stock']);
                }
                
                $itemTotal = $product['Price'] * $item['Quantity'];
                $totalAmount += $itemTotal;
                
                // Add the price to the item for the DAO
                $processedItems[] = [
                    'ProductID' => $item['ProductID'],
                    'Quantity' => $item['Quantity'],
                    'Price' => $product['Price']
                ];
            }

            // Prepare order data
            $orderData = [
                'UserID' => $data['UserID'],
                'TotalAmount' => $totalAmount,
                'OrderDate' => date('Y-m-d'),
                'items' => $processedItems
            ];

            error_log("Prepared order data: " . json_encode($orderData));

            // Create order using OrderDao's insert method
            $order = $this->dao->insert($orderData);
            error_log("Order created: " . json_encode($order));

            // Update stock levels
            foreach ($processedItems as $item) {
                error_log("Updating stock for product " . $item['ProductID'] . " by -" . $item['Quantity']);
                $this->productService->updateStock($item['ProductID'], -$item['Quantity']);
            }

            return $order;
        } catch (Exception $e) {
            error_log("OrderService::create error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function getByUserId($userId) {
        return $this->dao->getByUserId($userId);
    }

    public function getByDateRange($startDate, $endDate) {
        return $this->dao->getByDateRange($startDate, $endDate);
    }

    public function getOrderDetails($orderId) {
        return $this->dao->getOrderDetails($orderId);
    }

    public function delete($id) {
        // Get the order first to check if it exists
        $order = $this->getById($id);
        if (!$order) {
            throw new Exception("Order not found");
        }

        // Delete the order (this will cascade delete order items due to foreign key constraints)
        return $this->dao->delete($id);
    }
}