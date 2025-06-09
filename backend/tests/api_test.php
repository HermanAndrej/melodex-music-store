<?php
// Simple API test runner for Melodex backend (no dependencies)

class ApiTest {
    private $baseUrl = 'http://localhost:8000/api';
    private $token = null;
    private $userId = null;
    private $results = [];
    private $testData = [];

    public function __construct() {
        $now = time();
        $this->testData = [
            'user' => [
                'Name' => 'Test User',
                'Email' => 'test' . $now . '@example.com',
                'Password' => 'testpass123',
                'DateOfBirth' => '1990-01-01',
                'Phone' => '1234567890',
                'Address' => '123 Test St'
            ],
            'category' => [
                'CategoryName' => 'Test Category ' . $now,
                'Description' => 'Test Category Description'
            ],
            'product' => [
                'Name' => 'Test Product ' . $now,
                'Description' => 'Test Product Description',
                'Price' => 99.99,
                'Stock' => 100,
                'CategoryID' => null // Will be set after category creation
            ],
            'rating' => [
                'RatingValue' => 5,
                'ProductID' => null // Will be set after product creation
            ],
            'order' => [
                'items' => [
                    [
                        'ProductID' => null, // Will be set after product creation
                        'Quantity' => 2
                    ]
                ]
            ]
        ];
    }

    private function request($method, $endpoint, $data = null, $auth = true) {
        $url = $this->baseUrl . $endpoint;
        $headers = ['Content-Type: application/json'];
        if ($auth && $this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'code' => $code,
            'data' => json_decode($response, true)
        ];
    }

    private function assert($name, $condition, $message = '') {
        $this->results[] = [
            'name' => $name,
            'passed' => $condition,
            'message' => $message
        ];
        echo ($condition ? "✓" : "✗") . " $name\n";
        if (!$condition && $message) {
            echo "  Error: $message\n";
        }
    }

    public function run() {
        echo "Starting API Tests...\n\n";
        $this->testRegisterUser();
        $this->testLoginUser();
        $this->testInvalidLogin();
        $this->testUnauthorizedAccess();
        $this->testGetCategories();
        $this->testGetCategoryById();
        $this->testGetProductsByCategory();
        $this->testGetProducts();
        $this->testGetProductById();
        $this->testSearchProducts();
        $this->testSearchProductsPartial();
        $this->testGetUserProfile();
        $this->testUpdateUserProfile();
        $this->testUpdateUserInvalidData();
        $this->testCreateOrder();
        $this->testCreateOrderMultipleItems();
        $this->testGetOrders();
        $this->testGetOrderDetails();
        $this->testCreateRating();
        $this->testCreateDuplicateRating();
        $this->testGetProductRatings();
        $this->testGetUserRatings();
        $this->summary();
    }

