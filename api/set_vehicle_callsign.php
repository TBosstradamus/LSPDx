<?php
// api/set_vehicle_callsign.php

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
$callsign = $input['callsign'] ?? null;

if (!is_numeric($vehicleId) || !is_string($callsign) || empty(trim($callsign))) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data provided.']);
    exit;
}


// --- ACTION ---
try {
    $vehicleModel = new Vehicle();
    $success = $vehicleModel->updateCallsign($vehicleId, trim($callsign));

    if ($success) {
        Logger::log('vehicle_callsign_updated', "Callsign für Fahrzeug-ID {$vehicleId} wurde auf '{$callsign}' geändert.");
        echo json_encode(['success' => true, 'message' => 'Vehicle callsign updated.']);
    } else {
        http_response_code(500);
        Logger::log('vehicle_callsign_update_failed', "Fehler beim Ändern des Callsigns für Fahrzeug-ID {$vehicleId}.", null, $input);
        echo json_encode(['success' => false, 'message' => 'Failed to update vehicle callsign.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Set Vehicle Callsign API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>