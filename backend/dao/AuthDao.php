<?php
require_once __DIR__ . '/BaseDao.php';

class AuthDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("users", "UserID");
    }

    public function create($userData) {
        if (isset($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        return parent::insert($userData);
    }

    public function findByEmail($email) {
        return $this->query_unique("SELECT * FROM users WHERE email = :email", [':email' => $email]);
    }

    public function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }
}
