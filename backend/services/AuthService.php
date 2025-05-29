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
            $missingFields = [];
            
            // Check for required fields
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                return [
                    'success' => false, 
                    'message' => 'Missing required fields: ' . implode(', ', $missingFields)
                ];
            }

            // Normalize email
            $email = strtolower(trim($userData['email']));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            if (strlen($userData['password']) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters'];
            }

            $existingUser = $this->dao->findByEmail($email);
            if ($existingUser) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            $userId = $this->dao->create($userData);
            $user = $this->dao->getUserById($userId);

            if (!$user) {
                return ['success' => false, 'message' => 'Failed to retrieve user after registration'];
            }

            // Generate JWT token
            $token = $this->generateToken($user);

            // Remove sensitive data before returning
            unset($user['Password']);

            return [
                'success' => true, 
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => $user
            ];
            
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Registration failed. Please try again later.',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate JWT token for a user
     */
    private function generateToken($user) {
        $payload = [
            'user' => [
                'id' => $user['UserID'],
                'email' => $user['Email'],
                'name' => $user['Name']
            ],
            'iat' => time(),
            'exp' => time() + 86400 // token valid for 24 hours
        ];
        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }

    public function login($loginData) {
        try {
            // Input validation
            if (!is_array($loginData)) {
                return ['success' => false, 'message' => 'Invalid request format'];
            }

            $errors = [];
            if (empty($loginData['email'])) {
                $errors[] = 'Email is required';
            }
            if (empty($loginData['password'])) {
                $errors[] = 'Password is required';
            }

            if (!empty($errors)) {
                return ['success' => false, 'message' => implode('. ', $errors)];
            }


            $email = strtolower(trim($loginData['email']));
            $password = $loginData['password'];

            // Find user by email
            $user = $this->dao->findByEmail($email);
            if (!$user) {
                // For security, don't reveal if email exists or not
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Verify password
            if (!$this->dao->verifyPassword($password, $user['Password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Generate JWT token
            $token = $this->generateToken($user);

            // Remove sensitive data before returning
            unset($user['Password']);

            // Return success response with token and user data
            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ];
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during login. Please try again.'
            ];
        }
    }
}
