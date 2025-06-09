<?php
require_once __DIR__ . '/BaseDao.php';

class AuthDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("users", "UserID");
    }

    public function insert($userData) {
        if (isset($userData['Password'])) {
            $userData['Password'] = password_hash($userData['Password'], PASSWORD_DEFAULT);
        }
        return parent::insert($userData);
    }

    public function getByEmail($email) {
        return $this->query_unique("SELECT * FROM users WHERE Email = :email", [':email' => $email]);
    }

    public function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function update($id, $data) {
        // If password is being updated, hash it
        if (isset($data['Password'])) {
            $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);
        }
        return parent::update($id, $data);
    }
}
