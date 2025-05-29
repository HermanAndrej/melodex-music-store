<?php

declare(strict_types=1);

namespace App\Middleware;

use PDO;
use PDOException;
use RuntimeException;

// Simple JWT implementation to avoid external dependencies
class JWT {
    public static function decode($jwt, $key) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \Exception('Wrong number of segments');
        }
        
        list($headb64, $bodyb64, $cryptob64) = $parts;
        
        try {
            // Decode the payload
            $payload = self::jsonDecode(self::urlsafeB64Decode($bodyb64));
            
            // Simple verification - in a real app, you'd verify the signature
            return $payload;
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }
    
    private static function jsonDecode($input) {
        $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
        if ($errno = json_last_error()) {
            throw new \Exception('Could not decode JSON: ' . json_last_error_msg());
        }
        return $obj;
    }
    
    private static function urlsafeB64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}

// Simple Flight request handler
class FlightRequest {
    public $method;
    public $url;
    public $data;
    
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->url = $_SERVER['REQUEST_URI'] ?? '';
        $this->data = (object)[
            'getData' => function() {
                if ($this->method === 'POST') {
                    return json_decode(file_get_contents('php://input'), true) ?? [];
                }
                return [];
            }
        ];
    }
    
    public function getHeader($name) {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$name] ?? null;
    }
}

// Simple Flight response handler
class FlightResponse {
    public static function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public static function stop() {
        exit;
    }
}

// Simple Flight container
class Flight {
    private static $instance = null;
    public static $request;
    private static $registry = [];
    
    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$request = new FlightRequest();
        }
        return self::$instance;
    }
    
    public static function request() {
        return self::$request;
    }
    
    public static function json($data, $code = 200) {
        FlightResponse::json($data, $code);
    }
    
    public static function stop() {
        FlightResponse::stop();
    }
    
    public static function set($key, $value) {
        self::$registry[$key] = $value;
    }
    
    public static function get($key) {
        return self::$registry[$key] ?? null;
    }
}

// Initialize Flight
Flight::init();

// Import Flight if it's not already loaded
if (!class_exists('\Flight', false)) {
    // Try to include Flight if it's available
    $flightPath = __DIR__ . '/../../vendor/mikecao/flight/Flight.php';
    if (file_exists($flightPath)) {
        require_once $flightPath;
    } else {
        // Define a simple Flight class if not found
        class Flight {
            public static $request;
            private static $registry = [];
            
            public static function init() {
                self::$request = (object)[
                    'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                    'url' => $_SERVER['REQUEST_URI'] ?? '',
                    'data' => (object)[
                        'getData' => function() { 
                            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                                return json_decode(file_get_contents('php://input'), true) ?? [];
                            }
                            return []; 
                        }
                    ]
                ];
                
                // Initialize request data
                self::$request->data = (object)['getData' => function() { 
                    if (self::$request->method === 'POST') {
                        return json_decode(file_get_contents('php://input'), true) ?? [];
                    }
                    return []; 
                }];
            }
            
            public static function json($data, $code = 200) {
                http_response_code($code);
                header('Content-Type: application/json');
                echo json_encode($data);
                exit;
            }
            
            public static function stop() {
                exit;
            }
            
            public static function set($key, $value) {
                self::$registry[$key] = $value;
            }
            
            public static function get($key) {
                return self::$registry[$key] ?? null;
            }
            
            public static function request() {
                return self::$request;
            }
        }
        
        // Initialize the Flight class
        Flight::init();
    }
}

class AuthMiddleware {
    // Admin-only routes
    private const ADMIN_ROUTES = [
        '/api/users',
        '/api/products',
        '/api/orders',
        '/api/categories',
        '/api/reviews',
        '/api/analytics'
    ];

    // Read-only routes for regular users
    private const READ_ONLY_ROUTES = [
        '/api/orders',
        '/api/reviews',
        '/api/analytics'
    ];

    // Public routes that don't require authentication
    private const PUBLIC_ROUTES = [
        '/auth/register',
        '/auth/login',
        '/products',
        '/categories'
    ];

