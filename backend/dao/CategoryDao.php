<?php
require_once 'BaseDao.php';

class CategoryDao extends BaseDao {
    public function __construct() {
        parent::__construct("categories", "CategoryID");
    }

    public function getByName($name) {
        $sql = "SELECT * FROM " . $this->table . " WHERE CategoryName = :name";
        return $this->executeQuery($sql, [':name' => $name])->fetch();
    }

    public function getByParent($parentId) {
        $sql = "SELECT * FROM " . $this->table . " WHERE ParentCategoryID = :parentId";
        return $this->executeQuery($sql, [':parentId' => $parentId])->fetchAll();
    }

    public function getHierarchy() {
        $sql = "WITH RECURSIVE category_tree AS (
            SELECT CategoryID, CategoryName, ParentCategoryID, 0 as level
            FROM " . $this->table . "
            WHERE ParentCategoryID IS NULL
            UNION ALL
            SELECT c.CategoryID, c.CategoryName, c.ParentCategoryID, ct.level + 1
            FROM " . $this->table . " c
            INNER JOIN category_tree ct ON c.ParentCategoryID = ct.CategoryID
        )
        SELECT * FROM category_tree
        ORDER BY level, CategoryName";
        return $this->executeQuery($sql)->fetchAll();
    }

    public function getProductsByCategory($categoryId) {
        $sql = "SELECT p.* FROM products p
                WHERE p.CategoryID = :categoryId";
        return $this->executeQuery($sql, [':categoryId' => $categoryId])->fetchAll();
    }
}
?>