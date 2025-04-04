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

    public function getByParentId($parentId) {
        $sql = "SELECT * FROM " . $this->table . " WHERE ParentID = :parentId";
        return $this->executeQuery($sql, [':parentId' => $parentId])->fetchAll();
    }

    public function getByStatus($status) {
        $sql = "SELECT * FROM " . $this->table . " WHERE Status = :status";
        return $this->executeQuery($sql, [':status' => $status])->fetchAll();
    }
}
?>