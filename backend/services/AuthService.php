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
            $requiredFields = ['Name', 'Email', 'Password'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => "Missing required field: $field"];
                }
            }

            // Normalize email
            $userData['Email'] = strtolower(trim($userData['Email']));

            if (!filter_var($userData['Email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            if (strlen($userData['Password']) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters'];
            }

            $existingUser = $this->dao->getByEmail($userData['Email']);
            if ($existingUser) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            $result = $this->dao->insert($userData);

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
                throw new Exception('Invalid request format');
            }

            if (!array_key_exists('Email', $loginData)) {
                error_log("Missing email key");
                throw new Exception('Email is required');
            }
            if (!array_key_exists('Password', $loginData)) {
                error_log("Missing password key");
                throw new Exception('Password is required');
            }

            $email = strtolower(trim($loginData['Email']));
            $password = $loginData['Password'];

            error_log("EMAIL: $email");
            error_log("PASSWORD: " . ($password ? 'SET' : 'EMPTY'));

            $user = $this->dao->getByEmail($email);
            error_log("User from DAO: " . print_r($user, true));

            if (!$user) {
                error_log("User not found");
                throw new Exception('Invalid username or password');
            }

            if (!$this->dao->verifyPassword($password, $user['Password'])) {
                error_log("Password verification failed");
                throw new Exception('Invalid username or password');
            }

            $payload = [
                'user' => [
                    'id' => $user['UserID'],
                    'Email' => $user['Email'],
                    'Name' => $user['Name']
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
            throw $e;
        }
    }
}
