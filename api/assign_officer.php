<?php
// api/assign_officer.php

// --- BOOTSTRAP ---
header('Content-Type: application/json');
session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Dispatch.php';
require_once BASE_PATH . '/src/Logger.php';

// --- SECURITY & INPUT VALIDATION ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST method is accepted.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

// Get the posted data
$input = json_decode(file_get_contents('php://input'), true);

$officerId = $input['officerId'] ?? null;
$vehicleId = $input['vehicleId'] ?? null;
$seatIndex = $input['seatIndex'] ?? null;

if (!is_numeric($officerId) || !is_numeric($vehicleId) || !is_numeric($seatIndex)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid input data provided.']);
    exit;
}


// --- ACTION ---
try {
    $dispatchModel = new Dispatch();
    $success = $dispatchModel->assignOfficerToVehicle($officerId, $vehicleId, $seatIndex);

    if ($success) {
        Logger::log('officer_assigned', "Beamter {$officerId} wurde Fahrzeug {$vehicleId} auf Platz {$seatIndex} zugewiesen.");
        echo json_encode(['success' => true, 'message' => 'Officer assigned successfully.']);
    } else {
        http_response_code(500);
        Logger::log('officer_assign_failed', "Fehler bei Zuweisung von Beamten {$officerId} zu Fahrzeug {$vehicleId}.", null, $input);
        echo json_encode(['success' => false, 'message' => 'Failed to assign officer.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Assign Officer API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>