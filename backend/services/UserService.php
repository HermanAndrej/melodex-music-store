<?php

class UserService extends BaseService {
    protected $validationRules = [
        'Name' => ['required' => true],
        'Email' => [
            'required' => true,
            'type' => 'email'
        ],
        'Password' => ['required' => true],
        'DateOfBirth' => ['type' => 'date'],
        'Phone' => ['type' => 'string'],
        'Address' => ['type' => 'string']
    ];

    public function __construct($userDao) {
        parent::__construct($userDao);
    }

    public function create($data) {
        // Hash password before storing
        if (isset($data['Password'])) {
            $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }

    public function update($id, $data) {
        // If password is being updated, hash it
        if (isset($data['Password'])) {
            $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);
        }
        return parent::update($id, $data);
    }

    public function authenticate($email, $password) {
        $user = $this->dao->getByEmail($email);
        if ($user && password_verify($password, $user['Password'])) {
            unset($user['Password']); // Remove password from returned data
            return $user;
        }
        return null;
    }

    public function updateProfile($userData) {
        try {
            if (empty($userData['UserID'])) {
                return ['success' => false, 'message' => 'User ID is required'];
            }

            // Get current user data
            $currentUser = $this->dao->getById($userData['UserID']);
            if (!$currentUser) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Update only allowed fields that exist in the database
            $allowedFields = ['Name', 'Phone', 'Address', 'DateOfBirth'];
            $updateData = array_intersect_key($userData, array_flip($allowedFields));
            
            // Ensure Name is not empty (it's required)
            if (isset($updateData['Name']) && empty(trim($updateData['Name']))) {
                return ['success' => false, 'message' => 'Name cannot be empty'];
            }
            
            // Set default values for optional fields
            foreach ($allowedFields as $field) {
                if ($field === 'Name') {
                    // Skip Name as it's required
                    continue;
                }
                if (!isset($updateData[$field]) || $updateData[$field] === '') {
                    $updateData[$field] = null;
                }
            }
            
            // Ensure we have at least one field to update
            if (empty($updateData)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }
            
            $result = $this->dao->update($userData['UserID'], $updateData);
            if ($result) {
                // Get updated user data
                $updatedUser = $this->dao->getById($userData['UserID']);
                unset($updatedUser['Password']); // Remove password from response
                
                // Format the response data
                $formattedUser = [
                    'UserID' => $updatedUser['UserID'],
                    'Name' => $updatedUser['Name'],
                    'Email' => $updatedUser['Email'],
                    'Phone' => $updatedUser['Phone'] ?? 'N/A',
                    'Address' => $updatedUser['Address'] ?? 'N/A',
                    'DateJoined' => $updatedUser['DateOfJoin'] ?? date('Y-m-d'),
                    'DateOfBirth' => $updatedUser['DateOfBirth'] ?? null
                ];
                
                return [
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => $formattedUser
                ];
            }

            return ['success' => false, 'message' => 'Failed to update profile'];
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getProfile($userId) {
        try {
            $user = $this->dao->getById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            unset($user['Password']); // Remove password from response
            
            // Format the response data
            $formattedUser = [
                'UserID' => $user['UserID'],
                'Name' => $user['Name'],
                'Email' => $user['Email'],
                'Phone' => $user['Phone'] ?? 'N/A',
                'Address' => $user['Address'] ?? 'N/A',
                'DateJoined' => $user['DateOfJoin'] ?? date('Y-m-d'),
                'DateOfBirth' => $user['DateOfBirth'] ?? null
            ];
            
            return [
                'success' => true,
                'data' => $formattedUser
            ];
        } catch (Exception $e) {
            error_log("Get profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
} 