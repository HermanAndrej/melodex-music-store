<?php
require_once __DIR__ . '/BaseDao.php';

class AuthDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("users", "UserID");
    }

    public function create($userData) {
        // Map frontend fields to database fields
        $dbData = [
            'Name' => $userData['name'] ?? '',
            'Email' => strtolower(trim($userData['email'] ?? '')),
            'Password' => password_hash($userData['password'] ?? '', PASSWORD_DEFAULT),
            'DateOfBirth' => $userData['dateOfBirth'] ?? null,
            'Address' => $userData['address'] ?? null,
            'Phone' => $userData['phone'] ?? null,
            'DateOfJoin' => date('Y-m-d')
        ];
        
        return parent::insert($dbData);
    }

    public function findByEmail($email) {
        $email = strtolower(trim($email));
        return $this->query_unique("SELECT * FROM users WHERE Email = :email", [':email' => $email]);
    }

    public function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function getUserById($id) {
        return $this->query_unique("SELECT * FROM users WHERE UserID = :id", [':id' => $id]);
    }
}
