<?php
require_once 'BaseDao.php';

class OrderDao extends BaseDao {
    public function __construct() {
        parent::__construct("orders", "OrderID");
    }

    public function getByUserId($userId) {
        $sql = "SELECT * FROM " . $this->table . " WHERE UserID = :userId";
        return $this->executeQuery($sql, [':userId' => $userId])->fetchAll();
    }

    public function getByDateRange($startDate, $endDate) {
        $sql = "SELECT * FROM " . $this->table . " WHERE OrderDate BETWEEN :startDate AND :endDate";
        return $this->executeQuery($sql, [':startDate' => $startDate, ':endDate' => $endDate])->fetchAll();
    }
}
?>