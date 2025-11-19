<?php
/**
 * User Controller - handles HTTP requests for users
 */
class UserController
{
    private $db;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();

        require_once __DIR__ . '/../models/User.php';
        $this->user = new User($this->db);
    }

    /**
     * Handle incoming request
     * @param string $method
     * @param int|null $id
     */
    public function handleRequest($method, $id = null)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getUser($id);
                } else {
                    $this->getAllUsers();
                }
                break;

            case 'POST':
                $this->createUser();
                break;

            case 'PUT':
                if ($id) {
                    $this->updateUser($id);
                } else {
                    $this->sendError(400, 'User ID is required');
                }
                break;

            case 'DELETE':
                if ($id) {
                    $this->deleteUser($id);
                } else {
                    $this->sendError(400, 'User ID is required');
                }
                break;

            default:
                $this->sendError(405, 'Method not allowed');
        }
    }

    /**
     * Get all users
     */
    private function getAllUsers()
    {
        try {
            $users = $this->user->getAll();

            echo json_encode([
                'success' => true,
                'count' => count($users),
                'data' => $users
            ]);
        } catch (Exception $e) {
            $this->sendError(500, $e->getMessage());
        }
    }

    /**
     * Get single user
     * @param int $id
     */
    private function getUser($id)
    {
        try {
            $user = $this->user->getById($id);

            if ($user) {
                echo json_encode([
                    'success' => true,
                    'data' => $user
                ]);
            } else {
                $this->sendError(404, 'User not found');
            }
        } catch (Exception $e) {
            $this->sendError(500, $e->getMessage());
        }
    }

    /**
     * Create new user
     */
    private function createUser()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (empty($data['name']) || empty($data['email'])) {
                $this->sendError(400, 'Name and email are required');
                return;
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendError(400, 'Invalid email format');
                return;
            }

            // Check if email already exists
            if ($this->user->emailExists($data['email'])) {
                $this->sendError(409, 'Email already exists');
                return;
            }

            $this->user->name = $data['name'];
            $this->user->email = $data['email'];

            if ($this->user->create()) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'User created successfully',
                    'data' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email
                    ]
                ]);
            } else {
                $this->sendError(500, 'Failed to create user');
            }
        } catch (Exception $e) {
            $this->sendError(500, $e->getMessage());
        }
    }

    /**
     * Update user
     * @param int $id
     */
    private function updateUser($id)
    {
        try {
            // Check if user exists
            $existingUser = $this->user->getById($id);
            if (!$existingUser) {
                $this->sendError(404, 'User not found');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Validate at least one field
            if (empty($data['name']) && empty($data['email'])) {
                $this->sendError(400, 'At least name or email is required');
                return;
            }

            // Validate email if provided
            if (!empty($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->sendError(400, 'Invalid email format');
                    return;
                }

                // Check if email already exists for another user
                if ($this->user->emailExists($data['email'], $id)) {
                    $this->sendError(409, 'Email already exists');
                    return;
                }
            }

            $this->user->id = $id;
            $this->user->name = $data['name'] ?? $existingUser['name'];
            $this->user->email = $data['email'] ?? $existingUser['email'];

            if ($this->user->update()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'data' => [
                        'id' => $id,
                        'name' => $this->user->name,
                        'email' => $this->user->email
                    ]
                ]);
            } else {
                $this->sendError(500, 'Failed to update user');
            }
        } catch (Exception $e) {
            $this->sendError(500, $e->getMessage());
        }
    }

    /**
     * Delete user
     * @param int $id
     */
    private function deleteUser($id)
    {
        try {
            // Check if user exists
            if (!$this->user->getById($id)) {
                $this->sendError(404, 'User not found');
                return;
            }

            if ($this->user->delete($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                $this->sendError(500, 'Failed to delete user');
            }
        } catch (Exception $e) {
            $this->sendError(500, $e->getMessage());
        }
    }

    /**
     * Send error response
     * @param int $code
     * @param string $message
     */
    private function sendError($code, $message)
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}
