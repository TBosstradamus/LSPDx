<?php
// api/set_vehicle_funk.php

// --- BOOTSTRAP ---
header('Content-Type: application/json');
session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Vehicle.php';
require_once BASE_PATH . '/src/Logger.php';

// --- SECURITY & INPUT VALIDATION ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is accepted.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$vehicleId = $input['vehicleId'] ?? null;
$funk = $input['funk'] ?? null;

if (!is_numeric($vehicleId) || !is_string($funk) || empty(trim($funk))) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data provided.']);
    exit;
}


// --- ACTION ---
try {
    $vehicleModel = new Vehicle();
    $success = $vehicleModel->updateFunkChannel($vehicleId, trim($funk));

    if ($success) {
        Logger::log('vehicle_funk_updated', "Funk für Fahrzeug-ID {$vehicleId} wurde auf '{$funk}' geändert.");
        echo json_encode(['success' => true, 'message' => 'Vehicle funk channel updated.']);
    } else {
        http_response_code(500);
        Logger::log('vehicle_funk_update_failed', "Fehler beim Ändern des Funks für Fahrzeug-ID {$vehicleId}.", null, $input);
        echo json_encode(['success' => false, 'message' => 'Failed to update vehicle funk channel.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Set Vehicle Funk API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>