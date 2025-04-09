<?php

class CategoryService extends BaseService {
    protected $validationRules = [
        'CategoryName' => ['required' => true],
        'ParentCategoryID' => ['type' => 'numeric']
    ];

    public function __construct($categoryDao) {
        parent::__construct($categoryDao);
    }

    public function create($data) {
        // Validate parent category if provided
        if (isset($data['ParentCategoryID'])) {
            $parent = $this->getById($data['ParentCategoryID']);
            if (!$parent) {
                throw new Exception("Parent category not found");
            }
        }
        return parent::create($data);
    }

    public function update($id, $data) {
        // Prevent circular references
        if (isset($data['ParentCategoryID'])) {
            if ($data['ParentCategoryID'] == $id) {
                throw new Exception("Category cannot be its own parent");
            }
            
            // Check if the new parent is a descendant of this category
            $descendants = $this->getDescendants($id);
            if (in_array($data['ParentCategoryID'], array_column($descendants, 'CategoryID'))) {
                throw new Exception("Cannot set a descendant category as parent");
            }
        }
        return parent::update($id, $data);
    }

    public function getDescendants($categoryId) {
        $descendants = [];
        $children = $this->dao->getByParent($categoryId);
        
        foreach ($children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $this->getDescendants($child['CategoryID']));
        }
        
        return $descendants;
    }

    public function getHierarchy() {
        return $this->dao->getHierarchy();
    }

    public function getProductsByCategory($categoryId) {
        return $this->dao->getProductsByCategory($categoryId);
    }
} 