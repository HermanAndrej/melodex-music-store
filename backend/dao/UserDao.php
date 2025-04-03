<?php
require_once 'BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("users", "UserID");
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM " . $this->table . " WHERE email = :email";
        return $this->executeQuery($sql, [':email' => $email])->fetch();
    }

    public function getByUsername($username) {
        $sql = "SELECT * FROM " . $this->table . " WHERE username = :username";
        return $this->executeQuery($sql, [':username' => $username])->fetch();
    }
}
?>