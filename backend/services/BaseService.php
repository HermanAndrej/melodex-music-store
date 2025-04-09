<?php

abstract class BaseService {
    protected $dao;
    protected $validationRules = [];

    public function __construct($dao) {
        $this->dao = $dao;
    }

    protected function validate($data) {
        $errors = [];
        
        foreach ($this->validationRules as $field => $rules) {
            if (isset($rules['required']) && $rules['required'] && empty($data[$field])) {
                $errors[$field] = "Field is required";
                continue;
            }
            
            if (isset($rules['type']) && isset($data[$field])) {
                switch ($rules['type']) {
                    case 'email':
                        if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "Invalid email format";
                        }
                        break;
                    case 'numeric':
                        if (!is_numeric($data[$field])) {
                            $errors[$field] = "Must be a number";
                        }
                        break;
                    case 'date':
                        if (!strtotime($data[$field])) {
                            $errors[$field] = "Invalid date format";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }
        return $this->dao->create($data);
    }

    public function update($id, $data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }
        return $this->dao->update($id, $data);
    }

    public function delete($id) {
        return $this->dao->delete($id);
    }
} 