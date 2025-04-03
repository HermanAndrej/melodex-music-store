<?php
require_once 'BaseDao.php';

class ProductDao extends BaseDao {
    public function __construct() {
        parent::__construct("products", "ProductID");
    }

    public function getByName($name) {
        $sql = "SELECT * FROM " . $this->table . " WHERE ProductName = :name";
        return $this->executeQuery($sql, [':name' => $name])->fetch();
    }
}
?>