    // --- Individual test methods below ---
    private function testRegisterUser() {
        $r = $this->request('POST', '/auth/register', $this->testData['user'], false);
        $this->assert('Register User', $r['code'] === 200 && !empty($r['data']['success']), json_encode($r['data']));
    }
    private function testLoginUser() {
        $r = $this->request('POST', '/auth/login', [
            'Email' => $this->testData['user']['Email'],
            'Password' => $this->testData['user']['Password']
        ], false);
        $ok = $r['code'] === 200 && !empty($r['data']['success']) && !empty($r['data']['data']['token']);
        $this->assert('Login User', $ok, json_encode($r['data']));
        if ($ok) {
            $this->token = $r['data']['data']['token'];
            $this->userId = $r['data']['data']['user']['UserID'] ?? null;
        }
    }
    private function testInvalidLogin() {
        $r = $this->request('POST', '/auth/login', [
            'Email' => 'nope@example.com',
            'Password' => 'wrongpass'
        ], false);
        $this->assert('Invalid Login', $r['code'] === 401, json_encode($r['data']));
    }
    private function testUnauthorizedAccess() {
        $r = $this->request('GET', '/users/1', null, false);
        $this->assert('Unauthorized Access', $r['code'] === 401, json_encode($r['data']));
    }
    private function testGetCategories() {
        $r = $this->request('GET', '/categories');
        $ok = $r['code'] === 200 && is_array($r['data']);
        $this->assert('Get Categories', $ok, json_encode($r['data']));
        if ($ok && !empty($r['data'])) {
            $this->testData['product']['CategoryID'] = $r['data'][0]['CategoryID'];
        }
    }
    private function testGetCategoryById() {
        $id = $this->testData['product']['CategoryID'] ?? null;
        if (!$id) return $this->assert('Get Category by ID', false, 'No category ID');
        $r = $this->request('GET', '/categories/' . $id);
        $this->assert('Get Category by ID', $r['code'] === 200 && !empty($r['data']['CategoryID']), json_encode($r['data']));
    }
    private function testGetProductsByCategory() {
        $id = $this->testData['product']['CategoryID'] ?? null;
        if (!$id) return $this->assert('Get Products by Category', false, 'No category ID');
        $r = $this->request('GET', '/products/category/' . $id);
        $this->assert('Get Products by Category', $r['code'] === 200 && is_array($r['data']), json_encode($r['data']));
    }
    private function testGetProducts() {
        $r = $this->request('GET', '/products');
        $ok = $r['code'] === 200 && is_array($r['data']);
        $this->assert('Get Products', $ok, json_encode($r['data']));
        if ($ok && !empty($r['data'])) {
            $this->testData['rating']['ProductID'] = $r['data'][0]['ProductID'];
            $this->testData['order']['items'][0]['ProductID'] = $r['data'][0]['ProductID'];
        }
    }
    private function testGetProductById() {
        $id = $this->testData['rating']['ProductID'] ?? null;
        if (!$id) return $this->assert('Get Product by ID', false, 'No product ID');
        $r = $this->request('GET', '/products/' . $id);
        $this->assert('Get Product by ID', $r['code'] === 200 && !empty($r['data']['ProductID']), json_encode($r['data']));
    }
    private function testSearchProducts() {
        $r = $this->request('GET', '/products/search?q=Test');
        $this->assert('Search Products', $r['code'] === 200 && is_array($r['data']), json_encode($r['data']));
    }
    private function testSearchProductsPartial() {
        $r = $this->request('GET', '/products/search?q=nonexistentproduct');
        $this->assert('Search Products Partial', $r['code'] === 200 && is_array($r['data']) && empty($r['data']), json_encode($r['data']));
    }
    private function testGetUserProfile() {
        $id = $this->userId;
        if (!$id) return $this->assert('Get User Profile', false, 'No user ID');
        $r = $this->request('GET', '/users/' . $id);
        $this->assert('Get User Profile', $r['code'] === 200 && !empty($r['data']['UserID']), json_encode($r['data']));
    }
    private function testUpdateUserProfile() {
        $id = $this->userId;
        if (!$id) return $this->assert('Update User Profile', false, 'No user ID');
        $update = ['Name' => 'Updated Test User', 'Phone' => '9876543210'];
        $r = $this->request('PUT', '/users/' . $id, $update);
        $this->assert('Update User Profile', $r['code'] === 200 && !empty($r['data']['UserID']), json_encode($r['data']));
    }
    private function testUpdateUserInvalidData() {
        $id = $this->userId;
        if (!$id) return $this->assert('Update User Invalid Data', false, 'No user ID');
        $update = ['Email' => 'invalid-email', 'DateOfBirth' => 'invalid-date'];
        $r = $this->request('PUT', '/users/' . $id, $update);
        $this->assert('Update User Invalid Data', $r['code'] === 400 && !empty($r['data']['error']), json_encode($r['data']));
    }
    private function testCreateOrder() {
        $item = $this->testData['order']['items'][0];
        if (!$item['ProductID']) return $this->assert('Create Order', false, 'No product ID');
        $r = $this->request('POST', '/orders', $this->testData['order']);
        $this->assert('Create Order', $r['code'] === 200 && !empty($r['data']['OrderID']), json_encode($r['data']));
    }
    private function testCreateOrderMultipleItems() {
        $item = $this->testData['order']['items'][0];
        if (!$item['ProductID']) return $this->assert('Create Order Multiple Items', false, 'No product ID');
        $order = ['items' => [
            ['ProductID' => $item['ProductID'], 'Quantity' => 1],
            ['ProductID' => $item['ProductID'], 'Quantity' => 2]
        ]];
        $r = $this->request('POST', '/orders', $order);
        $this->assert('Create Order Multiple Items', $r['code'] === 200 && !empty($r['data']['OrderID']), json_encode($r['data']));
    }
    private function testGetOrders() {
        $r = $this->request('GET', '/orders');
        $this->assert('Get Orders', $r['code'] === 200 && is_array($r['data']), json_encode($r['data']));
    }
    private function testGetOrderDetails() {
        $r = $this->request('GET', '/orders');
        if (empty($r['data'])) return $this->assert('Get Order Details', false, 'No orders');
        $orderId = $r['data'][0]['OrderID'];
        $r2 = $this->request('GET', '/orders/' . $orderId);
        $this->assert('Get Order Details', $r2['code'] === 200 && !empty($r2['data']['OrderID']), json_encode($r2['data']));
    }
    private function testCreateRating() {
        $id = $this->testData['rating']['ProductID'] ?? null;
        if (!$id) return $this->assert('Create Rating', false, 'No product ID');
        $r = $this->request('POST', '/ratings', $this->testData['rating']);
        $this->assert('Create Rating', $r['code'] === 200 && !empty($r['data']['RatingID']), json_encode($r['data']));
    }
    private function testCreateDuplicateRating() {
        $id = $this->testData['rating']['ProductID'] ?? null;
        if (!$id) return $this->assert('Create Duplicate Rating', false, 'No product ID');
        $r = $this->request('POST', '/ratings', $this->testData['rating']);
        $this->assert('Create Duplicate Rating', $r['code'] === 400 && !empty($r['data']['error']), json_encode($r['data']));
    }
    private function testGetProductRatings() {
        $id = $this->testData['rating']['ProductID'] ?? null;
        if (!$id) return $this->assert('Get Product Ratings', false, 'No product ID');
        $r = $this->request('GET', '/ratings/product/' . $id);
        $this->assert('Get Product Ratings', $r['code'] === 200 && is_array($r['data']), json_encode($r['data']));
    }
    private function testGetUserRatings() {
        $id = $this->userId;
        if (!$id) return $this->assert('Get User Ratings', false, 'No user ID');
        $r = $this->request('GET', '/ratings/user/' . $id);
        $this->assert('Get User Ratings', $r['code'] === 200 && is_array($r['data']), json_encode($r['data']));
    }
    private function summary() {
        $total = count($this->results);
        $passed = count(array_filter($this->results, function($r) { return $r['passed']; }));
        echo "\nTest Summary:\nTotal: $total\nPassed: $passed\nFailed: " . ($total-$passed) . "\n";
        foreach ($this->results as $r) {
            if (!$r['passed']) {
                echo "- {$r['name']}: {$r['message']}\n";
            }
        }
    }
}

// Run the tests
$test = new ApiTest();
$test->run(); 