<?php
require_once 'BaseDao.php';

class ProductDao extends BaseDao {
    public function __construct() {
        parent::__construct("products", "ProductID");
    }

    public function getAll() {
        $sql = "SELECT p.*, c.CategoryName 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.CategoryID = c.CategoryID";
        return $this->executeQuery($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT p.*, c.CategoryName 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.CategoryID = c.CategoryID 
                WHERE p." . $this->primaryKey . " = :id";
        return $this->executeQuery($sql, [':id' => $id])->fetch(PDO::FETCH_ASSOC);
    }

    public function getByName($name) {
        $sql = "SELECT p.*, c.CategoryName 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.CategoryID = c.CategoryID 
                WHERE p.Name = :name";
        return $this->executeQuery($sql, [':name' => $name])->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategory($categoryId) {
        $sql = "SELECT p.*, c.CategoryName 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.CategoryID = c.CategoryID 
                WHERE p.CategoryID = :categoryId";
        return $this->executeQuery($sql, [':categoryId' => $categoryId])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($query) {
        $sql = "SELECT p.*, c.CategoryName 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.CategoryID = c.CategoryID 
                WHERE p.Name LIKE :query OR p.Description LIKE :query";
        $result = $this->executeQuery($sql, [':query' => "%$query%"])->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    public function getByProductId($productId) {
        $sql = "SELECT p.*, c.CategoryName 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.CategoryID = c.CategoryID 
                WHERE p.ProductID = :productId";
        return $this->executeQuery($sql, [':productId' => $productId])->fetch();
    }
}
?>