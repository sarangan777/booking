<?php
/**
 * VanGo Van Management API
 * Handles CRUD operations for vans
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include database functions
require_once 'database.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get database connection
$pdo = getDatabaseConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all vans or specific van
        $van_id = $_GET['id'] ?? null;
        if ($van_id) {
            getVan($pdo, $van_id);
        } else {
            getAllVans($pdo);
        }
        break;
        
    case 'POST':
        // Add new van
        addVan($pdo);
        break;
        
    case 'PUT':
        // Update van
        $van_id = $_GET['id'] ?? null;
        if ($van_id) {
            updateVan($pdo, $van_id);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Van ID required for update']);
        }
        break;
        
    case 'DELETE':
        // Delete van
        $van_id = $_GET['id'] ?? null;
        if ($van_id) {
            deleteVan($pdo, $van_id);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Van ID required for deletion']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

// Function to get all vans
function getAllVans($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM vans ORDER BY type, daily_rate");
        $vans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Vans retrieved successfully',
            'vans' => $vans,
            'count' => count($vans)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving vans: ' . $e->getMessage()]);
    }
}

// Function to get specific van
function getVan($pdo, $van_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM vans WHERE van_id = ?");
        $stmt->execute([$van_id]);
        $van = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($van) {
            echo json_encode([
                'success' => true,
                'message' => 'Van retrieved successfully',
                'van' => $van
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Van not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving van: ' . $e->getMessage()]);
    }
}

// Function to add new van
function addVan($pdo) {
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            return;
        }
        
        // Validate required fields
        $required_fields = ['name', 'type', 'seats', 'daily_rate', 'status'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                return;
            }
        }
        
        // Generate van ID
        $van_id = 'VAN' . strtoupper(substr(md5(uniqid()), 0, 6));
        
        // Collect features as array and encode as JSON
        $features = [];
        if (!empty($input['ac'])) $features[] = 'Air Conditioning';
        if (!empty($input['wifi'])) $features[] = 'WiFi';
        if (!empty($input['gps'])) $features[] = 'GPS Navigation';
        $featuresJson = json_encode($features);
        
        // Get conduct details
        $conductDetails = $input['conduct_details'] ?? '';
        
        // Prepare SQL statement (store features as JSON)
        $sql = "INSERT INTO vans (van_id, name, type, seats, daily_rate, description, status, features, conduct_details, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $van_id,
            $input['name'],
            $input['type'],
            $input['seats'],
            $input['daily_rate'],
            $input['description'] ?? '',
            $input['status'],
            $featuresJson,
            $conductDetails
        ]);
        
        // Get the inserted van
        $stmt = $pdo->prepare("SELECT * FROM vans WHERE van_id = ?");
        $stmt->execute([$van_id]);
        $van = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Van added successfully',
            'van' => $van
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error adding van: ' . $e->getMessage()]);
    }
}

// Function to update van
function updateVan($pdo, $van_id) {
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            return;
        }
        
        // Check if van exists
        $stmt = $pdo->prepare("SELECT * FROM vans WHERE van_id = ?");
        $stmt->execute([$van_id]);
        $existing_van = $stmt->fetch();
        
        if (!$existing_van) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Van not found']);
            return;
        }
        
        // Prepare update fields
        $update_fields = [];
        $params = [];
        
        $fields_to_update = ['type', 'seats', 'daily_rate', 'ac', 'wifi', 'gps', 'status'];
        foreach ($fields_to_update as $field) {
            if (isset($input[$field])) {
                $update_fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        if (empty($update_fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        $update_fields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $van_id;
        
        // Update van
        $sql = "UPDATE vans SET " . implode(', ', $update_fields) . " WHERE van_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Get the updated van
        $stmt = $pdo->prepare("SELECT * FROM vans WHERE van_id = ?");
        $stmt->execute([$van_id]);
        $van = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Van updated successfully',
            'van' => $van
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating van: ' . $e->getMessage()]);
    }
}

// Function to delete van
function deleteVan($pdo, $van_id) {
    try {
        // Check if van exists
        $stmt = $pdo->prepare("SELECT * FROM vans WHERE van_id = ?");
        $stmt->execute([$van_id]);
        $van = $stmt->fetch();
        
        if (!$van) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Van not found']);
            return;
        }
        
        // Check if van has active bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE van_id = ? AND status IN ('confirmed', 'active')");
        $stmt->execute([$van_id]);
        $active_bookings = $stmt->fetchColumn();
        
        if ($active_bookings > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete van with active bookings']);
            return;
        }
        
        // Delete van
        $stmt = $pdo->prepare("DELETE FROM vans WHERE van_id = ?");
        $stmt->execute([$van_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Van deleted successfully'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting van: ' . $e->getMessage()]);
    }
}
?> 