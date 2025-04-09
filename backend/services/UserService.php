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
} 