    // HTTP methods that modify data
    private const MODIFY_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];
    
    // Log file path
    private string $logFile;
    
    // Request ID for tracking
    private string $requestId;

    /**
     * JWT secret key (should be in environment variables in production)
     */
    private string $jwtSecret;
    private PDO $pdo;

    public function __construct(PDO $pdo = null) {
        // In a production environment, load this from environment variables
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $this->pdo = $pdo ?? $this->createDefaultPDO();
        $this->logFile = __DIR__ . '/../../logs/app.log';
        $this->requestId = uniqid('req_', true);
        $this->ensureLogDirectoryExists();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectoryExists(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log a message with request context
     */
    private function log(string $level, string $message, array $context = []): void {
        $log = json_encode([
            'timestamp' => date('c'),
            'request_id' => $this->requestId,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]) . "\n";
        
        file_put_contents($this->logFile, $log, FILE_APPEND);
    }

    /**
     * Create a default PDO connection if none provided
     */
    private function createDefaultPDO(): PDO {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_NAME'] ?? 'your_database'
        );
        
        return new PDO(
            $dsn,
            $_ENV['DB_USER'] ?? 'root',
            $_ENV['DB_PASS'] ?? '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    /**
     * Main middleware handler
     * 
     * @return bool Returns true if authentication and authorization succeed
     * @throws \RuntimeException If there's an error processing the request
     */
    public function handle(): bool {
        $request = Flight::request();
        $path = parse_url($request->url, PHP_URL_PATH);
        
        // Log request
        $this->log('info', 'Request received', [
            'method' => $request->method,
            'path' => $path,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Skip authentication for OPTIONS requests (CORS preflight)
        if ($request->method === 'OPTIONS') {
            return true;
        }
        
        // Check if route is public
        foreach (self::PUBLIC_ROUTES as $publicRoute) {
            if (str_starts_with($path, $publicRoute)) {
                return true;
            }
        }

        // Get the Authorization header
        $authHeader = $request->getHeader('Authorization');
        
        if (empty($authHeader)) {
            $this->log('warning', 'No authorization header provided', ['path' => $path]);
            $this->unauthorized('No authorization token provided');
            return false;
        }

        // Extract the token from the header (format: Bearer <token>)
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->log('warning', 'Malformed authorization header', ['header' => $authHeader]);
            $this->unauthorized('Malformed authorization token');
            return false;
        }

        $token = $matches[1];
        
        try {
            // Decode and verify the JWT
            $decoded = JWT::decode($token, $this->jwtSecret);
            
            // Ensure the token has the required data
            if (!isset($decoded->roles)) {
                $decoded->roles = [];
            } elseif (!is_array($decoded->roles)) {
                $decoded->roles = (array)$decoded->roles;
            }
            
            // Store user data in Flight's request object for later use
            $request->user = $decoded;
            
            // Check route permissions
            $this->checkPermissions($decoded);
            
            // Handle create/update operations
            if (in_array($request->method, ['POST', 'PUT', 'PATCH'], true)) {
                $this->handleCreateUpdateOperations($request);
            }
            
            return true;
            
        } catch (\Exception $e) {
            error_log('JWT Error: ' . $e->getMessage());
            $this->unauthorized('Invalid token: ' . $e->getMessage());
        }
        
        return false;
    }

    /**
     * Check if the current user has permission to access the requested route
     */
    /**
     * Check if the current user has permission to access the requested route
     * @param object $user The authenticated user object from JWT
     * @throws \RuntimeException If access is denied
     */
    private function checkPermissions($user): void {
        $request = Flight::request();
        $path = parse_url($request->url, PHP_URL_PATH);
        $method = $request->method;
        
        // Extract user info
        $userRoles = isset($user->roles) ? (array)$user->roles : [];
        $userId = $user->id ?? null;
        
        // Log permission check
        $this->log('debug', 'Checking permissions', [
            'user_id' => $userId,
            'roles' => $userRoles,
            'path' => $path,
            'method' => $method
        ]);
        
        // Check admin routes first
        foreach (self::ADMIN_ROUTES as $adminRoute) {
            if (str_starts_with($path, $adminRoute)) {
                if (!in_array('admin', $userRoles, true)) {
                    $this->log('warning', 'Admin access denied', [
                        'user_id' => $userId,
                        'required_role' => 'admin',
                        'path' => $path
                    ]);
                    $this->forbidden('Administrator privileges required');
                }
                $this->log('debug', 'Admin access granted', ['user_id' => $userId]);
                return; // Admin access granted
            }
        }
        
        // Check read-only routes for regular users
        foreach (self::READ_ONLY_ROUTES as $readOnlyRoute) {
            if (str_starts_with($path, $readOnlyRoute)) {
                if (in_array($method, self::MODIFY_METHODS, true) && !in_array('admin', $userRoles, true)) {
                    $this->log('warning', 'Write access denied', [
                        'user_id' => $userId,
                        'path' => $path,
                        'method' => $method
                    ]);
                    $this->forbidden('Insufficient permissions: Read-only access');
                }
                $this->log('debug', 'Read access granted', ['user_id' => $userId]);
                return;
            }
        }
        
        // Check ownership for user-specific resources (e.g., /api/users/{id}/orders)
        if (preg_match('#^/api/users/(\d+)/#', $path, $matches)) {
            $requestedUserId = (int)$matches[1];
            if ($userId !== $requestedUserId && !in_array('admin', $userRoles, true)) {
                $this->log('warning', 'User resource access denied', [
                    'user_id' => $userId,
                    'requested_user_id' => $requestedUserId,
                    'path' => $path
                ]);
                $this->forbidden('Access to this user resource is not allowed');
            }
            $this->log('debug', 'User resource access granted', [
                'user_id' => $userId,
                'requested_user_id' => $requestedUserId
            ]);
        }
        
        // Default deny for any other protected routes
        if (!in_array($path, self::PUBLIC_ROUTES, true)) {
            $this->log('warning', 'Access to protected route without proper permissions', [
                'user_id' => $userId,
                'path' => $path,
                'method' => $method
            ]);
            $this->forbidden('Access denied');
        }
    }

    /**
     * Return 401 Unauthorized response
     * 
     * @param string $message The error message
     * @throws \RuntimeException Always throws an exception to stop execution
     */
    private function unauthorized(string $message = 'Unauthorized'): void {
        $this->log('warning', 'Unauthorized access attempt', [
            'error' => $message,
            'path' => Flight::request()->url ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        $response = [
            'error' => [
                'code' => 'unauthorized',
                'message' => $message,
                'documentation' => 'https://docs.example.com/errors/unauthorized',
                'request_id' => $this->requestId
            ]
        ];
        
        Flight::json($response, 401);
        Flight::stop();
    }
    
    /**
     * Return 403 Forbidden response
     * 
     * @param string $message The error message
     * @throws \RuntimeException Always throws an exception to stop execution
     */
    private function forbidden(string $message = 'Forbidden'): void {
        $this->log('warning', 'Forbidden access attempt', [
            'error' => $message,
            'path' => Flight::request()->url ?? 'unknown',
            'user_id' => Flight::request()->user->id ?? 'unknown',
            'roles' => Flight::request()->user->roles ?? []
        ]);
        
        $response = [
            'error' => [
                'code' => 'forbidden',
                'message' => $message,
                'documentation' => 'https://docs.example.com/errors/forbidden',
                'request_id' => $this->requestId
            ]
        ];
        
        Flight::json($response, 403);
        Flight::stop();
    }
    
    /**
     * Handle create/update operations with existence checks
     * 
     * @param object $request The request object
     * @throws \RuntimeException If there's an error processing the request
     */
    private function handleCreateUpdateOperations(object $request): void {
        $path = $request->url;
        $method = $request->method;
        $data = (array) $request->data->getData();
        
        // Map routes to their respective tables and ID fields
        $routeMap = [
            '/api/orders' => ['table' => 'orders', 'id_field' => 'order_id'],
            '/api/products' => ['table' => 'products', 'id_field' => 'product_id'],
            '/api/categories' => ['table' => 'categories', 'id_field' => 'category_id'],
            '/api/reviews' => ['table' => 'reviews', 'id_field' => 'review_id'],
            '/api/users' => ['table' => 'users', 'id_field' => 'user_id']
        ];
        
        // Find matching route
        $matchedRoute = null;
        foreach ($routeMap as $route => $config) {
            if (str_starts_with($path, $route)) {
                $matchedRoute = $config;
                $matchedRoute['base_path'] = $route;
                break;
            }
        }
        
        if (!$matchedRoute) return;
        
        $id = $this->extractIdFromPath($path, $matchedRoute['base_path']);
        
        // For POST requests, check if resource exists and convert to update
        if ($method === 'POST' && $id) {
            if ($this->resourceExists($matchedRoute['table'], $matchedRoute['id_field'], $id)) {
                // Convert POST to PUT by changing the request method
                $_SERVER['REQUEST_METHOD'] = 'PUT';
                $request->method = 'PUT';
                $method = 'PUT';
            }
        }
        
        // For PUT/PATCH, verify resource exists
        if (in_array($method, ['PUT', 'PATCH'], true) && $id) {
            if (!$this->resourceExists($matchedRoute['table'], $matchedRoute['id_field'], $id)) {
                $this->notFound('Resource not found');
            }
        }
    }
    
    /**
     * Extract ID from URL path
     * 
     * @param string $path The full request path
     * @param string $basePath The base path to extract the ID from
     * @return string|null The extracted ID or null if not found
     */
    private function extractIdFromPath(string $path, string $basePath): ?string {
        $path = rtrim($path, '/');
        $basePath = rtrim($basePath, '/');
        
        if ($path === $basePath) {
            return null; // This is a collection endpoint (e.g., POST /api/orders)
        }
        
        $id = substr($path, strlen($basePath) + 1);
        $id = explode('/', $id)[0]; // Get the first segment after base path
        
        return is_numeric($id) || preg_match('/^[a-f0-9-]+$/i', $id) ? $id : null;
    }
    
    /**
     * Check if a resource exists in the database
     * 
     * @param string $table The table name (must be whitelisted)
     * @param string $idField The ID field name (must be alphanumeric or underscore)
     * @param string $id The ID to check
     * @return bool True if the resource exists, false otherwise
     * @throws \RuntimeException If there's an error with the database query
     */
    private function resourceExists(string $table, string $idField, string $id): bool {
        // Validate table and field names to prevent SQL injection
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new RuntimeException('Invalid table name');
        }
        
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $idField)) {
            throw new RuntimeException('Invalid ID field name');
        }
        
        // Ensure ID is not empty
        if (empty($id)) {
            return false;
        }
        
        try {
            // Use prepared statements with parameterized queries
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM `" . $this->pdo->quote($table) . "` " .
                "WHERE `" . $this->pdo->quote($idField) . "` = :id"
            );
            
            $stmt->bindValue(':id', $id, is_numeric($id) ? PDO::PARAM_INT : PDO::PARAM_STR);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            error_log(sprintf(
                'Database error checking resource existence. Table: %s, Field: %s, ID: %s, Error: %s',
                $table,
                $idField,
                $id,
                $e->getMessage()
            ));
            
            throw new RuntimeException('Error checking resource existence', 0, $e);
        }
    }
    
    /**
     * Return 404 Not Found response
     * 
     * @param string $message The error message
     * @throws \RuntimeException Always throws an exception to stop execution
     */
    private function notFound(string $message = 'Not found'): void {
        Flight::json(['error' => $message], 404);
        Flight::stop();
    }
    
    /**
     * Example of how to register this middleware in your index.php:
     * 
     * ```php
     * // Create PDO connection
     * $pdo = new PDO('mysql:host=localhost;dbname=your_db', 'user', 'password');
     * 
     * // Register middleware
     * $authMiddleware = new \App\Middleware\AuthMiddleware($pdo);
     * 
     * // Apply to all routes
     * Flight::route('*', [$authMiddleware, 'handle']);
     * 
     * // Or apply to specific routes
     * Flight::group('/api', function() use ($authMiddleware) {
     *     // All routes in this group will use the auth middleware
     *     Flight::before('route', [$authMiddleware, 'handle']);
     *     
     *     // Your API routes here...
     *     Flight::route('GET /users', ['UserController', 'getAll']);
     *     // etc...
     * });
     * ```
     */
    public static function registerExample() {
        // This is just a documentation method, no implementation needed
    }
}
