<?php
require_once 'BaseDao.php';

class RatingDao extends BaseDao {
    public function __construct() {
        parent::__construct("ratings", "RatingID");
    }

    public function getByProductId($productId) {
        $sql = "SELECT * FROM " . $this->table . " WHERE ProductID = :productId";
        return $this->executeQuery($sql, [':productId' => $productId])->fetchAll();
    }

    public function getByUserId($userId) {
        $sql = "SELECT * FROM " . $this->table . " WHERE UserID = :userId";
        return $this->executeQuery($sql, [':userId' => $userId])->fetchAll();
    }

    public function getByRating($rating) {
        $sql = "SELECT * FROM " . $this->table . " WHERE Rating = :rating";
        return $this->executeQuery($sql, [':rating' => $rating])->fetchAll();
    }
}
?>