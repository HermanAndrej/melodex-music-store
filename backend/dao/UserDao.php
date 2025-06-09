<?php
require_once 'BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("users", "UserID");
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM " . $this->table . " WHERE Email = :email";
        return $this->executeQuery($sql, [':email' => $email])->fetch();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            if ($key !== $this->primaryKey) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE " . $this->primaryKey . " = :id";
        $stmt = $this->executeQuery($sql, $params);
        
        return $stmt->rowCount() > 0;
    }
}
?>