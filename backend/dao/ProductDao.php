<?php
require_once 'BaseDao.php';

class ProductDao extends BaseDao {
    public function __construct() {
        parent::__construct("products", "ProductID");
    }

    public function getByName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE Name = :name";
        return $this->executeQuery($sql, [':name' => $name])->fetch();
    }

    public function create(array $product) {
        $id = $this->insert($product);
        return $this->getById($id);
    }

    public function getByCategoryId($categoryId, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE CategoryID = :categoryId";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        return $this->executeQuery($sql, [':categoryId' => $categoryId])->fetchAll();
    }

    public function searchByName($searchTerm) {
        $sql = "SELECT * FROM {$this->table} WHERE Name LIKE :term";
        $term = "%$searchTerm%";
        return $this->executeQuery($sql, [':term' => $term])->fetchAll();
    }

    public function updateStock($productId, $newStock) {
        $sql = "UPDATE {$this->table} SET Stock = :stock WHERE ProductID = :productId";
        $this->executeQuery($sql, [':stock' => $newStock, ':productId' => $productId]);
        return true;
    }

    public function getProductRatings($productId) {
        $sql = "SELECT * FROM ratings WHERE ProductID = :productId";
        return $this->executeQuery($sql, [':productId' => $productId])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopRated($limit = 10) {
        $sql = "SELECT * FROM products ORDER BY Rating DESC LIMIT :limit";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
