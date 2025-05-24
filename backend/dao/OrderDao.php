<?php
require_once 'BaseDao.php';

class OrderDao extends BaseDao {
    public function __construct() {
        parent::__construct("orders", "OrderID");
    }

    public function create(array $order) {
        $id = $this->insert($order);
        return $this->getById($id);
    }

    public function getByUserID($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE UserID = :userId";
        return $this->executeQuery($sql, [':userId' => $userId])->fetchAll();
    }

    public function getByDateRange($startDate, $endDate) {
        $sql = "SELECT * FROM {$this->table} WHERE OrderDate BETWEEN :startDate AND :endDate";
        return $this->executeQuery($sql, [':startDate' => $startDate, ':endDate' => $endDate])->fetchAll();
    }

    public function getOrdersWithMinAmount($minAmount) {
        $sql = "SELECT * FROM {$this->table} WHERE TotalAmount >= :minAmount";
        return $this->executeQuery($sql, [':minAmount' => $minAmount])->fetchAll();
    }

    public function getOrdersWithUserInfo() {
        $sql = "SELECT o.*, u.Name, u.Email FROM {$this->table} o JOIN users u ON o.UserID = u.UserID";
        return $this->executeQuery($sql)->fetchAll();
    }

    public function getOrderDetails($orderId) {
        $sql = "SELECT o.*, u.Name as CustomerName, u.Email as CustomerEmail, u.Phone as CustomerPhone, u.Address as CustomerAddress
                FROM {$this->table} o 
                JOIN users u ON o.UserID = u.UserID 
                WHERE o.OrderID = :orderId";
        return $this->executeQuery($sql, [':orderId' => $orderId])->fetch(PDO::FETCH_ASSOC);
    }
}
?>
