<?php
require_once 'BaseDao.php';

class CategoryDao extends BaseDao {
    public function __construct() {
        parent::__construct("categories", "CategoryID");
    }

    public function getByName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE CategoryName = :name";
        return $this->executeQuery($sql, [':name' => $name])->fetch();
    }

    public function getByParentId($parentId) {
        $sql = "SELECT * FROM {$this->table} WHERE ParentCategoryID = :parentId";
        return $this->executeQuery($sql, [':parentId' => $parentId])->fetchAll();
    }

    public function getRootCategories() {
        $sql = "SELECT * FROM {$this->table} WHERE ParentCategoryID IS NULL";
        return $this->executeQuery($sql)->fetchAll();
    }

    public function getSubcategories($categoryId) {
        return $this->getByParentId($categoryId);
    }

    public function getHierarchy() {
        $sql = "SELECT * FROM categories ORDER BY ParentCategoryID, CategoryName";
        return $this->executeQuery($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductsByCategory($categoryId) {
        $sql = "SELECT p.* FROM products p WHERE p.CategoryID = :catId";
        return $this->executeQuery($sql, [':catId' => $categoryId])->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>
