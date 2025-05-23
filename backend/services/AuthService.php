<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/AuthDao.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService {
    
    public function __construct() {
        $this->dao = new AuthDao();
    }
    
    public function register($userData) {
        try {
            $requiredFields = ['name', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => "Missing required field: $field"];
                }
            }

            // Normalize email
            $userData['email'] = strtolower(trim($userData['email']));

            if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            if (strlen($userData['password']) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters'];
            }

            $existingUser = $this->dao->findByEmail($userData['email']);
            if ($existingUser) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            $result = $this->dao->create($userData);

            return $result
                ? ['success' => true, 'message' => 'User registered successfully']
                : ['success' => false, 'message' => 'Registration failed'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function login($loginData) {
    try {
        error_log("LOGIN DATA (start): " . print_r($loginData, true));

        if (!is_array($loginData)) {
            error_log("LOGIN DATA is not array");
            return ['success' => false, 'message' => 'Invalid request format'];
        }

        if (!array_key_exists('email', $loginData)) {
            error_log("Missing email key");
            return ['success' => false, 'message' => 'Email is required'];
        }
        if (!array_key_exists('password', $loginData)) {
            error_log("Missing password key");
            return ['success' => false, 'message' => 'Password is required'];
        }

        $email = strtolower(trim($loginData['email']));
        $password = $loginData['password'];

        error_log("EMAIL: $email");
        error_log("PASSWORD: " . ($password ? 'SET' : 'EMPTY'));

        $user = $this->dao->findByEmail($email);
        error_log("User from DAO: " . print_r($user, true));

        if (!$user) {
            error_log("User not found");
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Fix here: use correct key 'Password' (capital P)
        if (!$this->dao->verifyPassword($password, $user['Password'])) {
            error_log("Password verification failed");
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Use correct keys with proper case for JWT payload
        $payload = [
            'user' => [
                'id' => $user['UserID'],
                'email' => $user['Email'],
                'name' => $user['Name']
            ],
            'iat' => time(),
            'exp' => time() + 86400 // token valid for 24 hours
        ];

        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

        // Remove password before returning user info
        unset($user['Password']);

        return [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ];
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
    }
}

}
