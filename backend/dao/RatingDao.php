<?php
require_once 'BaseDao.php';

class RatingDao extends BaseDao {
    public function __construct() {
        parent::__construct("ratings", "RatingID");
    }

    public function getByProductId($productId) {
        $sql = "SELECT * FROM {$this->table} WHERE ProductID = :productId";
        return $this->executeQuery($sql, [':productId' => $productId])->fetchAll();
    }

    // Alternative method name for consistency
    public function getByProduct($productId) {
        return $this->getByProductId($productId);
    }


    public function getByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE UserID = :userId";
        return $this->executeQuery($sql, [':userId' => $userId])->fetchAll();
    }

    // Alternative method name for consistency
    public function getByUser($userId) {
        return $this->getByUserId($userId);
    }

    public function getByRating($rating) {
        $sql = "SELECT * FROM {$this->table} WHERE RatingValue = :rating";
        return $this->executeQuery($sql, [':rating' => $rating])->fetchAll();
    }

    public function getAverageRatingForProduct($productId) {
        $sql = "SELECT AVG(RatingValue) as avgRating FROM {$this->table} WHERE ProductID = :productId";
        $result = $this->executeQuery($sql, [':productId' => $productId])->fetch();
        return $result ? (float)$result['avgRating'] : null;
    }

    public function countRatingsForProduct($productId) {
        $sql = "SELECT COUNT(*) as ratingCount FROM {$this->table} WHERE ProductID = :productId";
        $result = $this->executeQuery($sql, [':productId' => $productId])->fetch();
        return $result ? (int)$result['ratingCount'] : 0;
    }

    public function getRatingsAbove($minRating) {
        $sql = "SELECT * FROM {$this->table} WHERE RatingValue >= :minRating";
        return $this->executeQuery($sql, [':minRating' => $minRating])->fetchAll();
    }
}
?>
