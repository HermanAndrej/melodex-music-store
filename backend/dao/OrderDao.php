<?php
require_once 'BaseDao.php';

class OrderDao extends BaseDao {
    public function __construct() {
        parent::__construct("orders", "OrderID");
    }

    public function getAll() {
        // First get all orders
        $ordersSql = "SELECT o.*, u.Name as Username 
                     FROM " . $this->table . " o 
                     LEFT JOIN users u ON o.UserID = u.UserID 
                     ORDER BY o.OrderDate DESC";
        $orders = $this->executeQuery($ordersSql)->fetchAll(PDO::FETCH_ASSOC);
        
        // For each order, get its items
        foreach ($orders as &$order) {
            $itemsSql = "SELECT oi.*, p.Name as ProductName, p.Price as ProductPrice
                        FROM order_items oi
                        LEFT JOIN products p ON oi.ProductID = p.ProductID
                        WHERE oi.OrderID = :orderId";
            $items = $this->executeQuery($itemsSql, [':orderId' => $order['OrderID']])->fetchAll(PDO::FETCH_ASSOC);
            $order['items'] = $items;
        }
        
        return $orders;
    }

    public function getByUserId($userId) {
        // First get all orders for the user
        $ordersSql = "SELECT * FROM " . $this->table . " WHERE UserID = :userId ORDER BY OrderDate DESC";
        $orders = $this->executeQuery($ordersSql, [':userId' => $userId])->fetchAll(PDO::FETCH_ASSOC);
        
        // For each order, get its items
        foreach ($orders as &$order) {
            $itemsSql = "SELECT oi.*, p.Name as ProductName, p.Price as ProductPrice
                        FROM order_items oi
                        LEFT JOIN products p ON oi.ProductID = p.ProductID
                        WHERE oi.OrderID = :orderId";
            $items = $this->executeQuery($itemsSql, [':orderId' => $order['OrderID']])->fetchAll(PDO::FETCH_ASSOC);
            $order['items'] = $items;
        }
        
        return $orders;
    }

    public function getByDateRange($startDate, $endDate) {
        $sql = "SELECT * FROM " . $this->table . " WHERE OrderDate BETWEEN :startDate AND :endDate";
        return $this->executeQuery($sql, [':startDate' => $startDate, ':endDate' => $endDate])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderDetails($orderId) {
        // First get the order
        $orderSql = "SELECT * FROM " . $this->table . " WHERE OrderID = :orderId";
        $order = $this->executeQuery($orderSql, [':orderId' => $orderId])->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return null;
        }
        
        // Then get the order items
        $itemsSql = "SELECT oi.*, p.Name as ProductName, p.Price as ProductPrice
                    FROM order_items oi
                    LEFT JOIN products p ON oi.ProductID = p.ProductID
                    WHERE oi.OrderID = :orderId";
        $items = $this->executeQuery($itemsSql, [':orderId' => $orderId])->fetchAll(PDO::FETCH_ASSOC);
        
        // Combine order and items
        $order['items'] = $items;
        return $order;
    }

    public function insert($data) {
        $this->connection->beginTransaction();
        try {
            // Insert order
            $orderFields = ['UserID', 'OrderDate', 'TotalAmount'];
            $orderData = [];
            
            foreach ($orderFields as $field) {
                if (isset($data[$field])) {
                    $orderData[$field] = $data[$field];
                }
            }
            
            // Use parent's insert method for the main order
            $orderId = parent::insert($orderData);
            
            // Insert order items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $itemSql = "INSERT INTO order_items (OrderID, ProductID, Quantity, Price) 
                               VALUES (:OrderID, :ProductID, :Quantity, :Price)";
                    
                    $itemParams = [
                        ':OrderID' => $orderId,
                        ':ProductID' => $item['ProductID'],
                        ':Quantity' => $item['Quantity'],
                        ':Price' => $item['Price']
                    ];
                    
                    $this->executeQuery($itemSql, $itemParams);
                }
            }
            
            $this->connection->commit();
            return $this->getOrderDetails($orderId);
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}