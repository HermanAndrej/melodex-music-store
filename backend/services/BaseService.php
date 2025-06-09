<?php

abstract class BaseService {
    protected $dao;
    protected $validationRules = [];

    public function __construct($dao) {
        $this->dao = $dao;
    }

    protected function validate($data, $isUpdate = false) {
        $errors = [];
        
        foreach ($this->validationRules as $field => $rules) {
            // Skip validation for fields not present in update
            if ($isUpdate && !isset($data[$field])) {
                continue;
            }
            
            if (isset($rules['required']) && $rules['required'] && empty($data[$field])) {
                $errors[$field] = "Field is required";
                continue;
            }
            
            if (isset($rules['type']) && isset($data[$field]) && !empty($data[$field])) {
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
                        $date = strtotime($data[$field]);
                        if (!$date || $date === false) {
                            $errors[$field] = "Invalid date format";
                        } else {
                            // Convert to Y-m-d format for consistency
                            $data[$field] = date('Y-m-d', $date);
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
        return $this->dao->insert($data);
    }

    public function update($id, $data) {
        $errors = $this->validate($data, true);
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }
        return $this->dao->update($id, $data);
    }

    public function delete($id) {
        return $this->dao->delete($id);
    }
} 