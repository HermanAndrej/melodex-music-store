<?php
require_once 'BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("users", "UserID");
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE Email = :email";
        return $this->executeQuery($sql, [':email' => $email])->fetch();
    }

    public function getByUsername($name) {
        $sql = "SELECT * FROM {$this->table} WHERE Name = :name";
        return $this->executeQuery($sql, [':name' => $name])->fetch();
    }

    public function getUsersJoinedAfter($date) {
        $sql = "SELECT * FROM {$this->table} WHERE DateOfJoin > :date";
        return $this->executeQuery($sql, [':date' => $date])->fetchAll();
    }

    public function updatePassword($userId, $newPassword) {
        $sql = "UPDATE {$this->table} SET Password = :password WHERE UserID = :userId";
        $this->executeQuery($sql, [':password' => $newPassword, ':userId' => $userId]);
        return true;
    }

    public function searchByNameOrEmail($searchTerm) {
        $sql = "SELECT * FROM {$this->table} WHERE Name LIKE :term OR Email LIKE :term";
        $term = "%$searchTerm%";
        return $this->executeQuery($sql, [':term' => $term])->fetchAll();
    }
}
?>
