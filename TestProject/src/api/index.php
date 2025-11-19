<?php
/**
 * Pure PHP REST API
 * Main entry point - handles all API requests
 */

// Set headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/config/',
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load configuration
require_once __DIR__ . '/config/Database.php';

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if needed (adjust based on your setup)
$basePath = '/api';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Parse URI segments
$segments = array_values(array_filter(explode('/', $uri)));

// Simple router
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;

// Route to appropriate controller
try {
    switch ($resource) {
        case 'users':
            require_once __DIR__ . '/controllers/UserController.php';
            $controller = new UserController();
            $controller->handleRequest($method, $id);
            break;

        case 'health':
            echo json_encode([
                'status' => 'ok',
                'message' => 'API is running',
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => phpversion()
            ]);
            break;

        case '':
            echo json_encode([
                'message' => 'Welcome to the PHP API',
                'version' => '1.0.0',
                'endpoints' => [
                    'GET /api/health' => 'Health check',
                    'GET /api/users' => 'List all users',
                    'GET /api/users/{id}' => 'Get user by ID',
                    'POST /api/users' => 'Create new user',
                    'PUT /api/users/{id}' => 'Update user',
                    'DELETE /api/users/{id}' => 'Delete user'
                ]
            ]);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'error' => 'Not Found',
                'message' => "Resource '$resource' not found"
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage()
    ]);
}
